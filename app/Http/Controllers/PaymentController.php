<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Contract;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['tenant.user', 'contract.rentalSpace']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($q) use ($search) {
                        $q->where('business_name', 'like', "%{$search}%")
                            ->orWhere('tenant_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('contract', function ($q) use ($search) {
                        $q->where('contract_number', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tenant
        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Filter by contract
        if ($request->has('contract_id')) {
            $query->where('contract_id', $request->contract_id);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $payments = $query->paginate($request->get('per_page', 15));

        AuditLog::log('view', 'Payment', null, 'Viewed payment list');

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Display the specified payment
     */
    public function show($id)
    {
        $payment = Payment::with([
            'tenant.user',
            'contract.rentalSpace'
        ])->findOrFail($id);

        AuditLog::log('view', 'Payment', $payment->id, "Viewed payment: {$payment->payment_number}");

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Record a payment
     */
    public function recordPayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,check,bank_transfer',
            'reference_number' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::findOrFail($id);

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already marked as paid'
            ], 422);
        }

        $oldValues = $payment->toArray();

        // Calculate interest if overdue
        if ($payment->isOverdue()) {
            $payment->calculateInterest();
        }

        if ($request->amount > $payment->balance) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds balance'
            ], 422);
        }

        // Record the payment
        $payment->recordPayment(
            $request->amount,
            $request->payment_method,
            $request->reference_number,
            $request->remarks
        );

        // Create notification
        Notification::create([
            'user_id' => $payment->tenant->user_id,
            'type' => 'payment_received',
            'title' => 'Payment Received',
            'message' => "Payment of ₱" . number_format($request->amount, 2) . " received for {$payment->payment_number}",
            'data' => ['payment_id' => $payment->id],
        ]);

        AuditLog::log('update', 'Payment', $payment->id, "Recorded payment of ₱{$request->amount} for {$payment->payment_number}", $oldValues, $payment->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data' => $payment->fresh(['tenant.user', 'contract.rentalSpace'])
        ]);
    }

    /**
     * Update payment status manually
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,paid,partial,overdue',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::findOrFail($id);
        $oldStatus = $payment->status;
        $payment->status = $request->status;
        $payment->save();

        AuditLog::log('update', 'Payment', $payment->id, "Updated payment status from {$oldStatus} to {$request->status}", ['status' => $oldStatus], ['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'data' => $payment
        ]);
    }

    /**
     * Calculate overdue payments and apply interest
     */
    public function calculateOverduePayments()
    {
        $overduePayments = Payment::where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->get();

        $count = 0;
        foreach ($overduePayments as $payment) {
            $payment->calculateInterest();
            $count++;

            // Create notification if not already sent
            $existingNotification = Notification::where('user_id', $payment->tenant->user_id)
                ->where('type', 'payment_overdue')
                ->where('data->payment_id', $payment->id)
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if (!$existingNotification) {
                Notification::create([
                    'user_id' => $payment->tenant->user_id,
                    'type' => 'payment_overdue',
                    'title' => 'Payment Overdue',
                    'message' => "Payment {$payment->payment_number} is overdue. Please pay immediately to avoid additional charges.",
                    'data' => ['payment_id' => $payment->id],
                ]);
            }
        }

        AuditLog::log('system', 'Payment', null, "Calculated interest for {$count} overdue payments");

        return response()->json([
            'success' => true,
            'message' => "Processed {$count} overdue payments",
            'count' => $count
        ]);
    }

    /**
     * Get payment summary by tenant
     */
    public function getTenantPaymentSummary($tenantId)
    {
        $summary = [
            'total_payments' => Payment::where('tenant_id', $tenantId)->count(),
            'paid_payments' => Payment::where('tenant_id', $tenantId)->where('status', 'paid')->count(),
            'pending_payments' => Payment::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'overdue_payments' => Payment::where('tenant_id', $tenantId)->where('status', 'overdue')->count(),
            'total_paid' => Payment::where('tenant_id', $tenantId)->where('status', 'paid')->sum('amount_paid'),
            'total_balance' => Payment::where('tenant_id', $tenantId)->whereIn('status', ['pending', 'overdue', 'partial'])->sum('balance'),
            'total_interest_charged' => Payment::where('tenant_id', $tenantId)->where('status', 'overdue')->sum('interest_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory($tenantId)
    {
        $payments = Payment::with('contract.rentalSpace')
            ->where('tenant_id', $tenantId)
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }
}
