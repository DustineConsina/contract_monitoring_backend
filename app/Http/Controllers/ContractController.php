<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Tenant;
use App\Models\User;
use App\Models\RentalSpace;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Carbon\Carbon;

class ContractController extends Controller
{
    /**
     * Display a listing of contracts
     */
    public function index(Request $request)
    {
        $query = Contract::with(['tenant.user', 'rentalSpace', 'payments']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($q) use ($search) {
                        $q->where('business_name', 'like', "%{$search}%")
                            ->orWhere('tenant_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('rentalSpace', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('space_code', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status (case-insensitive)
        if ($request->has('status')) {
            $query->where('status', strtolower($request->status));
        }

        // Filter by rental space type (case-insensitive)
        if ($request->has('space_type')) {
            $query->whereHas('rentalSpace', function ($q) use ($request) {
                $q->whereRaw('LOWER(space_type) = ?', [strtolower($request->space_type)]);
            });
        }

        // Filter by tenant
        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Filter expiring soon
        if ($request->has('expiring_soon') && $request->expiring_soon) {
            $query->where('status', 'active')
                ->where('end_date', '<=', Carbon::now()->addDays(30));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $contracts = $query->paginate($request->get('per_page', 15));

        AuditLog::log('view', 'Contract', null, 'Viewed contract list');

        return response()->json([
            'success' => true,
            'data' => $contracts
        ]);
    }

    /**
     * Store a newly created contract
     */
    public function store(Request $request)
    {
        // Map camelCase to snake_case (handle both frontend field name variations)
        $data = $request->all();
        
        if (isset($data['tenantId'])) {
            $data['tenant_id'] = $data['tenantId'];
            unset($data['tenantId']);
        }
        
        if (isset($data['rentalSpaceId'])) {
            $data['rental_space_id'] = $data['rentalSpaceId'];
            unset($data['rentalSpaceId']);
        }
        
        if (isset($data['startDate'])) {
            $data['start_date'] = $data['startDate'];
            unset($data['startDate']);
        }
        
        if (isset($data['durationMonths'])) {
            $data['duration_months'] = $data['durationMonths'];
            unset($data['durationMonths']);
        }
        
        // If endDate provided but no duration_months, calculate it
        if (isset($data['endDate']) && !isset($data['duration_months'])) {
            if (isset($data['start_date'])) {
                $startDate = Carbon::parse($data['start_date']);
                $endDate = Carbon::parse($data['endDate']);
                $data['duration_months'] = intval($startDate->diffInMonths($endDate)) + 1;
            }
        }
        
        if (isset($data['endDate'])) {
            unset($data['endDate']); // Remove endDate, we calculate it from duration
        }
        
        // Handle monthlyRent/monthlyRental variations
        if (isset($data['monthlyRent'])) {
            $data['monthly_rental'] = $data['monthlyRent'];
            unset($data['monthlyRent']);
        } elseif (isset($data['monthlyRental'])) {
            $data['monthly_rental'] = $data['monthlyRental'];
            unset($data['monthlyRental']);
        }
        
        // Handle securityDeposit/depositAmount variations
        if (isset($data['securityDeposit'])) {
            $data['deposit_amount'] = $data['securityDeposit'];
            unset($data['securityDeposit']);
        } elseif (isset($data['depositAmount'])) {
            $data['deposit_amount'] = $data['depositAmount'];
            unset($data['depositAmount']);
        }
        
        if (isset($data['interestRate'])) {
            $data['interest_rate'] = $data['interestRate'];
            unset($data['interestRate']);
        }
        
        // Handle terms/termsConditions variations
        if (isset($data['terms'])) {
            $data['terms_conditions'] = $data['terms'];
            unset($data['terms']);
        } elseif (isset($data['termsConditions'])) {
            $data['terms_conditions'] = $data['termsConditions'];
            unset($data['termsConditions']);
        }
        
        // If tenantId (User ID) was provided, convert it to tenant_id (Tenant ID)
        if (isset($data['tenant_id']) && !is_null($data['tenant_id'])) {
            // Check if the provided tenant_id is actually a Tenant ID (exists in tenants table)
            $tenantExists = Tenant::where('id', $data['tenant_id'])->exists();
            if (!$tenantExists) {
                // It might be a User ID, try to find the associated Tenant
                $user = User::with('tenant')->find($data['tenant_id']);
                if ($user && $user->tenant) {
                    $data['tenant_id'] = $user->tenant->id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tenant profile not found for this user',
                        'errors' => ['tenant_id' => 'Tenant profile not found']
                    ], 422);
                }
            }
        }
        
        $validator = Validator::make($data, [
            'tenant_id' => 'required|exists:tenants,id',
            'rental_space_id' => 'required|exists:rental_spaces,id',
            'start_date' => 'required|date',
            'duration_months' => 'required|integer|min:1',
            'monthly_rental' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'terms_conditions' => 'nullable|string',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if rental space is available
        $rentalSpace = RentalSpace::find($data['rental_space_id']);
        if (!$rentalSpace || $rentalSpace->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Rental space is not available'
            ], 422);
        }

        // Check if tenant has existing active contract for the same space
        $existingContract = Contract::where('tenant_id', $data['tenant_id'])
            ->where('rental_space_id', $data['rental_space_id'])
            ->where('status', 'active')
            ->exists();

        if ($existingContract) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant already has an active contract for this space'
            ], 422);
        }

        // Calculate end date
        $startDate = Carbon::parse($data['start_date']);
        $endDate = $startDate->copy()->addMonths($data['duration_months'])->subDay();

        // Handle file upload
        $contractFile = null;
        if ($request->hasFile('contract_file')) {
            $contractFile = $request->file('contract_file')->store('contracts', 'public');
        }

        // Create contract
        $contract = Contract::create([
            'contract_number' => 'CON-' . date('Y') . '-' . str_pad(Contract::withTrashed()->count() + 1, 6, '0', STR_PAD_LEFT),
            'tenant_id' => $data['tenant_id'],
            'rental_space_id' => $data['rental_space_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_months' => $data['duration_months'],
            'monthly_rental' => $data['monthly_rental'],
            'deposit_amount' => $data['deposit_amount'] ?? 0,
            'interest_rate' => $data['interest_rate'] ?? 2, // Default 2% monthly interest
            'terms_conditions' => $data['terms_conditions'] ?? null,
            'contract_file' => $contractFile,
            'status' => 'pending',
        ]);

        AuditLog::log('create', 'Contract', $contract->id, "Created contract: {$contract->contract_number}", null, $contract->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Contract created successfully',
            'data' => $contract->load(['tenant.user', 'rentalSpace'])
        ], 201);
    }

    /**
     * Display the specified contract
     */
    public function show($id)
    {
        \Log::info('ContractController@show called with ID: ' . $id);
        
        try {
            $contract = Contract::with([
                'tenant.user',
                'rentalSpace',
                'payments'
            ])->findOrFail($id);

            \Log::info('Contract found', ['id' => $contract->id, 'number' => $contract->contract_number]);
            \Log::info('Contract has tenant: ' . ($contract->tenant ? 'YES' : 'NO'));
            \Log::info('Payments count: ' . (isset($contract->payments) ? count($contract->payments) : 'N/A'));
            
            AuditLog::log('view', 'Contract', $contract->id, "Viewed contract: {$contract->contract_number}");

            $responseData = [
                'success' => true,
                'data' => $contract
            ];
            
            \Log::info('Returning response', ['response_keys' => array_keys($responseData)]);
            
            return response()->json($responseData);
        } catch (\Exception $e) {
            \Log::error('Contract error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update the specified contract
     */
    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        // Map camelCase to snake_case (handle both frontend field name variations)
        $data = $request->all();
        
        // Map tenant and rental space IDs
        if (isset($data['tenantId'])) {
            $data['tenant_id'] = $data['tenantId'];
            unset($data['tenantId']);
        }
        
        if (isset($data['rentalSpaceId'])) {
            $data['rental_space_id'] = $data['rentalSpaceId'];
            unset($data['rentalSpaceId']);
        }
        
        if (isset($data['startDate'])) {
            $data['start_date'] = $data['startDate'];
            unset($data['startDate']);
        }
        
        if (isset($data['endDate'])) {
            unset($data['endDate']); // Remove endDate, we calculate it
        }
        
        if (isset($data['durationMonths'])) {
            $data['duration_months'] = $data['durationMonths'];
            unset($data['durationMonths']);
        }
        
        // Handle monthlyRent/monthlyRental variations
        if (isset($data['monthlyRent'])) {
            $data['monthly_rental'] = $data['monthlyRent'];
            unset($data['monthlyRent']);
        } elseif (isset($data['monthlyRental'])) {
            $data['monthly_rental'] = $data['monthlyRental'];
            unset($data['monthlyRental']);
        }
        
        // Handle securityDeposit/depositAmount variations
        if (isset($data['securityDeposit'])) {
            $data['deposit_amount'] = $data['securityDeposit'];
            unset($data['securityDeposit']);
        } elseif (isset($data['depositAmount'])) {
            $data['deposit_amount'] = $data['depositAmount'];
            unset($data['depositAmount']);
        }
        
        if (isset($data['interestRate'])) {
            $data['interest_rate'] = $data['interestRate'];
            unset($data['interestRate']);
        }
        
        // Handle terms/termsConditions variations
        if (isset($data['terms'])) {
            $data['terms_conditions'] = $data['terms'];
            unset($data['terms']);
        } elseif (isset($data['termsConditions'])) {
            $data['terms_conditions'] = $data['termsConditions'];
            unset($data['termsConditions']);
        }
        
        if (isset($data['contractFile'])) {
            $data['contract_file'] = $data['contractFile'];
            unset($data['contractFile']);
        }

        $validator = Validator::make($data, [
            'tenant_id' => 'sometimes|integer|exists:tenants,id',
            'rental_space_id' => 'sometimes|integer|exists:rental_spaces,id',
            'start_date' => 'sometimes|date',
            'duration_months' => 'sometimes|integer|min:1',
            'monthly_rental' => 'sometimes|numeric|min:0',
            'deposit_amount' => 'sometimes|numeric|min:0',
            'interest_rate' => 'sometimes|numeric|min:0|max:100',
            'terms_conditions' => 'sometimes|string',
            'status' => 'sometimes|in:active,expired,terminated,pending',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $oldValues = $contract->toArray();

        // Handle file upload
        if ($request->hasFile('contract_file')) {
            // Delete old file
            if ($contract->contract_file) {
                Storage::disk('public')->delete($contract->contract_file);
            }
            $contract->contract_file = $request->file('contract_file')->store('contracts', 'public');
        }

        // Recalculate end date if start date or duration changed
        if (isset($data['start_date']) || isset($data['duration_months'])) {
            $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : $contract->start_date;
            $duration = isset($data['duration_months']) ? $data['duration_months'] : $contract->duration_months;
            $contract->end_date = $startDate->copy()->addMonths($duration)->subDay();
        }

        $contract->fill($data)->save();

        AuditLog::log('update', 'Contract', $contract->id, "Updated contract: {$contract->contract_number}", $oldValues, $contract->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Contract updated successfully',
            'data' => $contract->load(['tenant.user', 'rentalSpace'])
        ]);
    }

    /**
     * Activate contract
     */
    public function activate($id)
    {
        try {
            $contract = Contract::with('rentalSpace')->findOrFail($id);

            \Log::info("=== ACTIVATE CONTRACT {$id} ===");
            \Log::info("Contract ID: {$contract->id}, Rental Space ID: {$contract->rental_space_id}, Status: {$contract->status}");

            // Allow activation for any contract that isn't already in a terminal state
            $inactiveStatuses = ['active', 'expired', 'terminated'];
            if (in_array(strtolower($contract->status), array_map('strtolower', $inactiveStatuses))) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot activate contract with status: {$contract->status}. Contract is already in a terminal state.",
                    'current_status' => $contract->status
                ], 422);
            }

            $oldStatus = $contract->status;
            $contract->status = 'active';
            $contract->save();

            \Log::info("Contract status updated to active");

            // Update rental space status to occupied
            if ($contract->rental_space_id) {
                \Log::info("Updating rental space {$contract->rental_space_id}...");
                
                // Try Eloquent update first
                if ($contract->rentalSpace) {
                    $contract->rentalSpace->status = 'occupied';
                    $contract->rentalSpace->save();
                    \Log::info("Eloquent update: Rental space {$contract->rental_space_id} status changed to 'occupied'");
                }
                
                // Also do a direct database update to be sure
                $updated = \DB::table('rental_spaces')
                    ->where('id', $contract->rental_space_id)
                    ->update(['status' => 'occupied']);
                
                \Log::info("Direct DB update result: " . ($updated ? "Success ({$updated} rows)" : "No rows updated"));
                
                // Verify the update
                $verify = \DB::table('rental_spaces')->where('id', $contract->rental_space_id)->first();
                \Log::info("Verification - Rental space status is now: " . ($verify ? $verify->status : 'NULL'));
                
            } else {
                \Log::error("Contract {$id} has no rental_space_id!");
            }

            // Generate payment schedule
            try {
                $contract->generatePaymentSchedule();
                \Log::info("Payment schedule generated successfully");
            } catch (\Exception $e) {
                \Log::error("Error generating payment schedule: " . $e->getMessage());
                // Continue activation even if payment schedule fails
            }

            // Create notification for tenant
            $tenant = $contract->tenant;
            $rentalSpaceName = $contract->rentalSpace ? $contract->rentalSpace->name : 'Unknown Space';
            Notification::create([
                'user_id' => $tenant->user_id,
                'type' => 'contract_activated',
                'title' => 'Contract Activated',
                'message' => "Your contract {$contract->contract_number} for {$rentalSpaceName} has been activated.",
                'data' => ['contract_id' => $contract->id],
            ]);

            AuditLog::log('update', 'Contract', $contract->id, "Activated contract: {$contract->contract_number}", ['status' => $oldStatus], ['status' => 'active']);

            // Refresh to get latest data
            $contract->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Contract activated successfully',
                'data' => $contract->load(['tenant.user', 'rentalSpace', 'payments'])
            ]);
        } catch (\Exception $e) {
            \Log::error("Error activating contract {$id}: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error activating contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terminate contract
     */
    public function terminate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $contract = Contract::with('rentalSpace')->findOrFail($id);

        \Log::info("=== TERMINATE CONTRACT {$id} ===");
        \Log::info("Contract ID: {$contract->id}, Rental Space ID: {$contract->rental_space_id}, Status: {$contract->status}");

        if ($contract->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Only active contracts can be terminated'
            ], 422);
        }

        $oldStatus = $contract->status;
        $contract->status = 'terminated';
        $contract->terms_conditions .= "\n\nTermination Reason: " . $request->reason;
        $contract->save();

        \Log::info("Contract status updated to terminated");

        // Update rental space status to available
        if ($contract->rental_space_id) {
            \Log::info("Updating rental space {$contract->rental_space_id}...");
            
            // Try Eloquent update first
            if ($contract->rentalSpace) {
                $contract->rentalSpace->status = 'available';
                $contract->rentalSpace->save();
                \Log::info("Eloquent update: Rental space {$contract->rental_space_id} status changed to 'available'");
            }
            
            // Also do a direct database update to be sure
            $updated = \DB::table('rental_spaces')
                ->where('id', $contract->rental_space_id)
                ->update(['status' => 'available']);
            
            \Log::info("Direct DB update result: " . ($updated ? "Success ({$updated} rows)" : "No rows updated"));
            
            // Verify the update
            $verify = \DB::table('rental_spaces')->where('id', $contract->rental_space_id)->first();
            \Log::info("Verification - Rental space status is now: " . ($verify ? $verify->status : 'NULL'));
            
        } else {
            \Log::error("Contract {$id} has no rental_space_id!");
        }

        // Create notification for tenant
        $tenant = $contract->tenant;
        Notification::create([
            'user_id' => $tenant->user_id,
            'type' => 'contract_terminated',
            'title' => 'Contract Terminated',
            'message' => "Your contract {$contract->contract_number} has been terminated. Reason: {$request->reason}",
            'data' => ['contract_id' => $contract->id],
        ]);

        AuditLog::log('update', 'Contract', $contract->id, "Terminated contract: {$contract->contract_number}. Reason: {$request->reason}", ['status' => $oldStatus], ['status' => 'terminated']);

        // Refresh relationships to get latest data
        $contract->refresh();
        
        return response()->json([
            'success' => true,
            'message' => 'Contract terminated successfully',
            'data' => $contract->load(['tenant.user', 'rentalSpace'])
        ]);
    }

    /**
     * Delete contract
     */
    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $contractNumber = $contract->contract_number;

        // Delete contract file
        if ($contract->contract_file) {
            Storage::disk('public')->delete($contract->contract_file);
        }

        AuditLog::log('delete', 'Contract', $contract->id, "Deleted contract: {$contractNumber}");

        $contract->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contract deleted successfully'
        ]);
    }

    /**
     * Renew contract
     */
    public function renew(Request $request, $id)
    {
        $oldContract = Contract::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'duration_months' => 'required|integer|min:1',
            'monthly_rental' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create new contract based on old one
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addMonths($request->duration_months)->subDay();

        $newContract = Contract::create([
            'contract_number' => 'CON-' . date('Y') . '-' . str_pad(Contract::withTrashed()->count() + 1, 6, '0', STR_PAD_LEFT),
            'tenant_id' => $oldContract->tenant_id,
            'rental_space_id' => $oldContract->rental_space_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_months' => $request->duration_months,
            'monthly_rental' => $request->monthly_rental ?? $oldContract->monthly_rental,
            'deposit_amount' => 0,
            'interest_rate' => $oldContract->interest_rate,
            'terms_conditions' => $oldContract->terms_conditions,
            'status' => 'active',
        ]);

        // Update old contract status
        $oldContract->status = 'expired';
        $oldContract->save();

        // Generate payment schedule for new contract
        $newContract->generatePaymentSchedule();

        // Create notification
        Notification::create([
            'user_id' => $oldContract->tenant->user_id,
            'type' => 'contract_renewed',
            'title' => 'Contract Renewed',
            'message' => "Your contract has been renewed. New contract number: {$newContract->contract_number}",
            'data' => ['contract_id' => $newContract->id, 'old_contract_id' => $oldContract->id],
        ]);

        AuditLog::log('create', 'Contract', $newContract->id, "Renewed contract from {$oldContract->contract_number} to {$newContract->contract_number}");

        return response()->json([
            'success' => true,
            'message' => 'Contract renewed successfully',
            'data' => $newContract->load(['tenant.user', 'rentalSpace'])
        ], 201);
    }

    /**
     * Generate and get QR code for contract
     */
    public function getQRCode($id)
    {
        try {
            $contract = Contract::with(['tenant.user', 'rentalSpace'])->findOrFail($id);

            // Create QR code data - point directly to the lease PDF using network IP (for phone scanning)
            $qrBaseUrl = env('QR_BASE_URL', config('app.url'));
            $qrData = $qrBaseUrl . '/api/contracts/' . $contract->id . '/lease';

            // Generate QR code
            $qrCode = new QrCode($qrData);
            $writer = new SvgWriter();
            $qrSvg = $writer->write($qrCode);
            
            // Convert SVG to string
            $svgString = $qrSvg->getString();
            
            // Convert to data URI
            $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svgString);

            // Return QR code data URI
            return response()->json([
                'success' => true,
                'qrCode' => $dataUri,
                'data' => [
                    'contract_id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'tenant' => $contract->tenant->contact_person,
                    'rental_space' => $contract->rentalSpace->space_code,
                    'start_date' => $contract->start_date,
                    'end_date' => $contract->end_date,
                    'monthly_rental' => $contract->monthly_rental,
                    'status' => $contract->status,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Public endpoint to view contract details via QR code
     * No authentication required
     */
    public function viewQRContract($id)
    {
        try {
            $contract = Contract::with([
                'tenant.user',
                'rentalSpace'
            ])->findOrFail($id);

            // Return contract details in camelCase for frontend
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $contract->id,
                    'contractNumber' => $contract->contract_number,
                    'tenant' => [
                        'id' => $contract->tenant->id,
                        'contactPerson' => $contract->tenant->contact_person,
                        'businessName' => $contract->tenant->business_name,
                        'contactNumber' => $contract->tenant->contact_number,
                    ],
                    'rentalSpace' => [
                        'id' => $contract->rentalSpace->id,
                        'spaceCode' => $contract->rentalSpace->space_code,
                        'spaceType' => $contract->rentalSpace->space_type,
                        'sizeSqm' => $contract->rentalSpace->size_sqm,
                    ],
                    'startDate' => $contract->start_date?->format('Y-m-d'),
                    'endDate' => $contract->end_date?->format('Y-m-d'),
                    'monthlyRental' => $contract->monthly_rental,
                    'securityDeposit' => $contract->deposit_amount,
                    'status' => $contract->status,
                    'terms' => $contract->terms,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contract not found'
            ], 404);
        }
    }

    /**
     * Download lease PDF for a contract
     */
    public function downloadLease($id)
    {
        try {
            $contract = Contract::with(['tenant.user', 'rentalSpace'])->findOrFail($id);

            // Prepare data for the lease document
            $data = [
                'contractNumber' => $contract->contract_number,
                'contractDate' => $contract->created_at->format('F d, Y'),
                'startDate' => $contract->start_date->format('F d, Y'),
                'endDate' => $contract->end_date->format('F d, Y'),
                'tenantName' => $contract->tenant->contact_person,
                'tenantCompany' => $contract->tenant->business_name,
                'tenantAddress' => $contract->tenant->business_address ?? 'Not provided',
                'tenantPhone' => $contract->tenant->contact_number,
                'spaceName' => $contract->rentalSpace->name,
                'spaceCode' => $contract->rentalSpace->space_code,
                'spaceType' => $contract->rentalSpace->space_type,
                'spaceSqm' => $contract->rentalSpace->size_sqm ?? 'N/A',
                'monthlyRent' => number_format($contract->monthly_rental, 2),
                'securityDeposit' => number_format($contract->deposit_amount ?? 0, 2),
                'terms' => $contract->terms ?? 'Standard lease terms apply. Tenant agrees to maintain the premises in good condition and pay rent on time.',
                'totalDurationMonths' => $contract->start_date->diffInMonths($contract->end_date),
            ];

            // Generate PDF
            $pdf = \PDF::loadView('contracts.lease', $data);
            
            // Return PDF to view in browser (for QR scanning on mobile)
            return $pdf->stream('lease-' . $contract->contract_number . '.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate lease: ' . $e->getMessage()
            ], 500);
        }
    }
}
