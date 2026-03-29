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
    </style>
    <div class="h-full flex flex-col" x-data="{ 
                                                isViewModalOpen: false, 
                                            viewData: {},

                                            openViewModal(row) {
                                                this.viewData = row;
                                                this.isViewModalOpen = true;
                                            },

                                            formatDate(dateString) {
                                                if (!dateString) return 'N/A';
                                                const date = new Date(dateString);
                                                return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                                            },

                                            formatTime(timeString) {
                                                if (!timeString || timeString === '00:00:00') return 'Not specified';
                                                try {
                                                    // Handle "HH:mm:ss" or ISO strings
                                                    const time = timeString.includes('T') ? new Date(timeString) : new Date(`1970-01-01T${timeString}`);
                                                    return time.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                                                } catch (e) {
                                                    return timeString;
                                                }
                                            }
                                        }">



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

        <div
            class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden search-results-container relative flex flex-col mb-2" x-data="tableSearch()" @click="handlePagination">
            <!-- Removed blur loading state for instant feel -->
            <!-- Filters -->
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <form action="{{ route('sacraments') }}" method="GET" class="flex flex-wrap items-center gap-4 search-form" @submit.prevent="submitSearch">
                    <div class="flex items-center gap-2 mr-2">
                        <label for="per_page_sacrament" class="text-xs font-bold text-gray-400 uppercase tracking-wider">Show</label>
                        <select name="per_page" id="per_page_sacrament" @change="submitSearch"
                            class="dropdown-btn w-20 px-3 py-1.5 h-10">
                            @foreach([5, 10, 15, 20, 50] as $n)
                                <option value="{{ $n }}" {{ request('per_page', 15) == $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="relative max-w-xs w-full">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search applicant..."
                            @input.debounce.300ms="submitSearch"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm">
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
                        @if(request()->anyFilled(['search', 'service_type', 'status']))
                            <a href="{{ route('sacraments') }}" 
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-medium transition-all">
                                <i class="fas fa-times-circle mr-1"></i>Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

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
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200">
                                                    {{ $row->first_name }} {{ $row->last_name }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1 rounded-full text-sm font-bold uppercase tracking-wider {{ ServiceHelper::getServiceBadgeClass($row->service_type) }}">
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
                                                        <!-- View Button -->
                                                        <button @click="openViewModal({{ $row }})"
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
                                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-green-600 hover:border-green-300 hover:bg-green-50 dark:hover:bg-green-900/30 transition-all shadow-sm"
                                                                    title="Mark as Complete">
                                                                    <i class="fas fa-check text-xs"></i>
                                                                </button>
                                                            </form>
                                                        @elseif($row->status === 'Completed')
                                                            <!-- Print Certificate Button -->
                                                            <a href="{{ route('sacraments.certificate', $row->id) }}" target="_blank"
                                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-purple-600 hover:border-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition-all shadow-sm"
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

        {{-- View Details Modal --}}
        <template x-teleport="body">
            <div x-show="isViewModalOpen" style="display: none;"
                class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl shadow-2xl flex flex-col"
                    style="max-height:90vh" @click.away="isViewModalOpen = false">

                    {{-- ── Sticky Header ── --}}
                    <div class="shrink-0 p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-white dark:bg-gray-800 rounded-t-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                                <i class="fas fa-bible text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white text-xl leading-tight" x-text="viewData.service_type + ' — Service Details'"></h3>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <span class="inline-flex items-center gap-1 text-sm px-2 py-0.5 rounded-full font-bold"
                                        :class="viewData.status === 'Completed'
                                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                            : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'">
                                        <span class="w-1.5 h-1.5 rounded-full inline-block"
                                            :class="viewData.status === 'Completed' ? 'bg-blue-500' : 'bg-amber-500'"></span>
                                        <span x-text="viewData.status === 'Approved' ? 'Pending Completion' : viewData.status"></span>
                                    </span>
                                     @if(Auth::user()->role !== 'Priest')
                                    <span class="inline-flex items-center gap-1 text-sm px-2 py-0.5 rounded-full font-bold"
                                        :class="viewData.payment_status === 'Paid'
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300'">
                                        <i class="fas fa-circle-check text-[9px]"></i>
                                        <span x-text="viewData.payment_status"></span>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button @click="isViewModalOpen = false"
                            class="text-gray-400 hover:text-gray-600 transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    {{-- ── Scrollable Body ── --}}
                    <div class="overflow-y-auto p-5 space-y-4">

                        {{-- Schedule Info --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3.5">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                                    <i class="fas fa-calendar mr-1"></i> Scheduled Date
                                </p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm" x-text="formatDate(viewData.scheduled_date)"></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3.5">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                                    <i class="fas fa-clock mr-1"></i> Scheduled Time
                                </p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm"
                                    x-text="formatTime(viewData.scheduled_time)"></p>
                            </div>
                        </div>

                        {{-- Applicant Information --}}
                        <div class="border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-700/30 px-4 py-2.5 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <i class="fas fa-user mr-1.5"></i> Applicant Information
                                </p>
                            </div>
                            <div class="p-4 grid grid-cols-2 gap-x-6 gap-y-3">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase font-bold mb-0.5">Full Name</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm"
                                        x-text="(viewData.first_name || '') + ' ' + (viewData.middle_name ? viewData.middle_name + ' ' : '') + (viewData.last_name || '')"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase font-bold mb-0.5">Officiant / Priest</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm"
                                        x-text="(viewData.priest && viewData.priest.name) ? viewData.priest.name : 'Not assigned'"></p>
                                </div>
                                <div x-show="viewData.fathers_name">
                                    <p class="text-xs text-gray-400 uppercase font-bold mb-0.5">Father's Name</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm" x-text="viewData.fathers_name || '—'"></p>
                                </div>
                                <div x-show="viewData.mothers_name">
                                    <p class="text-xs text-gray-400 uppercase font-bold mb-0.5">Mother's Name</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm" x-text="viewData.mothers_name || '—'"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 font-bold mb-0.5">Contact Number</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm" x-text="viewData.contact_number || '—'"></p>
                                </div>
                                <div x-show="viewData.email">
                                    <p class="text-xs text-gray-400 uppercase font-bold mb-0.5">Email Address</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm" x-text="viewData.email || '—'"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Requirements Submitted --}}
                        <div class="border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-700/30 px-4 py-2.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <i class="fas fa-clipboard-check mr-1.5"></i> Requirements Submitted
                                </p>
                                <span class="text-xs px-2 py-0.5 rounded-full font-bold"
                                    :class="(viewData.requirements && viewData.requirements.length > 0)
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                        : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'"
                                    x-text="(viewData.requirements ? viewData.requirements.length : 0) + ' document(s)'"></span>
                            </div>
                             <div class="p-4">
                                <template x-if="viewData.requirements && viewData.requirements.length > 0">
                                    <ul class="space-y-2">
                                        <template x-for="(req, i) in viewData.requirements" :key="i">
                                            <li class="flex items-center gap-2.5">
                                                <span class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center flex-none">
                                                    <i class="fas fa-check text-[9px]"></i>
                                                </span>
                                                <span class="text-sm text-gray-700 dark:text-gray-300 font-medium" x-text="req"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                                <template x-if="!viewData.requirements || viewData.requirements.length === 0">
                                    <div class="flex flex-col gap-3">
                                        <div class="flex items-center gap-2 text-gray-400">
                                            <i class="fas fa-info-circle text-blue-500/50"></i>
                                            <span class="text-sm italic">No uploaded documents on file. Standard requirements for this service:</span>
                                        </div>
                                        <ul class="space-y-2 pl-1">
                                            <template x-if="viewData.service_type === 'Baptism'">
                                                <li class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-circle text-[4px]"></i> PSA Birth Certificate</li>
                                            </template>
                                            <template x-if="viewData.service_type === 'Wedding'">
                                                <li class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-circle text-[4px]"></i> CENOMAR / Marriage License</li>
                                            </template>
                                            <template x-if="viewData.service_type === 'Funeral Mass' || viewData.service_type === 'Wake'">
                                                <li class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-circle text-[4px]"></i> Death Certificate</li>
                                            </template>
                                            <li class="text-[10px] text-gray-400 mt-1 mt-2">Please coordinate with the parish office to complete documentation.</li>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Additional Details / Notes --}}
                        <div class="border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-700/30 px-4 py-2.5 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    <i class="fas fa-sticky-note mr-1.5"></i> Additional Notes
                                </p>
                            </div>
                            <div class="p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed"
                                    x-text="viewData.details || 'No additional details provided.'"></p>
                            </div>
                        </div>

                    </div>{{-- end scrollable body --}}

                    {{-- ── Sticky Footer ── --}}
                    <div class="shrink-0 p-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3 bg-white dark:bg-gray-800 rounded-b-2xl">
                        <button @click="isViewModalOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                            Close
                        </button>
                        <template x-if="viewData.status === 'Approved'">
                            <form :action="'{{ url('sacraments') }}/' + viewData.id + '/complete'" method="POST">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-green-500/30 transition-all">
                                    <i class="fas fa-check"></i> Mark as Complete
                                </button>
                            </form>
                        </template>
                        <template x-if="viewData.status === 'Completed'">
                            <a :href="'{{ url('sacraments') }}/' + viewData.id + '/certificate'"
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-purple-500/30 transition-all">
                                <i class="fas fa-print"></i> Print Certificate
                            </a>
                        </template>
                    </div>

                </div>
            </div>
        </template>
    </div>
@endsection