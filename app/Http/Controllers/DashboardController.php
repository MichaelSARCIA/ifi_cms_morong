<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\ServiceRequest;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = [];
        $user = auth()->user();
        $cacheTime = 60 * 60; // 1 hour cache

        // ============================================================
        // 1. KPI CARDS
        // ============================================================

        if ($user->hasModule('service_requests')) {
            $query = DB::table('service_requests');
            if ($user->role === 'Priest') {
                $query->whereIn('status', ['For Priest Review'])
                      ->where(function($q) use ($user) {
                          $q->where('priest_id', $user->id)
                            ->orWhereNull('priest_id');
                      });
            } else {
                $query->whereIn('status', ['For Priest Review']);
            }
            $data['pending_requests_count'] = $query->count();
        }

        // Card 2: Total Services Available
        // Card 2: Total Services Available (Also used for fee mapping on modals)
        if ($user->role !== 'Priest') {
            $data['total_services_list'] = DB::table('service_types')
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['name', 'icon', 'color', 'fee']);
        }

        // Card 3 & 4: Total Collection & Donation Frequencies
        if ($user->hasModule('collections') || $user->hasModule('donations') || $user->hasModule('finance')) {
            $data['collection_frequency'] = DB::table('donations')
                ->whereIn('type', ['Collection', 'Sunday Collection', 'Mass Offering', 'Special Collection', 'Other'])
                ->count();

            $data['donation_frequency'] = DB::table('donations')
                ->whereIn('type', ['Donation', 'General Donation', 'Tithes', 'Love Offering', 'Others', 'Other'])
                ->count();
        }

        // ============================================================
        // 2. CHARTS — Server-side GROUP BY aggregation
        // ============================================================

        // Service Requests Charts (Pie + Activity Combo Chart)
        if ($user->hasModule('service_requests') && $user->role !== 'Priest') {
            $startDate = $request->input('start_date', date('Y-01-01'));
            $endDate = $request->input('end_date', date('Y-12-31'));
            $data['start_date'] = $startDate;
            $data['end_date'] = $endDate;

            $diffInDays = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate));
            $groupBy = $diffInDays <= 31 ? 'day' : 'month';
            $data['group_by'] = $groupBy;



            // Combo Chart: GROUP BY YEAR/MONTH or DATE directly in MySQL
            if ($groupBy === 'day') {
                $data['request_activity_data'] = DB::table('service_requests')
                    ->select(DB::raw('DATE(created_at) as date'), 'service_type', DB::raw('COUNT(*) as total'))
                    ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->groupBy('date', 'service_type')
                    ->orderBy('date')
                    ->get();
            } else {
                $data['request_activity_data'] = DB::table('service_requests')
                    ->select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), 'service_type', DB::raw('COUNT(*) as total'))
                    ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->groupBy('year', 'month', 'service_type')
                    ->orderBy('year')->orderBy('month')
                    ->get();
            }
        }

        // Financial Activity Trends — gated to finance/donations module
        if ($user->hasModule('collections') || $user->hasModule('donations') || $user->hasModule('finance')) {
            $finStartDate = $request->input('fin_start_date', date('Y-01-01'));
            $finEndDate = $request->input('fin_end_date', date('Y-12-31'));
            $data['fin_start_date'] = $finStartDate;
            $data['fin_end_date'] = $finEndDate;
            $diffInDaysFin = \Carbon\Carbon::parse($finStartDate)->diffInDays(\Carbon\Carbon::parse($finEndDate));
            $finGroupBy = $diffInDaysFin <= 31 ? 'day' : 'month';
            $data['fin_group_by'] = $finGroupBy;

            $collectionsQuery = DB::table('donations')
                ->whereIn('type', ['Collection', 'Sunday Collection', 'Mass Offering', 'Special Collection', 'Other'])
                ->whereBetween('date_received', [$finStartDate, $finEndDate]);

            $donationsQuery = DB::table('donations')
                ->whereIn('type', ['Donation', 'General Donation', 'Tithes', 'Love Offering', 'Others', 'Other'])
                ->whereBetween('date_received', [$finStartDate, $finEndDate]);

            $feesQuery = DB::table('payments')
                ->whereBetween('paid_at', [$finStartDate . ' 00:00:00', $finEndDate . ' 23:59:59']);

            if ($finGroupBy === 'day') {
                $data['trend_collections'] = $collectionsQuery
                    ->select(DB::raw('date_received as date'), DB::raw('COUNT(*) as total'))
                    ->groupBy('date_received')->orderBy('date_received')->get();

                $data['trend_donations'] = $donationsQuery
                    ->select(DB::raw('date_received as date'), DB::raw('COUNT(*) as total'))
                    ->groupBy('date_received')->orderBy('date_received')->get();

                $data['trend_fees'] = $feesQuery
                    ->select(DB::raw('DATE(paid_at) as date'), DB::raw('COUNT(*) as total'))
                    ->groupBy('date')->orderBy('date')->get();
            } else {
                $data['trend_collections'] = $collectionsQuery
                    ->select(DB::raw('MONTH(date_received) as month'), DB::raw('YEAR(date_received) as year'), DB::raw('COUNT(*) as total'))
                    ->groupBy('year', 'month')->orderBy('year')->orderBy('month')->get();

                $data['trend_donations'] = $donationsQuery
                    ->select(DB::raw('MONTH(date_received) as month'), DB::raw('YEAR(date_received) as year'), DB::raw('COUNT(*) as total'))
                    ->groupBy('year', 'month')->orderBy('year')->orderBy('month')->get();

                $data['trend_fees'] = $feesQuery
                    ->select(DB::raw('MONTH(paid_at) as month'), DB::raw('YEAR(paid_at) as year'), DB::raw('COUNT(*) as total'))
                    ->groupBy('year', 'month')->orderBy('year')->orderBy('month')->get();
            }
        }

        // ============================================================
        // 3. LISTS — Strictly limited, no full table scans
        // ============================================================

        // Recent Activity Logs — only for Admin role (as requested)
        if ($user->role === 'Admin') {
            $data['recent_activities'] = DB::table('audit_logs')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Priest Queue — only for Priest role
        // Shows ALL active (non-completed/cancelled) service requests assigned to this priest
        if ($user->role === 'Priest') {
            $data['priest_queue'] = ServiceRequest::whereIn('status', ['For Priest Review'])
                ->where(function($q) use ($user) {
                    $q->where('priest_id', $user->id)
                      ->orWhereNull('priest_id');
                })
                ->orderBy('created_at', 'desc')
                ->get(['id', 'first_name', 'last_name', 'middle_name', 'email', 'details', 'requirements', 'service_type', 'scheduled_date', 'scheduled_time', 'contact_number', 'status', 'payment_status', 'created_at', 'custom_data']);
        }

        // Services Fees Queue - ONLY for Treasurer
        if ($user->role === 'Treasurer') {
            $data['services_fees_queue'] = ServiceRequest::leftJoin('payments', 'service_requests.id', '=', 'payments.service_request_id')
                ->leftJoin('service_types', 'service_requests.service_type', '=', 'service_types.name')
                ->where(function ($q) {
                    $q->where('service_requests.status', 'For Payment')
                      ->orWhere('service_requests.payment_status', 'Paid');
                })
                ->where('service_requests.status', '!=', 'Cancelled')
                ->orderBy('service_requests.created_at', 'desc')
                ->limit(50)
                ->get([
                    'service_requests.id', 
                    'service_requests.first_name', 
                    'service_requests.last_name', 
                    'service_requests.middle_name',
                    'service_requests.email',
                    'service_requests.details',
                    'service_requests.requirements',
                    'service_requests.service_type', 
                    'service_requests.scheduled_date', 
                    'service_requests.scheduled_time', 
                    'service_requests.contact_number', 
                    'service_requests.status', 
                    'service_requests.payment_status', 
                    'service_requests.created_at',
                    'payments.id as payment_id',
                    'custom_data',
                    'service_types.payment_methods as allowed_payment_methods',
                    'service_types.id as service_type_config_id'
                ]);
        }

        // Upcoming Schedules — for users with service_requests module
        if ($user->hasModule('service_requests')) {
            $manualSchedules = DB::table('schedules')
                ->where('status', 'Scheduled')
                ->where(DB::raw('DATE(start_datetime)'), '>=', now()->toDateString())
                ->get(['id', 'title', 'start_datetime', 'type'])
                ->map(function ($item) {
                    return (object)[
                        'category' => 'manual',
                        'id' => $item->id,
                        'title' => $item->title,
                        'service_type' => $item->type,
                        'start_datetime' => $item->start_datetime,
                    ];
                });

            $serviceRequests = ServiceRequest::where(function ($q) {
                    $q->where('status', 'Approved')
                      ->orWhere(function($sub) {
                          $sub->where('payment_status', 'Paid')
                              ->whereNotIn('status', ['Cancelled', 'Completed']);
                      });
                })
                ->where('scheduled_date', '>=', now()->toDateString())
                ->get(['id', 'first_name', 'last_name', 'middle_name', 'service_type', 'scheduled_date', 'scheduled_time', 'custom_data'])
                ->map(function ($item) {
                    $dateStr = $item->scheduled_date instanceof \Carbon\Carbon ? $item->scheduled_date->format('Y-m-d') : $item->scheduled_date;
                    $dateTimeStr = $dateStr . ' ' . ($item->scheduled_time ?? '00:00:00');
                    $fullName = trim(implode(' ', array_filter([
                        $item->first_name,
                        $item->middle_name,
                        $item->last_name,
                    ]))) ?: $item->applicant_name;
                    return (object)[
                        'category' => 'request',
                        'id' => $item->id,
                        'title' => $fullName,
                        'service_type' => $item->service_type,
                        'start_datetime' => $dateTimeStr,
                    ];
                });

            $data['upcoming_schedules'] = $manualSchedules->concat($serviceRequests)
                ->sortBy('start_datetime')
                ->take(5)
                ->values();
        }

        $data['payment_methods'] = PaymentMethod::active()->orderBy('sort_order')->orderBy('id')->get();

        $data['all_service_types'] = \App\Models\ServiceType::orderBy('name')->pluck('name');

        return view('dashboard', compact('data'));
    }
}
