@extends('layouts.app')
@use('App\Helpers\ServiceHelper')

@section('title', 'Sacraments')
@section('page_title', 'Services History')
@section('page_subtitle', 'Official records of church services')
@section('role_label', 'Admin')



@section('content')
    <style>
        /* Allow main to scroll naturally */
        main {
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        #sacModal { display: none; }
        #sacModal.is-open { display: flex; }
    </style>
    <script>
        function sacFormatDate(d) {
            if (!d) return 'N/A';
            return new Date(d).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        }
        function sacFormatTime(t) {
            if (!t || t === '00:00:00') return 'Not specified';
            try {
                var time = t.includes('T') ? new Date(t) : new Date('1970-01-01T' + t);
                return time.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            } catch(e) { return t; }
        }
        function openSacModal(btn) {
            var row;
            try { row = JSON.parse(btn.getAttribute('data-row')); }
            catch(e) { alert('Error reading record data: ' + e.message); return; }

            var m = document.getElementById('sacModal');

            // Header
            m.querySelector('#sm-service-type').textContent = (row.service_type || '') + ' — Service Details';

            // Status badge
            var sb = m.querySelector('#sm-status');
            sb.textContent = row.status === 'Approved' ? 'Pending Completion' : (row.status || '');
            sb.className = 'inline-flex items-center gap-1 text-sm px-2 py-0.5 rounded-full font-bold ' +
                (row.status === 'Completed' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300');

            // Payment badge
            var pb = m.querySelector('#sm-payment');
            if (pb) {
                pb.textContent = row.payment_status || '';
                pb.className = 'inline-flex items-center gap-1 text-sm px-2 py-0.5 rounded-full font-bold ' +
                    (row.payment_status === 'Paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300');
            }

            // Schedule
            m.querySelector('#sm-date').textContent = sacFormatDate(row.scheduled_date);
            m.querySelector('#sm-time').textContent = sacFormatTime(row.scheduled_time);

            // Applicant
            var fullName = (row.first_name || '').trim();
            if (row.middle_name && row.middle_name.toLowerCase().trim() !== 'n/a') fullName += ' ' + row.middle_name.trim();
            fullName += ' ' + (row.last_name || '').trim();
            if (row.suffix && row.suffix.toLowerCase().trim() !== 'n/a') fullName += ' ' + row.suffix.trim();
            m.querySelector('#sm-fullname').textContent = fullName.trim();
            m.querySelector('#sm-priest').textContent = (row.priest && row.priest.name) ? row.priest.name : 'Not assigned';
            m.querySelector('#sm-contact').textContent = row.contact_number || '—';

            var fatherRow = m.querySelector('#sm-father-row');
            if (fatherRow) { fatherRow.style.display = row.fathers_name ? '' : 'none'; m.querySelector('#sm-father').textContent = row.fathers_name || ''; }
            var motherRow = m.querySelector('#sm-mother-row');
            if (motherRow) { motherRow.style.display = row.mothers_name ? '' : 'none'; m.querySelector('#sm-mother').textContent = row.mothers_name || ''; }
            var emailRow = m.querySelector('#sm-email-row');
            if (emailRow) { emailRow.style.display = row.email ? '' : 'none'; m.querySelector('#sm-email').textContent = row.email || ''; }

            // Requirements
            var reqs = row.requirements || [];
            if (typeof reqs === 'string') { try { reqs = JSON.parse(reqs); } catch(e) { reqs = []; } }
            var reqList = m.querySelector('#sm-req-list');
            var reqEmpty = m.querySelector('#sm-req-empty');
            if (reqs.length > 0) {
                reqList.style.display = '';
                reqEmpty.style.display = 'none';
                reqList.innerHTML = reqs.map(function(r) {
                    return '<li class="flex items-center gap-2.5"><span class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 flex items-center justify-center flex-none"><i class="fas fa-check text-[9px]"></i></span><span class="text-sm text-gray-700 dark:text-gray-300 font-medium">' + r + '</span></li>';
                }).join('');
            } else {
                reqList.style.display = 'none';
                reqEmpty.style.display = '';
            }

            // Notes
            m.querySelector('#sm-notes').textContent = row.details || 'No additional details provided.';

            // Footer action buttons
            var completeForm = m.querySelector('#sm-complete-form');
            var printBtn = m.querySelector('#sm-print-btn');
            if (completeForm) completeForm.style.display = row.status === 'Approved' ? '' : 'none';
            if (completeForm) completeForm.action = '{{ url("sacraments") }}/' + row.id + '/complete';
            if (printBtn) {
                printBtn.style.display = row.status === 'Completed' ? '' : 'none';
                printBtn.href = '{{ url("sacraments") }}/' + row.id + '/certificate';
            }

            m.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
        function closeSacModal() {
            document.getElementById('sacModal').classList.remove('is-open');
            document.body.style.overflow = '';
        }
    </script>
    <div class="h-full flex flex-col">



        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-100 dark:border-green-800 flex items-center gap-3"
                x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                <div
                    class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-800 text-green-600 dark:text-green-400 flex items-center justify-center shrink-0">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <h4 class="font-bold text-green-800 dark:text-green-200 text-sm uppercase">Success</h4>
                    <p class="text-sm text-green-600 dark:text-green-300">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <div x-data="tableSearch()">
            <!-- Filters -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-t-3xl border border-gray-100 dark:border-gray-800 border-b-0 shadow-sm relative z-20">
                <form action="{{ route('sacraments') }}" method="GET" class="flex flex-wrap items-center gap-4 search-form" @submit.prevent="submitSearch">


                    <!-- Search -->
                    <div class="relative max-w-xs w-full">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search applicant..."
                            @input.debounce.50ms="submitSearch"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium">
                    </div>

                    <!-- Service Type Filter -->
                    <div class="relative w-full lg:w-fit">
                        <select name="service_type" @change="submitSearch"
                            class="dropdown-btn w-full lg:w-auto">
                            <option value="">All Service Types</option>
                            @foreach($serviceTypes as $type)
                                <option value="{{ $type }}" {{ request('service_type') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="relative w-full lg:w-fit">
                        <select name="status" @change="submitSearch"
                            class="dropdown-btn w-full lg:w-auto">
                            <option value="">All Status</option>
                            <option value="Pending Completion" {{ request('status') == 'Pending Completion' ? 'selected' : '' }}>Pending Completion</option>
                            <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-center gap-2">
                        @if(request()->anyFilled(['service_type', 'status']))
                            <button type="button" @click="clearFilters()"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-bold transition-all flex items-center gap-1 px-2">
                                <i class="fas fa-times-circle"></i>Clear
                            </button>
                        @endif
                    </div>
                </form>
            </div> <!-- End of Filters div -->

            <div class="bg-white dark:bg-gray-800 rounded-b-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden search-results-container relative flex flex-col mb-2" @click="handlePagination">
                <div class="w-full overflow-x-auto overflow-y-auto max-h-[calc(100vh-280px)] custom-scrollbar">
                <table class="w-full text-left border-collapse relative">
                    <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800">
                        <tr
                            class="text-sm font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700 tracking-wider">
                            <th class="px-6 py-4">Date & Time</th>
                            <th class="px-6 py-4">Applicant</th>
                            <th class="px-6 py-4">Service Type</th>
                            @if(Auth::user()->role !== 'Priest')
                                <th class="px-6 py-4">Payment</th>
                            @endif
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($sacraments as $row)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors group">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                                                            {{ $row->scheduled_date->format('d') }}
                                                        </div>
                                                        <div>
                                                            <p class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                                                                {{ $row->scheduled_date->format('F Y') }}
                                                            </p>
                                                            <p class="text-sm text-gray-400">{{ $row->scheduled_date->format('l') }}</p>
                                                            <p class="text-xs font-bold text-blue-600 dark:text-blue-400 mt-1 flex items-center gap-1">
                                                                <i class="fas fa-clock text-[10px]"></i>
                                                                {{ $row->scheduled_time ? \Carbon\Carbon::parse($row->scheduled_time)->format('h:i A') : 'N/A' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200">
                                                    {{ $row->first_name }} 
                                                    {{ ($row->middle_name && strtolower(trim($row->middle_name)) !== 'n/a') ? $row->middle_name . ' ' : '' }}
                                                    {{ $row->last_name }}
                                                    {{ ($row->suffix && strtolower(trim($row->suffix)) !== 'n/a') ? ' ' . $row->suffix : '' }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span 
                                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border {{ \App\Helpers\ServiceHelper::getServiceBadgeClass($row->service_type) }}">
                                                        {{ $row->service_type }}
                                                    </span>
                                                </td>
                                                @if(Auth::user()->role !== 'Priest')
                                                <td class="px-6 py-4">
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-bold 
                                                                                    {{ $row->payment_status === 'Paid' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800' }}">
                                                        <i
                                                            class="fas {{ $row->payment_status === 'Paid' ? 'fa-check-circle' : 'fa-clock' }} text-xs"></i>
                                                        {{ $row->payment_status }}
                                                    </span>
                                                </td>
                                                @endif
                                                <td class="px-6 py-4">
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-bold 
                                                                                    {{ $row->status === 'Completed' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800' :
                                ($row->status === 'Processing' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800' :
                                    'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600') }}">
                                                        <div
                                                            class="w-1.5 h-1.5 rounded-full {{ $row->status === 'Completed' ? 'bg-blue-500' : ($row->status === 'Processing' ? 'bg-purple-500' : 'bg-gray-500') }}">
                                                        </div>
                                                        {{ $row->status === 'Approved' ? 'Pending' : $row->status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <div class="flex items-center justify-center gap-2">
                                                        <button type="button" data-row="{{ $row->toJson() }}" 
                                                            onclick="openSacModal(this)"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-all shadow-sm"
                                                            title="View Details">
                                                            <i class="fas fa-eye text-xs"></i>
                                                        </button>

                                                        <!-- Mark Complete Button -->
                                                        @if($row->status === 'Approved')
                                                            <form action="{{ route('sacraments.complete', $row->id) }}" method="POST"
                                                                class="inline-block"
                                                                onsubmit="event.preventDefault(); showConfirm('Mark as Complete', 'Are you sure this service has been performed?', 'bg-green-600 hover:bg-green-700', () => this.submit(), 'Yes, Complete')">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-600 hover:bg-green-700 text-white shadow-sm transition-all"
                                                                    title="Mark as Complete">
                                                                    <i class="fas fa-check text-xs"></i>
                                                                </button>
                                                            </form>
                                                        @elseif($row->status === 'Completed')
                                                            <!-- Print Certificate Button -->
                                                            <a href="{{ route('sacraments.certificate', $row->id) }}" target="_blank"
                                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all"
                                                                title="Print Certificate">
                                                                <i class="fas fa-print text-xs"></i>
                                                            </a>
                                                        @else
                                                            <!-- Disabled Certificate Button -->
                                                            <button disabled
                                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 shadow-sm cursor-not-allowed opacity-50"
                                                                title="Certificate available after completion">
                                                                <i class="fas fa-print text-xs"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                        @empty
                            <tr>
                                 <td colspan="{{ Auth::user()->role === 'Priest' ? '5' : '6' }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-file-alt text-4xl mb-4 text-gray-300 dark:text-gray-600"></i>
                                        <p class="text-lg font-medium">No active service records found</p>
                                        <p class="text-sm mt-1">Approved service requests will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                {{ $sacraments->links() }}
            </div>
        </div>
    </div>

        {{-- View Details Modal (Pure JS — no Alpine dependency) --}}
        <div id="sacModal" class="fixed inset-0 z-[9999] items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeSacModal()"></div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl shadow-2xl flex flex-col relative z-20" style="max-height:90vh">

                {{-- Header --}}
                <div class="shrink-0 p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-white dark:bg-gray-800 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                            <i class="fas fa-bible text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-xl leading-tight" id="sm-service-type">Service Details</h3>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                <span id="sm-status" class="inline-flex items-center gap-1 text-sm px-2 py-0.5 rounded-full font-bold bg-amber-100 text-amber-700"></span>
                                @if(Auth::user()->role !== 'Priest')
                                <span id="sm-payment" class="inline-flex items-center gap-1 text-sm px-2 py-0.5 rounded-full font-bold bg-yellow-100 text-yellow-700"></span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <button onclick="closeSacModal()" class="text-gray-400 hover:text-gray-600 transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Scrollable Body --}}
                <div class="overflow-y-auto p-5 space-y-4">

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3.5">
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1" style="font-weight: 700 !important;"><i class="fas fa-calendar mr-1"></i> Scheduled Date</p>
                            <p class="text-gray-800 dark:text-gray-200 text-sm" id="sm-date" style="font-weight: 400 !important;"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3.5">
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1" style="font-weight: 700 !important;"><i class="fas fa-clock mr-1"></i> Scheduled Time</p>
                            <p class="text-gray-800 dark:text-gray-200 text-sm" id="sm-time" style="font-weight: 400 !important;"></p>
                        </div>
                    </div>

                    <div class="border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-700/30 px-4 py-2.5 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"><i class="fas fa-user mr-1.5"></i> Applicant Information</p>
                        </div>
                        <div class="p-4 grid grid-cols-2 gap-x-6 gap-y-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-0.5" style="font-weight: 700 !important;">Full Name</p>
                                <p class="text-gray-800 dark:text-gray-200 text-sm" id="sm-fullname" style="font-weight: 400 !important;"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-0.5" style="font-weight: 700 !important;">Officiant / Priest</p>
                                <p class="text-gray-800 dark:text-gray-200 text-sm" id="sm-priest" style="font-weight: 400 !important;"></p>
                            </div>
                            <div id="sm-father-row">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-0.5" style="font-weight: 700 !important;">Father's Name</p>
                                <p class="text-gray-800 dark:text-gray-200 text-sm" id="sm-father" style="font-weight: 400 !important;"></p>
                            </div>
                            <div id="sm-mother-row">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-0.5" style="font-weight: 700 !important;">Mother's Name</p>
                                <p class="text-gray-800 dark:text-gray-200 text-sm" id="sm-mother" style="font-weight: 400 !important;"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-0.5" style="font-weight: 700 !important;">Contact Number</p>
                                <p class="text-gray-800 dark:text-gray-200 text-sm" id="sm-contact" style="font-weight: 400 !important;"></p>
                            </div>
                            <div id="sm-email-row">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-0.5" style="font-weight: 700 !important;">Email Address</p>
                                <p class="text-gray-800 dark:text-gray-200 text-sm" id="sm-email" style="font-weight: 400 !important;"></p>
                            </div>
                        </div>
                    </div>

                    <div class="border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-700/30 px-4 py-2.5 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"><i class="fas fa-clipboard-check mr-1.5"></i> Requirements Submitted</p>
                        </div>
                        <div class="p-4">
                            <ul class="space-y-2" id="sm-req-list"></ul>
                            <div id="sm-req-empty" class="flex items-center gap-2 text-gray-400">
                                <i class="fas fa-info-circle text-blue-500/50"></i>
                                <span class="text-sm italic">No uploaded documents on file.</span>
                            </div>
                        </div>
                    </div>

                    <div class="border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-700/30 px-4 py-2.5 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"><i class="fas fa-sticky-note mr-1.5"></i> Additional Notes</p>
                        </div>
                        <div class="p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed" id="sm-notes"></p>
                        </div>
                    </div>

                </div>{{-- end scrollable body --}}

                {{-- Footer --}}
                <div class="shrink-0 p-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3 bg-white dark:bg-gray-800 rounded-b-2xl">
                    <button onclick="closeSacModal()" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                        Close
                    </button>
                    <form id="sm-complete-form" method="POST" style="display:none">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-green-500/30 transition-all">
                            <i class="fas fa-check"></i> Mark as Complete
                        </button>
                    </form>
                    <a id="sm-print-btn" href="#" target="_blank" style="display:none"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-purple-500/30 transition-all">
                        <i class="fas fa-print"></i> Print Certificate
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection