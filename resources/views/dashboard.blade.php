@extends('layouts.app')
@use('App\Helpers\ServiceHelper')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Overview of Parish activities')
@section('role_label', Auth::user()->role)

@section('content')

    {{-- Skeleton Loader CSS --}}
    <style>
        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 0.75rem;
        }

        .dark .skeleton {
            background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
    </style>

    {{-- KPI Cards Section --}}
    <div x-data="{ loaded: false }" x-init="$nextTick(() => { loaded = true })">

        {{-- Skeleton KPI Cards (shown until DOM ready) --}}
        <div x-show="!loaded" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @for ($i = 0; $i < 4; $i++)
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-start">
                        <div class="space-y-3 flex-1">
                            <div class="skeleton h-3 w-24 rounded"></div>
                            <div class="skeleton h-8 w-16 rounded"></div>
                            <div class="skeleton h-2 w-32 rounded"></div>
                        </div>
                        <div class="skeleton w-12 h-12 rounded-xl"></div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Real KPI Cards --}}
        <div x-show="loaded" x-cloak class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">



            @if(isset($data['pending_requests_count']))
                <!-- For Priest Review Requests -->
                <a href="{{ route('service-requests.index', ['status' => 'For Priest Review']) }}"
                    class="block h-full bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300 group">
                    <div class="flex justify-between items-center text-left">
                        <div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">For Priest Review</p>
                            <h3
                                class="text-3xl font-extrabold text-gray-900 dark:text-white group-hover:text-amber-600 transition-colors">
                                {{ $data['pending_requests_count'] }}
                            </h3>
                            <p class="text-[15px] text-gray-600 dark:text-gray-300 mt-1 font-medium italic">Awaiting Review</p>
                        </div>
                        <div
                            class="p-4 bg-amber-50 dark:bg-amber-900/30 rounded-2xl group-hover:bg-amber-100 transition-colors text-amber-500 shadow-sm border border-amber-100/50 dark:border-amber-800/30">
                            <i class="fas fa-clipboard-list text-2xl"></i>
                        </div>
                    </div>
                </a>
            @endif

            @if(isset($data['total_services_list']) && Auth::user()->role === 'Admin')
                <!-- Total Services -->
                <a href="{{ route('system-settings.index', ['tab' => 'services']) }}"
                    class="block h-full bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300 group">
                    <div class="flex justify-between items-center text-left">
                        <div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Services</p>
                            <h3
                                class="text-3xl font-extrabold text-gray-900 dark:text-white group-hover:text-indigo-600 transition-colors">
                                {{ count($data['total_services_list']) }}
                            </h3>
                            <p class="text-[15px] text-gray-600 dark:text-gray-300 mt-1 font-medium italic">Available Services</p>
                        </div>
                        <div
                            class="p-4 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl group-hover:bg-indigo-100 transition-colors text-indigo-500 shadow-sm border border-indigo-100/50 dark:border-indigo-800/30">
                            <i class="fas fa-church text-2xl"></i>
                        </div>
                    </div>
                </a>
            @endif

            @if(isset($data['collection_frequency']))
                <!-- Collection Frequency -->
                <a href="{{ route('collections') }}"
                    class="block h-full bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300 group">
                    <div class="flex justify-between items-center text-left">
                        <div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Collections</p>
                            <h3
                                class="text-3xl font-extrabold text-gray-900 dark:text-white group-hover:text-emerald-600 transition-colors">
                                {{ $data['collection_frequency'] }}
                            </h3>
                            <p class="text-[15px] text-gray-600 dark:text-gray-300 mt-1 font-medium italic">This Year</p>
                        </div>
                        <div
                            class="p-4 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl group-hover:bg-emerald-100 transition-colors text-emerald-500 shadow-sm border border-emerald-100/50 dark:border-emerald-800/30">
                            <i class="fas fa-hand-holding-heart text-2xl"></i>
                        </div>
                    </div>
                </a>
            @endif

            @if(isset($data['donation_frequency']))
                <!-- Donation Frequency -->
                <a href="{{ route('donations') }}"
                    class="block h-full bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300 group">
                    <div class="flex justify-between items-center text-left">
                        <div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Donations</p>
                            <h3
                                class="text-3xl font-extrabold text-gray-900 dark:text-white group-hover:text-sky-600 transition-colors">
                                {{ $data['donation_frequency'] }}
                            </h3>
                            <p class="text-[15px] text-gray-600 dark:text-gray-300 mt-1 font-medium italic">This Year</p>
                        </div>
                        <div
                            class="p-4 bg-sky-50 dark:bg-sky-900/30 rounded-2xl group-hover:bg-sky-100 transition-colors text-sky-500 shadow-sm border border-sky-100/50 dark:border-sky-800/30">
                            <i class="fas fa-gift text-2xl"></i>
                        </div>
                    </div>
                </a>
            @endif

        </div> {{-- end real KPI cards --}}
    </div> {{-- end Alpine KPI wrapper --}}





    @php
        $hasRightColumn = isset($data['recent_activities']) && count($data['recent_activities']) > 0;
    @endphp

    <div class="grid grid-cols-1 {{ $hasRightColumn ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-8 mb-8">

        <!-- Main Content Area -->
        <div class="{{ $hasRightColumn ? 'lg:col-span-2' : 'lg:col-span-1' }} space-y-8">

            @if(isset($data['priest_queue']))
                {{-- Pending Services Data Grid (Priest Only) --}}
                    <div x-data="{
                        search: '',
                        filterType: '',
                        serviceColors: {{ json_encode(ServiceHelper::getServiceColorMap()) }},
                        getServiceClass(type) {
                            const t = (type || '').toLowerCase();
                            return this.serviceColors[t] || 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
                        },
                        rows: {{ Js::from($data['priest_queue']) }},
                        get filtered() {
                            return this.rows.filter(r => {
                                const name = (r.applicant_name || '').toLowerCase();
                                const type = (r.service_type || '').toLowerCase();
                                const matchSearch = !this.search || name.includes(this.search.toLowerCase()) || type.includes(this.search.toLowerCase());
                                const matchType = !this.filterType || r.service_type === this.filterType;
                                return matchSearch && matchType;
                            });
                        },
                        async approveRequest(row) {
                            window.showConfirm(
                                'Approve Request?',
                                'Are you sure you want to approve this request?',
                                'bg-green-600 hover:bg-green-700',
                                async () => {
                                    try {
                                        const formData = new FormData();
                                        formData.append('_token', '{{ csrf_token() }}');
                                        formData.append('_method', 'PUT');
                                        formData.append('service_type', row.service_type || '');
                                        formData.append('scheduled_date', row.scheduled_date || '');
                                        if (row.scheduled_time) formData.append('scheduled_time', row.scheduled_time);
                                        formData.append('status', 'For Payment');
                                        formData.append('payment_status', row.payment_status || 'Pending');
                                        
                                        const response = await fetch(`{{ url('service-requests') }}/${row.id}`, {
                                            method: 'POST',
                                            body: formData,
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'Accept': 'application/json'
                                            }
                                        });
                                        
                                        if(response.ok) {
                                            row.status = 'For Payment';
                                            // Instantly remove from the grid view
                                            this.rows = this.rows.filter(r => r.id !== row.id);
                                            window.showConfirmModal({
                                                title: 'Success',
                                                message: 'Request confirmed successfully.',
                                                btnClass: 'bg-green-600 hover:bg-green-700',
                                                confirmText: 'Okay',
                                                isAlert: true
                                            });
                                        } else {
                                            window.showConfirmModal({
                                                title: 'Error',
                                                message: 'Failed to confirm request. Please try again.',
                                                btnClass: 'bg-red-600 hover:bg-red-700',
                                                confirmText: 'Okay',
                                                isAlert: true
                                            });
                                        }
                                    } catch (e) {
                                        console.error(e);
                                        window.showConfirmModal({
                                            title: 'Error',
                                            message: 'An unexpected error occurred.',
                                            btnClass: 'bg-red-600 hover:bg-red-700',
                                            confirmText: 'Okay',
                                            isAlert: true
                                        });
                                    }
                                },
                                'Approve'
                            );
                        }
                    }"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
                            <div>
                                <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-lg">
                                        <i class="fas fa-tasks text-sm"></i>
                                    </span>
                                    My Services For Review
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Service requests awaiting your review and approval</p>
                            </div>
                            <a href="{{ route('service-requests.index') }}"
                               class="shrink-0 inline-flex items-center gap-1.5 text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 uppercase tracking-wider transition-colors">
                                View All <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>

                        {{-- Filters Row --}}
                        <div class="mt-3 flex flex-col sm:flex-row gap-2">
                            {{-- Search --}}
                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                                    <i class="fas fa-search text-xs"></i>
                                </span>
                                <input type="text" x-model="search" placeholder="Search by name or service type…"
                                    class="w-full pl-8 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none transition-colors">
                            </div>
                            {{-- Type Filter --}}
                            <div class="relative">
                                <select x-model="filterType"
                                    class="dropdown-btn text-sm w-full sm:w-auto">
                                    <option value="">All Services</option>
                                    @foreach($data['all_service_types'] ?? [] as $stype)
                                        <option value="{{ $stype }}">{{ $stype }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto overflow-y-auto max-h-96">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">#</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">Applicant</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">Service</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">Sched. Date & Time</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">Contact</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-center font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in filtered" :key="row.id">
                                    <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-indigo-50/40 dark:hover:bg-indigo-900/10 transition-colors">
                                        <td class="px-4 py-3 text-gray-400 dark:text-gray-500 font-mono text-xs" x-text="index + 1"></td>
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-gray-800 dark:text-white" x-text="row.applicant_name || 'Guest / Applicant'"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border" :class="getServiceClass(row.service_type)" x-text="row.service_type"></span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                            <div class="flex flex-col gap-0.5">
                                                <span x-text="row.scheduled_date ? new Date(row.scheduled_date.split('T')[0] + 'T00:00:00').toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' }) : '—'"></span>
                                                <span class="text-xs text-gray-400" x-text="row.scheduled_time ? (() => { try { return new Date('1970-01-01T' + row.scheduled_time).toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', hour12: true }); } catch(e) { return row.scheduled_time; } })() : ''"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs" x-text="row.contact_number || '—'"></td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" @click="window.openDetailsModal(row)"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-all shadow-sm"
                                                    title="View Details">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </button>
                                                <button type="button" 
                                                        @click="approveRequest(row)"
                                                        :disabled="['Approved', 'Cancelled', 'Completed', 'For Payment'].includes(row.status)"
                                                        :class="['Approved', 'Cancelled', 'Completed', 'For Payment'].includes(row.status) ? 'bg-gray-400 cursor-not-allowed opacity-70' : 'bg-green-600 hover:bg-green-700'"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white transition-colors shadow-sm"
                                                        title="Approve / Confirm Request">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                {{-- Empty state when filter returns nothing --}}
                                <tr x-show="filtered.length === 0">
                                    <td colspan="6" class="px-6 py-10 text-center">
                                        <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-gray-500">
                                            <i class="fas fa-search text-2xl opacity-40"></i>
                                            <p class="text-sm font-medium">No requests match your filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer count --}}
                    <div class="px-6 py-3 bg-gray-50/60 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs text-gray-400 dark:text-gray-500">
                        <span x-text="'Showing ' + filtered.length + ' of ' + rows.length + ' record' + (rows.length !== 1 ? 's' : '')"></span>
                        <span>Sorted by scheduled date ↑</span>
                    </div>
                </div>
            @endif

            @if(isset($data['services_fees_queue']) && Auth::user()->role !== 'Priest')
                {{-- Services Fees Data Grid (Treasurer Only) --}}
                <div x-data="{
                        search: '',
                        filterStatus: '',
                        filterType: '',
                        filterPayment: '',
                        serviceColors: {{ json_encode(ServiceHelper::getServiceColorMap()) }},
                        serviceFees: {{ json_encode(collect($data['total_services_list'] ?? [])->pluck('fee', 'name')) }},
                        getServiceClass(type) {
                            const t = (type || '').toLowerCase();
                            return this.serviceColors[t] || 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
                        },
                        getFee(type) {
                            return this.serviceFees[type] || 0;
                        },
                        rows: {{ Js::from($data['services_fees_queue'] ?? []) }},
                        get filtered() {
                            return this.rows.filter(r => {
                                const name = (r.applicant_name || '').toLowerCase();
                                const type = (r.service_type || '').toLowerCase();
                                const matchSearch = !this.search || name.includes(this.search.toLowerCase()) || type.includes(this.search.toLowerCase());
                                const matchStatus = !this.filterStatus || r.status === this.filterStatus;
                                const matchPayment = !this.filterPayment || r.payment_status === this.filterPayment;
                                const matchType = !this.filterType || r.service_type === this.filterType;
                                return matchSearch && matchStatus && matchPayment && matchType;
                            });
                        }
                    }"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="font-bold text-lg text-gray-800 dark:text-white flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 rounded-lg">
                                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                                    </span>
                                    Services Fees
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Recent service requests for payment processing</p>
                            </div>
                            <a href="{{ route('donations', ['type' => 'fee']) }}"
                               class="shrink-0 inline-flex items-center gap-1.5 text-sm font-bold text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 uppercase tracking-wider transition-colors">
                                View All <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>

                        {{-- Filters Row --}}
                        <div class="mt-3 flex flex-col sm:flex-row gap-2">
                            {{-- Search --}}
                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                                    <i class="fas fa-search text-xs"></i>
                                </span>
                                <input type="text" x-model="search" placeholder="Search by name or service type…"
                                    class="w-full pl-8 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-green-400 focus:border-green-400 outline-none transition-colors">
                            </div>
                            {{-- Status Filter --}}
                            <select x-model="filterStatus"
                                class="text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-3 py-2 w-full sm:w-auto focus:ring-2 focus:ring-green-400 outline-none transition-colors">
                                <option value="">All Status</option>
                                <option value="For Payment">For Payment</option>
                                <option value="Approved">Approved</option>
                            </select>
                            {{-- Payment Status Filter --}}
                            <select x-model="filterPayment"
                                class="text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-3 py-2 w-full sm:w-auto focus:ring-2 focus:ring-green-400 outline-none transition-colors">
                                <option value="">All Payments</option>
                                <option value="Pending">Pending</option>
                                <option value="Paid">Paid</option>
                            </select>
                            {{-- Type Filter --}}
                            <select x-model="filterType"
                                class="text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-3 py-2 w-full sm:w-auto focus:ring-2 focus:ring-green-400 outline-none transition-colors">
                                <option value="">All Services</option>
                                @foreach($data['all_service_types'] ?? [] as $stype)
                                    <option value="{{ $stype }}">{{ $stype }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto overflow-y-auto max-h-96">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">#</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">Applicant</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">Service</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">Sched. Date & Time</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">Status</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">Payment</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left font-semibold">Contact</th>
                                    <th class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-center font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in filtered" :key="row.id">
                                    <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-green-50/40 dark:hover:bg-green-900/10 transition-colors">
                                        <td class="px-4 py-3 text-gray-400 dark:text-gray-500 font-mono text-xs" x-text="index + 1"></td>
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-gray-800 dark:text-white" x-text="row.applicant_name || 'Guest / Applicant'"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border" :class="getServiceClass(row.service_type)" x-text="row.service_type"></span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                            <div class="flex flex-col gap-0.5">
                                                <span x-text="row.scheduled_date ? new Date(row.scheduled_date.split('T')[0] + 'T00:00:00').toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' }) : '—'"></span>
                                                <span class="text-xs text-gray-400" x-text="row.scheduled_time ? (() => { try { return new Date('1970-01-01T' + row.scheduled_time).toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', hour12: true }); } catch(e) { return row.scheduled_time; } })() : ''"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border"
                                                :class="{
                                                    'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800/40': row.status === 'Pending',
                                                    'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-300 dark:border-indigo-800/40': row.status === 'For Priest Review',
                                                    'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-300 dark:border-orange-800/40': row.status === 'For Payment',
                                                    'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800/40': row.status === 'Approved',
                                                }"
                                                x-text="row.status">
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border"
                                                :class="{
                                                    'bg-gray-50 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600': row.payment_status === 'Pending',
                                                    'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800/40': row.payment_status === 'Paid',
                                                    'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-900/20 dark:text-sky-300 dark:border-sky-800/40': row.payment_status === 'Waived',
                                                }"
                                                x-text="row.payment_status">
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs" x-text="row.contact_number || '—'"></td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">

                                                <template x-if="row.payment_status === 'Paid'">
                                                    <div class="flex items-center gap-2">
                                                        <button disabled
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed"
                                                            title="Already Paid">
                                                            <i class="fas fa-check text-xs"></i>
                                                        </button>
                                                        
                                                        <template x-if="row.payment_id">
                                                            <a :href="'{{ url('payments') }}/' + row.payment_id + '/receipt'" target="_blank"
                                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all"
                                                                title="Print Receipt">
                                                                <i class="fas fa-print text-xs"></i>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>

                                                <template x-if="row.payment_status !== 'Paid' || !row.payment_id">
                                                    <button type="button" @click="window.openPaymentModal(row, getFee(row.service_type))"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-600 hover:bg-green-700 text-white shadow-sm transition-all"
                                                        title="Process Payment">
                                                        <i class="fas fa-money-bill-wave text-xs"></i>
                                                    </button>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                {{-- Empty state when filter returns nothing --}}
                                <tr x-show="filtered.length === 0">
                                    <td colspan="8" class="px-6 py-10 text-center">
                                        <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-gray-500">
                                            <i class="fas fa-search text-2xl opacity-40"></i>
                                            <p class="text-sm font-medium">No service fee entries match your filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer count --}}
                    <div class="px-6 py-3 bg-gray-50/60 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs text-gray-400 dark:text-gray-500">
                        <span x-text="'Showing ' + filtered.length + ' of ' + rows.length + ' record' + (rows.length !== 1 ? 's' : '')"></span>
                        <span>Sorted by recently updated ↓</span>
                    </div>
                </div>
            @endif


            @if(isset($data['request_activity_data']))
                <!-- Monthly Trends (Combo Chart) -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 relative"
                    x-data="{ showFilter: false, chartReady: false }" x-init="$nextTick(() => { chartReady = true })">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg text-gray-800 dark:text-white">
                            Request Activity
                            <span class="text-base font-normal text-gray-500 ml-2">({{ $data['group_by'] == 'day' ? 'Daily' : 'Monthly' }} Trend)</span>
                        </h3>

                        <!-- Filter Toggle Button -->
                        <button @click="showFilter = !showFilter"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors relative"
                            title="Filter Options">
                            <i class="fas fa-filter"></i>
                            @if(request('start_date') || request('end_date'))
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-blue-500 rounded-full"></span>
                            @endif
                        </button>

                        <!-- Collapsible Filter Panel -->
                        <div x-show="showFilter" @click.away="showFilter = false" style="display: none;"
                            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-16 z-20 w-72 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 p-4 mr-6">

                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Date Filter</h4>
                                <button @click="showFilter = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <form method="GET" action="{{ route('dashboard') }}" class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                                    <input type="date" name="start_date" value="{{ $data['start_date'] ?? '' }}"
                                        class="w-full text-sm px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                                    <input type="date" name="end_date" value="{{ $data['end_date'] ?? '' }}"
                                        class="w-full text-sm px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                                </div>

                                <div class="flex gap-2 pt-2">
                                    <button type="submit"
                                        class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                                        Apply
                                    </button>
                                    @if(request('start_date') || request('end_date'))
                                        <a href="{{ route('dashboard') }}"
                                            class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold rounded-lg transition-colors text-center">
                                            Clear
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Skeleton while chart JS loads --}}
                    <div x-show="!chartReady" class="relative h-80 w-full flex flex-col gap-3 justify-center px-2">
                        <div class="skeleton h-4 w-full rounded"></div>
                        <div class="skeleton h-4 w-5/6 rounded"></div>
                        <div class="skeleton h-4 w-4/6 rounded"></div>
                        <div class="skeleton h-4 w-full rounded"></div>
                        <div class="skeleton h-4 w-3/4 rounded"></div>
                        <div class="skeleton h-4 w-5/6 rounded"></div>
                    </div>
                    <div x-show="chartReady" x-cloak class="relative h-80 w-full">
                        <canvas id="activityTrendChart"></canvas>
                    </div>
                </div>
            @endif

            @if(isset($data['trend_collections']))
                <!-- Financial Activity Trends -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 relative"
                    x-data="{ showFilter: false, chartReady: false }" x-init="$nextTick(() => { chartReady = true })">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg text-gray-800 dark:text-white">
                            Transaction Frequency
                            <span class="text-base font-normal text-gray-500 ml-2">(Count of Collections, Donations, & Fees)</span>
                        </h3>
                        
                        <!-- Filter Toggle Button -->
                        <button @click="showFilter = !showFilter"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors relative"
                            title="Filter Options">
                            <i class="fas fa-filter"></i>
                            @if(request('fin_start_date') || request('fin_end_date'))
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-blue-500 rounded-full"></span>
                            @endif
                        </button>

                        <!-- Collapsible Filter Panel -->
                        <div x-show="showFilter" @click.away="showFilter = false" style="display: none;"
                            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-16 z-20 w-72 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 p-4 mr-6">

                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Date Filter</h4>
                                <button @click="showFilter = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <form method="GET" action="{{ route('dashboard') }}" class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                                    <input type="date" name="fin_start_date" value="{{ $data['fin_start_date'] ?? '' }}"
                                        class="w-full text-sm px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                                    <input type="date" name="fin_end_date" value="{{ $data['fin_end_date'] ?? '' }}"
                                        class="w-full text-sm px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                                </div>

                                <div class="flex gap-2 pt-2">
                                    <button type="submit"
                                        class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                                        Apply
                                    </button>
                                    @if(request('fin_start_date') || request('fin_end_date'))
                                        <a href="{{ route('dashboard') }}"
                                            class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold rounded-lg transition-colors text-center">
                                            Clear
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Skeleton while chart JS loads --}}
                    <div x-show="!chartReady" class="relative h-80 w-full flex flex-col gap-3 justify-center px-2">
                        <div class="skeleton h-4 w-full rounded"></div>
                        <div class="skeleton h-4 w-4/6 rounded"></div>
                        <div class="skeleton h-4 w-5/6 rounded"></div>
                        <div class="skeleton h-4 w-full rounded"></div>
                        <div class="skeleton h-4 w-3/4 rounded"></div>
                    </div>
                    <div x-show="chartReady" x-cloak class="relative h-80 w-full">
                        <canvas id="financialTrendChart"></canvas>
                    </div>
                </div>
            @endif





            </div>

            <!-- Quick Feed Area (Right Column - 1/3 Width, hidden for Priest) -->
            @if($hasRightColumn)
            <div class="lg:col-span-1 space-y-8">

                
                @if(isset($data['recent_activities']))
                    <!-- Recent Activity -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="font-bold text-lg text-gray-800 dark:text-white">Recent Logs</h3>
                            <a href="{{ route('audit-trail') }}"
                                class="text-sm font-bold text-primary hover:text-blue-700 uppercase tracking-wider">View All</a>
                        </div>
                        <div class="p-6 space-y-4">
                            @foreach($data['recent_activities']->take(5) as $activity)
                                <div class="flex items-start gap-4">
                                    <div
                                        class="mt-1 flex-shrink-0 w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-sm">
                                        <i class="fas fa-history text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800 dark:text-white text-[15px] uppercase tracking-wide">
                                            {{ $activity->action }}
                                        </h4>
                                        <p class="text-base text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2">
                                            {{ $activity->details }}
                                        </p>
                                        <p class="text-sm text-gray-400 font-medium mt-1">
                                            {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach

                            @if($data['recent_activities']->isEmpty())
                                <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-inbox text-2xl mb-2 opacity-50"></i>
                                    <p class="text-sm">No recent activity recorded.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            @endif
        </div>
        @if(isset($data['request_activity_data']))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Dark mode check for chart colors
                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#e5e7eb' : '#374151';
                    const gridColor = isDark ? '#374151' : '#e5e7eb';

                    @if(isset($data['request_activity_data']))
                            const trendCtx = document.getElementById('activityTrendChart').getContext('2d');
                            const trendData = @json($data['request_activity_data']);
                            const groupBy = "{{ $data['group_by'] }}";
                            const startDateStr = "{{ $data['start_date'] }}";
                            const endDateStr = "{{ $data['end_date'] }}";

                            // Dynamically extract service types that actually have data
                            const allServiceTypes = [...new Set(trendData.map(item => item.service_type))].sort();

                            // Curated color map for known IFI service types; fallback palette for unknowns
                            const knownColors = {
                                'Baptism':              'rgba(59,  130, 246, 0.85)',
                                'Wedding':              'rgba(236,  72, 153, 0.85)',
                                'Confirmation':         'rgba(16,  185, 129, 0.85)',
                                'Burial':               'rgba(249, 115,  22, 0.85)',
                                'Funeral Mass':         'rgba(239,  68,  68, 0.85)',
                                'First Holy Communion': 'rgba(234, 179,   8, 0.85)',
                                'Ordination':           'rgba(20,  184, 166, 0.85)',
                                'Anointing':            'rgba(139,  92, 246, 0.85)',
                            };
                            const fallbackPalette = [
                                'rgba(99, 102, 241, 0.85)',
                                'rgba(6, 182, 212, 0.85)',
                                'rgba(132, 204,  22, 0.85)',
                                'rgba(156, 163, 175, 0.85)',
                            ];
                            let fallbackIdx = 0;
                            function getColor(type) {
                                return knownColors[type] || fallbackPalette[fallbackIdx++ % fallbackPalette.length];
                            }

                            let labels = [];
                            const countsByType = {};
                            allServiceTypes.forEach(t => countsByType[t] = []);

                            if (groupBy === 'day') {
                                let current = new Date(startDateStr + 'T00:00:00');
                                const end = new Date(endDateStr + 'T00:00:00');
                                while (current <= end) {
                                    const dateStr = current.toISOString().split('T')[0];
                                    labels.push(current.toLocaleDateString('en-US', { month: 'long', day: 'numeric' }));
                                    allServiceTypes.forEach(type => {
                                        const match = trendData.find(item => item.date === dateStr && item.service_type === type);
                                        countsByType[type].push(match ? match.total : 0);
                                    });
                                    current.setDate(current.getDate() + 1);
                                }
                            } else {
                                const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                                let current = new Date(startDateStr + 'T00:00:00');
                                current.setDate(1);
                                const end = new Date(endDateStr + 'T00:00:00');
                                end.setMonth(end.getMonth() + 1);
                                end.setDate(0);
                                while (current <= end) {
                                    const m = current.getMonth(), y = current.getFullYear();
                                    labels.push(monthNames[m] + ' ' + y);
                                    allServiceTypes.forEach(type => {
                                        const match = trendData.find(item => item.month == (m+1) && item.year == y && item.service_type === type);
                                        countsByType[type].push(match ? match.total : 0);
                                    });
                                    current.setMonth(current.getMonth() + 1);
                                }
                            }

                            // Only include types that have at least 1 non-zero entry (keeps chart clean)
                            const activeTypes = allServiceTypes.filter(t => countsByType[t].some(v => v > 0));

                            const datasets = activeTypes.map(type => {
                                const color = getColor(type);
                                return {
                                    label: type,
                                    data: countsByType[type],
                                    backgroundColor: color,
                                    borderColor: color.replace('0.85', '1'),
                                    borderWidth: 1,
                                    borderRadius: 5,
                                    borderSkipped: false,
                                    maxBarThickness: 32,
                                };
                            });

                            window.activityTrendChart = new Chart(trendCtx, {
                                type: 'bar',
                                data: { labels, datasets },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    animation: false,
                                    interaction: { mode: 'index', intersect: false },
                                    plugins: {
                                        legend: {
                                            display: true,
                                            position: 'top',
                                            labels: {
                                                color: textColor,
                                                boxWidth: 12,
                                                boxHeight: 12,
                                                borderRadius: 3,
                                                useBorderRadius: true,
                                                padding: 12,
                                                font: { family: "'Poppins', sans-serif", size: 13 }
                                            }
                                        },
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                            backgroundColor: 'rgba(17, 24, 39, 0.92)',
                                            titleColor: '#f9fafb',
                                            bodyColor: '#d1d5db',
                                            titleFont: { family: "'Poppins', sans-serif", size: 14, weight: 'bold' },
                                            bodyFont: { family: "'Poppins', sans-serif", size: 14 },
                                            padding: 12,
                                            cornerRadius: 8,
                                            displayColors: true,
                                            callbacks: {
                                                // Hide datasets with 0 value from tooltip to reduce noise
                                                label: ctx => ctx.parsed.y > 0 ? ` ${ctx.dataset.label}: ${ctx.parsed.y}` : null
                                            }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            title: { display: true, text: 'No. of Requests', color: textColor, font: { size: 13, weight: 'bold' } },
                                            grid: { color: gridColor, lineWidth: 1 },
                                            border: { dash: [4, 4] },
                                            ticks: { color: textColor, precision: 0, font: { family: "'Poppins', sans-serif", size: 13 } }
                                        },
                                        x: {
                                            grid: { display: false },
                                            ticks: {
                                                color: textColor,
                                                font: { family: "'Poppins', sans-serif", size: 12 },
                                                maxRotation: 45,
                                                autoSkip: true,
                                                maxTicksLimit: 14
                                            }
                                        }
                                    }
                                }
                            });

                        @endif
                });
            </script>
        @else
            <script>console.log('Charts not loaded: data missing');</script>
        @endif

        <!-- Financial Activity Chart Script -->
        @if(isset($data['trend_collections']))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Dark mode check for chart colors
                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#e5e7eb' : '#374151';
                    const gridColor = isDark ? '#374151' : '#e5e7eb';

                    const ctx = document.getElementById('financialTrendChart').getContext('2d');
                    const groupBy = "{{ $data['fin_group_by'] }}";
                    const startDateStr = "{{ $data['fin_start_date'] }}";
                    const endDateStr = "{{ $data['fin_end_date'] }}";

                    // Raw Data
                    const collections = @json($data['trend_collections']);
                    const donations = @json($data['trend_donations']);
                    const fees = @json($data['trend_fees']);

                    // Prepare Arrays
                    let labels = [];
                    let dataColl = [];
                    let dataDon = [];
                    let dataFees = [];

                    if (groupBy === 'day') {
                        // Daily grouping
                        let current = new Date(startDateStr);
                        const end = new Date(endDateStr);

                        while (current <= end) {
                            const dateStr = current.toISOString().split('T')[0];
                            const labelStr = current.toLocaleDateString('en-US', { month: 'long', day: 'numeric' });
                            labels.push(labelStr);

                            // Collections
                            const matchColl = collections.find(item => item.date === dateStr);
                            dataColl.push(matchColl ? matchColl.total : 0);

                            // Donations
                            const matchDon = donations.find(item => item.date === dateStr);
                            dataDon.push(matchDon ? matchDon.total : 0);

                            // Fees
                            const matchFees = fees.find(item => item.date === dateStr);
                            dataFees.push(matchFees ? matchFees.total : 0);

                            current.setDate(current.getDate() + 1);
                        }
                    } else {
                        // Monthly grouping
                        let current = new Date(startDateStr);
                        current.setDate(1);
                        const end = new Date(endDateStr);
                        end.setDate(1); // Compare first day of months

                        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                        while (current <= end) {
                            const m = current.getMonth();
                            const y = current.getFullYear();
                            const label = monthNames[m] + ' ' + y;
                            labels.push(label);

                            // Collections
                            const matchColl = collections.find(item => item.month == (m + 1) && item.year == y);
                            dataColl.push(matchColl ? matchColl.total : 0);

                            // Donations
                            const matchDon = donations.find(item => item.month == (m + 1) && item.year == y);
                            dataDon.push(matchDon ? matchDon.total : 0);

                            // Fees
                            const matchFees = fees.find(item => item.month == (m + 1) && item.year == y);
                            dataFees.push(matchFees ? matchFees.total : 0);

                            current.setMonth(current.getMonth() + 1);
                        }
                    }

                    window.financialTrendChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Collections',
                                    data: dataColl,
                                    backgroundColor: '#10B981', // Green
                                    borderRadius: 5,
                                    maxBarThickness: 32
                                },
                                {
                                    label: 'Donations',
                                    data: dataDon,
                                    backgroundColor: '#3B82F6', // Blue
                                    borderRadius: 5,
                                    maxBarThickness: 32
                                },
                                {
                                    label: 'Service Fees',
                                    data: dataFees,
                                    backgroundColor: '#F59E0B', // Orange
                                    borderRadius: 5,
                                    maxBarThickness: 32
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: false,
                            plugins: {
                                legend: { 
                                    position: 'top',
                                    labels: {
                                        color: textColor,
                                        font: { family: "'Poppins', sans-serif", size: 13 }
                                    }
                                },
                                tooltip: { 
                                    mode: 'index', 
                                    intersect: false,
                                    titleFont: { family: "'Poppins', sans-serif", size: 14, weight: 'bold' },
                                    bodyFont: { family: "'Poppins', sans-serif", size: 14 }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: { 
                                        display: true, 
                                        text: 'Amount (₱)', 
                                        color: textColor, 
                                        font: { size: 13, weight: 'bold' } 
                                    },
                                    ticks: { 
                                        precision: 0,
                                        color: textColor,
                                        font: { family: "'Poppins', sans-serif", size: 13 }
                                    },
                                    grid: { display: true, borderDash: [2, 4], color: gridColor }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        color: textColor,
                                        font: { family: "'Poppins', sans-serif", size: 12 }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        @endif


        <script>
            // Listen for theme changes from Alpine.js toggle
            window.addEventListener('theme-toggled', function () {
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#e5e7eb' : '#374151';
                const gridColor = isDark ? '#374151' : '#e5e7eb';



                if (window.activityTrendChart) {
                    window.activityTrendChart.options.plugins.legend.labels.color = textColor;
                    window.activityTrendChart.options.scales.x.ticks.color = textColor;
                    if (window.activityTrendChart.options.scales.y.title) {
                        window.activityTrendChart.options.scales.y.title.color = textColor;
                    }
                    window.activityTrendChart.options.scales.y.ticks.color = textColor;
                    window.activityTrendChart.options.scales.y.grid.color = gridColor;
                    window.activityTrendChart.update();
                }

                if (window.financialTrendChart) {
                    window.financialTrendChart.options.plugins.legend.labels.color = textColor;
                    window.financialTrendChart.options.scales.x.ticks.color = textColor;
                    window.financialTrendChart.options.scales.y.ticks.color = textColor;
                    window.financialTrendChart.options.scales.y.grid.color = gridColor;
                    window.financialTrendChart.update();
                }


            });
        </script>

    @include('partials.details_modal')

    @if(isset($data['upcoming_schedules']) && count($data['upcoming_schedules']) > 0)
        <!-- Universal Floating Schedules UI (Unified Dashboard Experience) -->
        <div x-data="{ open: false }" class="fixed bottom-6 right-6 z-[60]">
            <!-- Floating Toggle Button -->
            <button @click="open = !open" 
                    title="View Upcoming Schedules"
                    class="w-14 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-2xl flex items-center justify-center transition-all transform hover:scale-110 active:scale-95 group relative border-4 border-white dark:border-gray-900">
                <i class="far fa-calendar-alt text-xl transition-all" :class="open ? 'opacity-0 scale-50' : 'opacity-100 scale-100'"></i>
                <i class="fas fa-times text-xl absolute transition-all" :class="open ? 'opacity-100 scale-100' : 'opacity-0 scale-50'"></i>
                
                <!-- Notification Badge -->
                <span class="absolute -top-1 -right-1 flex h-5 w-5" x-show="!open">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-5 w-5 bg-red-500 text-white text-[9px] font-bold items-center justify-center border-2 border-white dark:border-gray-900">
                        {{ count($data['upcoming_schedules']) }}
                    </span>
                </span>
            </button>

            <!-- Floating Panel -->
            <div x-show="open" 
                 style="display: none;"
                 class="absolute bottom-20 right-0 w-96 sm:w-[420px] bg-white dark:bg-gray-800 rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-gray-100 dark:border-gray-700 overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-10 scale-95">
                 
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="far fa-calendar-check text-indigo-500"></i>
                        Next Schedules
                    </h3>
                    <a href="{{ route('schedules') }}" class="text-xs font-bold text-primary hover:text-blue-700 uppercase tracking-wider">Full Calendar</a>
                </div>

                <div class="max-h-[60vh] overflow-y-auto p-4 space-y-3 custom-scrollbar">
                    @foreach($data['upcoming_schedules'] as $schedule)
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all cursor-pointer group shadow-sm"
                             onclick="window.location.href='{{ route('schedules') }}'">
                             <div class="flex items-start justify-between mb-3">
                                  <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold uppercase tracking-wide border {{ ServiceHelper::getServiceBadgeClass($schedule->service_type) }}">
                                    {{ $schedule->service_type }}
                                  </span>
                                  <span class="text-xs font-semibold text-gray-400">
                                    {{ \Carbon\Carbon::parse($schedule->start_datetime)->diffForHumans() }}
                                  </span>
                             </div>
                             <h4 class="font-bold text-gray-900 dark:text-white text-base mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                 {{ $schedule->title }}
                             </h4>
                             <div class="flex items-center gap-5 text-xs text-gray-500 dark:text-gray-400">
                                 <span class="flex items-center gap-1.5"><i class="far fa-calendar text-indigo-400"></i> {{ \Carbon\Carbon::parse($schedule->start_datetime)->format('M d, Y') }}</span>
                                 <span class="flex items-center gap-1.5"><i class="far fa-clock text-amber-500"></i> {{ \Carbon\Carbon::parse($schedule->start_datetime)->format('h:i A') }}</span>
                             </div>
                        </div>
                    @endforeach
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-800/80 text-center border-t border-gray-100 dark:border-gray-700">
                    <button @click="open = false" class="text-xs font-bold text-gray-400 hover:text-gray-600 uppercase tracking-widest transition-colors">Close Panel</button>
                </div>
            </div>
        </div>
    @endif

    @include('partials.payment_modal')
@endsection