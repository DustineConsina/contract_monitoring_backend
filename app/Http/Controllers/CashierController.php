<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashierController extends Controller
{
    /**
     * Get today's collection summary
     */
    public function getTodaysCollection()
    {
        try {
            $today = Carbon::today();
            
            // Get today's recorded payments
            $payments = Payment::whereDate('payment_date', $today)
                ->where('status', 'paid')
                ->get();
            
            $totalCollected = $payments->sum('amount_paid');
            $paymentCount = $payments->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $today->format('Y-m-d'),
                    'total_collected' => number_format($totalCollected, 2),
                    'payment_count' => $paymentCount,
                    'payments' => $payments->map(fn($p) => [
                        'id' => $p->id,
                        'payment_number' => $p->payment_number,
                        'contract_number' => $p->contract->contract_number,
                        'tenant' => $p->contract->tenant->contact_person,
                        'amount' => $p->amount_paid,
                        'method' => $p->payment_method,
                        'recorded_at' => $p->payment_date,
                    ])
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch today\'s collection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending and overdue payments for collection
     */
    public function getCollectibles(Request $request)
    {
        try {
            $status = $request->get('status', 'all'); // all, pending, overdue, paid
            
            // Load contract with tenant details
            $query = Payment::with(['contract.tenant.user']);
            
            if ($status === 'paid') {
                $query->where('status', 'paid');
            } elseif ($status === 'overdue') {
                $query->where('status', 'overdue');
            } elseif ($status === 'pending') {
                $query->whereIn('status', ['pending', 'partial']);
            } else {
                // 'all' - include all statuses
                $query->whereIn('status', ['pending', 'overdue', 'partial', 'paid']);
            }
            
            $payments = $query->orderBy('due_date', 'asc')->get();
            
            // Group by days overdue for priority
            $grouped = $payments->mapToGroups(function($payment) {
                if ($payment->status === 'overdue') {
                    $daysOverdue = Carbon::parse($payment->due_date)->diffInDays(Carbon::today());
                    return [$daysOverdue > 30 ? 'critical' : ($daysOverdue > 7 ? 'urgent' : 'warning') => $payment];
                }
                return ['pending' => $payment];
            });
            
            // Map payments with guaranteed calculations
            $mappedPayments = $payments->map(function($p) {
                // amount_due should come from payment record, fallback to contract's monthly_rental
                $amountDue = floatval($p->amount_due ?? 0);
                if ($amountDue <= 0 && $p->contract) {
                    $amountDue = floatval($p->contract->monthly_rental ?? 0);
                }
                
                // Calculate interest if not set
                $interestAmount = floatval($p->interest_amount ?? 0);
                if ($interestAmount == 0 && $amountDue > 0) {
                    $interestAmount = $amountDue * 0.03;
                }
                
                // Calculate total if not set
                $totalAmount = floatval($p->total_amount ?? 0);
                if ($totalAmount == 0) {
                    $totalAmount = $amountDue + $interestAmount;
                }
                
                // Calculate balance
                $amountPaid = floatval($p->amount_paid ?? 0);
                $balance = $totalAmount - $amountPaid;
                
                // Get tenant name - try multiple locations
                $tenantName = 'N/A';
                if ($p->contract && $p->contract->tenant) {
                    $tenantName = $p->contract->tenant->contact_person ?? 
                                 $p->contract->tenant->user->name ?? 
                                 $p->contract->tenant->business_name ?? 
                                 'N/A';
                }
                
                return [
                    'id' => $p->id,
                    'payment_number' => $p->payment_number,
                    'contract_number' => $p->contract->contract_number ?? 'N/A',
                    'tenant' => $tenantName,
                    'amount_due' => $amountDue,
                    'interest' => $interestAmount,
                    'total' => $totalAmount,
                    'balance' => $balance,
                    'due_date' => $p->due_date,
                    'status' => $p->status,
                    'days_overdue' => $p->status === 'overdue' ? Carbon::parse($p->due_date)->diffInDays(Carbon::today()) : 0,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'critical' => (isset($grouped['critical']) ? $grouped['critical']->count() : 0) . ' payments (30+ days overdue)',
                    'urgent' => (isset($grouped['urgent']) ? $grouped['urgent']->count() : 0) . ' payments (7-30 days overdue)',
                    'warning' => (isset($grouped['warning']) ? $grouped['warning']->count() : 0) . ' payments (overdue)',
                    'pending' => (isset($grouped['pending']) ? $grouped['pending']->count() : 0) . ' payments (pending)',
                    'total_balance' => $mappedPayments->sum('balance'),
                    'payments' => $mappedPayments
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch collectibles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record a payment (same as PaymentController but for cashier)
     */
    public function recordPayment(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,check,bank_transfer',
                'reference_number' => 'nullable|string',
                'remarks' => 'nullable|string',
            ]);

            $payment = Payment::findOrFail($id);

            if ($validated['amount'] > $payment->balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds balance due'
                ], 422);
            }

            // Generate reference number if not provided
            $referenceNumber = $validated['reference_number'] ?: $this->generateReferenceNumber($validated['payment_method']);

            $payment->update([
                'amount_paid' => $payment->amount_paid + $validated['amount'],
                'balance' => max(0, $payment->balance - $validated['amount']),
                'payment_method' => $validated['payment_method'],
                'reference_number' => $referenceNumber,
                'remarks' => $validated['remarks'],
                'payment_date' => Carbon::now(),
                'status' => $validated['amount'] >= $payment->balance ? 'paid' : 'partial',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate payment receipt
     */
    public function getReceipt($id)
    {
        try {
            $payment = Payment::with(['contract.tenant', 'contract.rentalSpace'])->findOrFail($id);

            $receiptData = [
                'receipt_number' => 'RCP-' . $payment->id . '-' . date('Ymd'),
                'payment_date' => $payment->payment_date ? Carbon::parse($payment->payment_date)->format('F d, Y H:i A') : Carbon::now()->format('F d, Y H:i A'),
                'contract_number' => $payment->contract->contract_number,
                'tenant_name' => $payment->contract->tenant->contact_person,
                'tenant_company' => $payment->contract->tenant->business_name,
                'space' => $payment->contract->rentalSpace->space_code,
                'payment_for' => $payment->billing_period_start . ' to ' . $payment->billing_period_end,
                'amount_due' => $payment->amount_due,
                'interest' => $payment->interest_amount,
                'total_due' => $payment->total_amount,
                'amount_paid' => $payment->amount_paid,
                'balance' => $payment->balance,
                'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
                'reference' => $payment->reference_number,
                'status' => ucfirst($payment->status),
            ];

            return response()->json([
                'success' => true,
                'data' => $receiptData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique reference number for payment
     */
    private function generateReferenceNumber($paymentMethod)
    {
        $timestamp = now()->format('YmdHis');
        $random = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        
        $prefix = match($paymentMethod) {
            'cash' => 'CASH',
            'check' => 'CHK',
            'bank_transfer' => 'BANK',
            default => 'PAY'
        };
        
        return "{$prefix}-{$timestamp}-{$random}";
    }
}
