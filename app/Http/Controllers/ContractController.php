<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Tenant;
use App\Models\RentalSpace;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
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

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by rental space type
        if ($request->has('space_type')) {
            $query->whereHas('rentalSpace', function ($q) use ($request) {
                $q->where('space_type', $request->space_type);
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
        $validator = Validator::make($request->all(), [
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
        $rentalSpace = RentalSpace::find($request->rental_space_id);
        if ($rentalSpace->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Rental space is not available'
            ], 422);
        }

        // Check if tenant has existing active contract for the same space
        $existingContract = Contract::where('tenant_id', $request->tenant_id)
            ->where('rental_space_id', $request->rental_space_id)
            ->where('status', 'active')
            ->exists();

        if ($existingContract) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant already has an active contract for this space'
            ], 422);
        }

        // Calculate end date
        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addMonths($request->duration_months)->subDay();

        // Handle file upload
        $contractFile = null;
        if ($request->hasFile('contract_file')) {
            $contractFile = $request->file('contract_file')->store('contracts', 'public');
        }

        // Create contract
        $contract = Contract::create([
            'contract_number' => 'CON-' . date('Y') . '-' . str_pad(Contract::withTrashed()->count() + 1, 6, '0', STR_PAD_LEFT),
            'tenant_id' => $request->tenant_id,
            'rental_space_id' => $request->rental_space_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_months' => $request->duration_months,
            'monthly_rental' => $request->monthly_rental,
            'deposit_amount' => $request->deposit_amount ?? 0,
            'interest_rate' => $request->interest_rate ?? 2, // Default 2% monthly interest
            'terms_conditions' => $request->terms_conditions,
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
        $contract = Contract::with([
            'tenant.user',
            'rentalSpace',
            'payments'
        ])->findOrFail($id);

        AuditLog::log('view', 'Contract', $contract->id, "Viewed contract: {$contract->contract_number}");

        return response()->json([
            'success' => true,
            'data' => $contract
        ]);
    }

    /**
     * Update the specified contract
     */
    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        $validator = Validator::make($request->all(), [
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
        if ($request->has('start_date') || $request->has('duration_months')) {
            $startDate = $request->has('start_date') ? Carbon::parse($request->start_date) : $contract->start_date;
            $duration = $request->has('duration_months') ? $request->duration_months : $contract->duration_months;
            $contract->end_date = $startDate->copy()->addMonths($duration)->subDay();
        }

        $contract->fill($request->except('contract_file'))->save();

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
        $contract = Contract::findOrFail($id);

        if ($contract->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending contracts can be activated'
            ], 422);
        }

        $oldStatus = $contract->status;
        $contract->status = 'active';
        $contract->save();

        // Update rental space status to occupied
        $rentalSpace = $contract->rentalSpace;
        $rentalSpace->status = 'occupied';
        $rentalSpace->save();

        // Generate payment schedule
        $contract->generatePaymentSchedule();

        // Create notification for tenant
        $tenant = $contract->tenant;
        Notification::create([
            'user_id' => $tenant->user_id,
            'type' => 'contract_activated',
            'title' => 'Contract Activated',
            'message' => "Your contract {$contract->contract_number} for {$rentalSpace->name} has been activated.",
            'data' => ['contract_id' => $contract->id],
        ]);

        AuditLog::log('update', 'Contract', $contract->id, "Activated contract: {$contract->contract_number}", ['status' => $oldStatus], ['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Contract activated successfully',
            'data' => $contract->load(['tenant.user', 'rentalSpace', 'payments'])
        ]);
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

        $contract = Contract::findOrFail($id);

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

        // Update rental space status to available
        $rentalSpace = $contract->rentalSpace;
        $rentalSpace->status = 'available';
        $rentalSpace->save();

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
}
