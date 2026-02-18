<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\RentalSpace;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Generate contracts report
     */
    public function contractsReport(Request $request)
    {
        $query = Contract::with(['tenant.user', 'rentalSpace']);

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'all') {
                // Do nothing, get all statuses
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }

        // Filter by space type
        if ($request->has('space_type')) {
            $query->whereHas('rentalSpace', function ($q) use ($request) {
                $q->where('space_type', $request->space_type);
            });
        }

        $contracts = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_contracts' => $contracts->count(),
            'active_contracts' => $contracts->where('status', 'active')->count(),
            'expired_contracts' => $contracts->where('status', 'expired')->count(),
            'terminated_contracts' => $contracts->where('status', 'terminated')->count(),
            'pending_contracts' => $contracts->where('status', 'pending')->count(),
            'total_monthly_revenue' => $contracts->where('status', 'active')->sum('monthly_rental'),
        ];

        AuditLog::log('view', 'Report', null, 'Generated contracts report');

        if ($request->has('format') && $request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.contracts', [
                'contracts' => $contracts,
                'summary' => $summary,
                'filters' => $request->all(),
            ]);
            return $pdf->download('contracts-report-' . date('Y-m-d') . '.pdf');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'contracts' => $contracts,
                'summary' => $summary,
            ]
        ]);
    }

    /**
     * Generate payments report
     */
    public function paymentsReport(Request $request)
    {
        $query = Payment::with(['tenant.user', 'contract.rentalSpace']);

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'all') {
                // Do nothing
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        // Filter by tenant
        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $summary = [
            'total_payments' => $payments->count(),
            'paid_payments' => $payments->where('status', 'paid')->count(),
            'pending_payments' => $payments->where('status', 'pending')->count(),
            'overdue_payments' => $payments->where('status', 'overdue')->count(),
            'partial_payments' => $payments->where('status', 'partial')->count(),
            'total_collected' => $payments->where('status', 'paid')->sum('amount_paid'),
            'total_pending' => $payments->whereIn('status', ['pending', 'overdue', 'partial'])->sum('balance'),
            'total_interest_charged' => $payments->sum('interest_amount'),
        ];

        AuditLog::log('view', 'Report', null, 'Generated payments report');

        if ($request->has('format') && $request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.payments', [
                'payments' => $payments,
                'summary' => $summary,
                'filters' => $request->all(),
            ]);
            return $pdf->download('payments-report-' . date('Y-m-d') . '.pdf');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $payments,
                'summary' => $summary,
            ]
        ]);
    }

    /**
     * Generate delinquency report
     */
    public function delinquencyReport(Request $request)
    {
        $overduePayments = Payment::with(['tenant.user', 'contract.rentalSpace'])
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc')
            ->get();

        $delinquentTenants = $overduePayments->groupBy('tenant_id')->map(function ($payments, $tenantId) {
            $tenant = Tenant::with('user')->find($tenantId);
            return [
                'tenant' => $tenant,
                'overdue_count' => $payments->count(),
                'total_balance' => $payments->sum('balance'),
                'total_interest' => $payments->sum('interest_amount'),
                'oldest_due_date' => $payments->min('due_date'),
                'payments' => $payments,
            ];
        })->sortByDesc('total_balance')->values();

        $summary = [
            'total_delinquent_tenants' => $delinquentTenants->count(),
            'total_overdue_payments' => $overduePayments->count(),
            'total_outstanding_balance' => $overduePayments->sum('balance'),
            'total_interest_charges' => $overduePayments->sum('interest_amount'),
        ];

        AuditLog::log('view', 'Report', null, 'Generated delinquency report');

        if ($request->has('format') && $request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.delinquency', [
                'delinquentTenants' => $delinquentTenants,
                'summary' => $summary,
            ]);
            return $pdf->download('delinquency-report-' . date('Y-m-d') . '.pdf');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'delinquent_tenants' => $delinquentTenants,
                'summary' => $summary,
            ]
        ]);
    }

    /**
     * Generate revenue report
     */
    public function revenueReport(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month');

        $query = Payment::where('status', 'paid');

        if ($month) {
            $query->whereYear('payment_date', $year)
                  ->whereMonth('payment_date', $month);
        } else {
            $query->whereYear('payment_date', $year);
        }

        $payments = $query->with(['tenant.user', 'contract.rentalSpace'])->get();

        // Monthly breakdown
        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthRevenue = Payment::where('status', 'paid')
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $m)
                ->sum('amount_paid');
            
            $monthlyRevenue[] = [
                'month' => Carbon::create($year, $m, 1)->format('F'),
                'revenue' => $monthRevenue,
            ];
        }

        // By space type
        $revenueBySpaceType = [
            'food_stall' => Payment::where('status', 'paid')
                ->whereYear('payment_date', $year)
                ->whereHas('contract.rentalSpace', function ($q) {
                    $q->where('space_type', 'food_stall');
                })
                ->sum('amount_paid'),
            'market_hall' => Payment::where('status', 'paid')
                ->whereYear('payment_date', $year)
                ->whereHas('contract.rentalSpace', function ($q) {
                    $q->where('space_type', 'market_hall');
                })
                ->sum('amount_paid'),
            'banera_warehouse' => Payment::where('status', 'paid')
                ->whereYear('payment_date', $year)
                ->whereHas('contract.rentalSpace', function ($q) {
                    $q->where('space_type', 'banera_warehouse');
                })
                ->sum('amount_paid'),
        ];

        $summary = [
            'total_revenue' => $payments->sum('amount_paid'),
            'payment_count' => $payments->count(),
            'average_payment' => $payments->count() > 0 ? $payments->sum('amount_paid') / $payments->count() : 0,
        ];

        AuditLog::log('view', 'Report', null, 'Generated revenue report');

        if ($request->has('format') && $request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.revenue', [
                'payments' => $payments,
                'monthlyRevenue' => $monthlyRevenue,
                'revenueBySpaceType' => $revenueBySpaceType,
                'summary' => $summary,
                'year' => $year,
                'month' => $month,
            ]);
            return $pdf->download('revenue-report-' . $year . ($month ? '-' . str_pad($month, 2, '0', STR_PAD_LEFT) : '') . '.pdf');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $payments,
                'monthly_revenue' => $monthlyRevenue,
                'revenue_by_space_type' => $revenueBySpaceType,
                'summary' => $summary,
            ]
        ]);
    }

    /**
     * Generate tenants report
     */
    public function tenantsReport(Request $request)
    {
        $query = Tenant::with(['user', 'activeContracts.rentalSpace']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tenants = $query->get()->map(function ($tenant) {
            return [
                'tenant' => $tenant,
                'active_contracts_count' => $tenant->activeContracts->count(),
                'total_contracts_count' => $tenant->contracts->count(),
                'pending_payments_count' => $tenant->payments()->where('status', 'pending')->count(),
                'overdue_payments_count' => $tenant->overduePayments()->count(),
                'total_balance' => $tenant->payments()->whereIn('status', ['pending', 'overdue', 'partial'])->sum('balance'),
            ];
        });

        $summary = [
            'total_tenants' => $tenants->count(),
            'active_tenants' => $tenants->where('tenant.status', 'active')->count(),
            'inactive_tenants' => $tenants->where('tenant.status', 'inactive')->count(),
            'blacklisted_tenants' => $tenants->where('tenant.status', 'blacklisted')->count(),
            'tenants_with_active_contracts' => $tenants->where('active_contracts_count', '>', 0)->count(),
            'tenants_with_overdue_payments' => $tenants->where('overdue_payments_count', '>', 0)->count(),
        ];

        AuditLog::log('view', 'Report', null, 'Generated tenants report');

        if ($request->has('format') && $request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.tenants', [
                'tenants' => $tenants,
                'summary' => $summary,
            ]);
            return $pdf->download('tenants-report-' . date('Y-m-d') . '.pdf');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenants' => $tenants,
                'summary' => $summary,
            ]
        ]);
    }

    /**
     * Generate expiring contracts report
     */
    public function expiringContractsReport(Request $request)
    {
        $days = $request->get('days', 30); // Default to 30 days

        $expiringContracts = Contract::with(['tenant.user', 'rentalSpace'])
            ->where('status', 'active')
            ->where('end_date', '>=', Carbon::now())
            ->where('end_date', '<=', Carbon::now()->addDays($days))
            ->orderBy('end_date', 'asc')
            ->get();

        $summary = [
            'total_expiring' => $expiringContracts->count(),
            'next_7_days' => $expiringContracts->where('end_date', '<=', Carbon::now()->addDays(7))->count(),
            'next_14_days' => $expiringContracts->where('end_date', '<=', Carbon::now()->addDays(14))->count(),
            'next_30_days' => $expiringContracts->where('end_date', '<=', Carbon::now()->addDays(30))->count(),
        ];

        AuditLog::log('view', 'Report', null, 'Generated expiring contracts report');

        if ($request->has('format') && $request->format === 'pdf') {
            $pdf = Pdf::loadView('reports.expiring-contracts', [
                'expiringContracts' => $expiringContracts,
                'summary' => $summary,
                'days' => $days,
            ]);
            return $pdf->download('expiring-contracts-report-' . date('Y-m-d') . '.pdf');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'expiring_contracts' => $expiringContracts,
                'summary' => $summary,
            ]
        ]);
    }

    /**
     * Generate audit log report
     */
    public function auditLogReport(Request $request)
    {
        $query = AuditLog::with('user');

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->paginate(100);

        AuditLog::log('view', 'Report', null, 'Generated audit log report');

        return response()->json([
            'success' => true,
            'data' => $auditLogs
        ]);
    }
}
