<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants
     */
    public function index(Request $request)
    {
        $query = Tenant::with('user');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tenant_code', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tenants = $query->paginate($request->get('per_page', 15));

        AuditLog::log('view', 'Tenant', null, 'Viewed tenant list');

        return response()->json([
            'success' => true,
            'data' => $tenants
        ]);
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'business_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'tin' => 'nullable|string|max:50',
            'business_address' => 'nullable|string',
            'contact_person' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create user account
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'tenant',
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => 'active',
        ]);

        // Create tenant profile
        $tenant = Tenant::create([
            'user_id' => $user->id,
            'tenant_code' => 'TEN-' . date('Y') . '-' . str_pad(Tenant::count() + 1, 4, '0', STR_PAD_LEFT),
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'tin' => $request->tin,
            'business_address' => $request->business_address,
            'contact_person' => $request->contact_person,
            'contact_number' => $request->contact_number,
            'status' => 'active',
        ]);

        // Generate QR Code
        $this->generateQRCode($tenant);

        AuditLog::log('create', 'Tenant', $tenant->id, "Created tenant: {$tenant->business_name}", null, $tenant->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully',
            'data' => $tenant->load('user')
        ], 201);
    }

    /**
     * Display the specified tenant
     */
    public function show($id)
    {
        $tenant = Tenant::with([
            'user',
            'contracts.rentalSpace',
            'payments',
            'activeContracts',
            'overduePayments'
        ])->findOrFail($id);

        AuditLog::log('view', 'Tenant', $tenant->id, "Viewed tenant: {$tenant->business_name}");

        return response()->json([
            'success' => true,
            'data' => $tenant
        ]);
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'business_name' => 'sometimes|string|max:255',
            'business_type' => 'sometimes|string|max:255',
            'tin' => 'sometimes|string|max:50',
            'business_address' => 'sometimes|string',
            'contact_person' => 'sometimes|string|max:255',
            'contact_number' => 'sometimes|string|max:20',
            'status' => 'sometimes|in:active,inactive,blacklisted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $oldValues = $tenant->toArray();
        $tenant->update($request->all());

        AuditLog::log('update', 'Tenant', $tenant->id, "Updated tenant: {$tenant->business_name}", $oldValues, $tenant->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => $tenant->load('user')
        ]);
    }

    /**
     * Remove the specified tenant
     */
    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenantName = $tenant->business_name;
        
        AuditLog::log('delete', 'Tenant', $tenant->id, "Deleted tenant: {$tenantName}");
        
        $tenant->user->delete(); // This will cascade delete the tenant

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }

    /**
     * Generate QR code for tenant
     */
    public function generateQRCode($tenant)
    {
        $qrData = json_encode([
            'tenant_code' => $tenant->tenant_code,
            'business_name' => $tenant->business_name,
            'contact_person' => $tenant->contact_person,
            'contact_number' => $tenant->contact_number,
        ]);

        $qrCode = QrCode::format('png')
            ->size(300)
            ->generate($qrData);

        $filename = 'qr-' . $tenant->tenant_code . '.png';
        $path = 'qrcodes/' . $filename;

        Storage::disk('public')->put($path, $qrCode);

        $tenant->qr_code = $path;
        $tenant->save();

        return $path;
    }

    /**
     * Get tenant QR code with contract details
     */
    public function getQRCodeWithDetails($id)
    {
        $tenant = Tenant::with(['activeContracts.rentalSpace'])->findOrFail($id);

        $qrData = [
            'tenant_code' => $tenant->tenant_code,
            'business_name' => $tenant->business_name,
            'contact_person' => $tenant->contact_person,
            'contact_number' => $tenant->contact_number,
            'contracts' => $tenant->activeContracts->map(function ($contract) {
                return [
                    'contract_number' => $contract->contract_number,
                    'rental_space' => [
                        'name' => $contract->rentalSpace->name,
                        'type' => $contract->rentalSpace->getSpaceTypeLabel(),
                        'size_sqm' => $contract->rentalSpace->size_sqm,
                        'map_image' => $contract->rentalSpace->map_image,
                    ],
                    'start_date' => $contract->start_date->format('Y-m-d'),
                    'end_date' => $contract->end_date->format('Y-m-d'),
                    'monthly_rental' => $contract->monthly_rental,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $qrData,
            'qr_image' => $tenant->qr_code ? Storage::url($tenant->qr_code) : null,
        ]);
    }

    /**
     * Scan QR code and get tenant details
     */
    public function scanQRCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tenant = Tenant::with(['activeContracts.rentalSpace'])
            ->where('tenant_code', $request->tenant_code)
            ->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        return $this->getQRCodeWithDetails($tenant->id);
    }
}
