@extends('layouts.app')
@use('App\Helpers\ServiceHelper')

@section('title', 'Services Request')
@section('page_title', 'Services Request')
@section('page_subtitle', 'Manage service applications from parishioners')

@section('content')
@php
    $defaultServiceRequestsTab = Auth::user()->hasModule('service_requests_form') ? 'form' : (Auth::user()->hasModule('service_requests_records') ? 'records' : '');
@endphp
    <div x-data="{ 
                        ...requestManager(),
                        activeTab: new URLSearchParams(window.location.search).get('tab') || localStorage.getItem('service_requests_tab') || '{{ $defaultServiceRequestsTab }}'
                    }" class="flex flex-col w-full">

        @if(auth()->user()->role === 'Priest')
            {{-- PRIEST VIEW: Shows only requests assigned to this priest --}}
            <div class="flex-1 pb-12 pr-2 w-full">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                    <div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white">My Assigned Applications</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Showing service requests assigned to you{{ $selected_service ? ' for ' . $selected_service->name : '' }}.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden search-results-container relative" x-data="tableSearch()" @click="handlePagination">
                    <!-- Removed blur loading state for instant feel -->
                    {{-- Filters for Priest --}}
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <form action="{{ route('service-requests.index') }}" method="GET" @submit.prevent="submitSearch"
                            class="flex flex-wrap items-center gap-4 w-full search-form">
                            @if($selected_service)
                                <input type="hidden" name="type" value="{{ $selected_service->name }}">
                            @endif

                            <div class="flex items-center gap-2 mr-2">
                                <label for="per_page" class="text-xs font-bold text-gray-400 uppercase tracking-wider">Show</label>
                                <select name="per_page" id="per_page" @change="submitSearch"
                                    class="dropdown-btn w-20 px-3 py-1.5 h-10">
                                    @foreach([5, 10, 15, 20, 50] as $n)
                                        <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Search Input -->
                            <div class="relative max-w-xs w-full lg:w-auto">
                                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search applicant..."
                                    @input.debounce.300ms="submitSearch"
                                    class="w-full lg:w-48 pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
                            </div>

                            <!-- Status Filter -->
                            <div class="relative w-full lg:w-auto">
                                <select name="status" @change="submitSearch"
                                    class="dropdown-btn w-full lg:w-auto">
                                    <option value="">All Status</option>
                                    <option value="For Priest Review" {{ request('status') == 'For Priest Review' ? 'selected' : '' }}>For Priest Review</option>
                                    <option value="For Payment" {{ request('status') == 'For Payment' ? 'selected' : '' }}>For Payment</option>
                                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="relative max-w-[150px] w-full lg:w-auto">
                                <input type="text" name="date_filter" value="{{ request('date_filter') }}"
                                    x-init="initAirDatepicker($el)" placeholder="Filter Date" readonly
                                    class="datepicker-input w-full lg:w-36 pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer text-gray-700 dark:text-gray-300 transition-all">
                                <i class="fas fa-calendar absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            </div>

                            <div class="flex items-center gap-2">
                                @if(request()->anyFilled(['status', 'date_filter', 'search']))
                                    <a href="{{ route('service-requests.index', array_filter(['type' => request('type')])) }}"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-medium transition-all px-2">
                                        <i class="fas fa-times-circle mr-1"></i>Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="w-full overflow-x-auto overflow-y-auto max-h-[calc(100vh-320px)] custom-scrollbar">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50/90 dark:bg-gray-900/90 backdrop-blur-md text-xs text-gray-600 uppercase tracking-wider font-bold border-b border-gray-100 dark:border-gray-800 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-4">Applicant</th>
                                    <th class="px-6 py-4">Service</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Time</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($requests as $req)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white text-base">
                                                {{ $req->applicant_name }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-medium mt-0.5">
                                                <i class="fas fa-phone mr-1 opacity-70"></i>
                                                {{ $req->contact_number ?: 'No Contact' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 font-bold text-xs rounded-lg border shadow-sm inline-block {{ ServiceHelper::getServiceBadgeClass($req->service_type) }}">
                                                {{ $req->service_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-medium">
                                            <i class="fas fa-calendar-alt text-gray-400 mr-1.5"></i>
                                            {{ $req->scheduled_date ? $req->scheduled_date->format('F d, Y') : 'TBD' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-medium">
                                            <i class="fas fa-clock text-gray-400 mr-1.5"></i>
                                            {{ $req->scheduled_time ? \Carbon\Carbon::parse($req->scheduled_time)->format('h:i A') : 'TBD' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $colors = [
                                                    'Pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                    'For Priest Review' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                    'For Payment' => 'bg-orange-100 text-orange-700 border-orange-200',
                                                    'Approved' => 'bg-green-100 text-green-700 border-green-200',
                                                    'Completed' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                    'Cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                                    'Declined' => 'bg-red-50 text-red-700 border-red-200'
                                                ];
                                            @endphp
                                            <span class="px-3 py-1 rounded-md text-xs font-bold border {{ $colors[$req->status] ?? 'bg-gray-100 border-gray-200 text-gray-600' }} shadow-sm">
                                                {{ $req->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button @click="openUpdateModal({{ $req }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-all shadow-sm"
                                                    title="View Details">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </button>
                                                <button @click="openUpdateModal({{ $req }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white transition-all shadow-sm {{ in_array($req->status, ['For Payment', 'Approved', 'Cancelled', 'Completed']) ? 'bg-gray-400 cursor-not-allowed opacity-70' : 'bg-blue-600 hover:bg-blue-700' }}"
                                                    title="{{ in_array($req->status, ['For Payment', 'Approved', 'Cancelled', 'Completed']) ? 'Request already ' . $req->status : 'Confirm Request' }}"
                                                    {{ in_array($req->status, ['For Payment', 'Approved', 'Cancelled', 'Completed']) ? 'disabled' : '' }}>
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200 dark:border-gray-700">
                                                <i class="fas fa-inbox text-2xl text-gray-400"></i>
                                            </div>
                                            <h4 class="text-base font-bold text-gray-900 dark:text-white">No assigned applications</h4>
                                            <p class="text-sm">No service requests have been assigned to you yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>

        @elseif($selected_service && auth()->user()->role !== 'Priest')
            <!-- TABS (Visible only when a service is selected) -->
            <div class="w-full -mt-2 mb-5 border-b border-gray-200 dark:border-gray-800 px-1 shrink-0">
                <nav class="flex space-x-8" aria-label="Tabs">
                    @if(Auth::user()->hasModule('service_requests_form'))
                    <button @click="activeTab = 'form'"
                        :class="activeTab === 'form' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-700'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2.5 outline-none">
                        <i class="fas fa-file-signature"></i> Application Form
                    </button>
                    @endif
                    @if(Auth::user()->hasModule('service_requests_records'))
                    <button @click="activeTab = 'records'"
                        :class="activeTab === 'records' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-700'"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2.5 outline-none">
                        <i class="fas fa-list-ul"></i> Applications List
                        <span
                            class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full text-xs font-bold border border-gray-200 dark:border-gray-700">{{ $requests->total() }}</span>
                    </button>
                    @endif
                </nav>
            </div>

            <!-- SCROLLABLE TAB CONTENT CONTAINER -->
            <div class="flex-1 pb-12 pr-2 w-full">

                <!-- TAB 1: Application Form Section -->
                <div x-show="activeTab === 'form'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    class="max-w-5xl mx-auto space-y-6 w-full">

                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div
                            class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                    <i class="fas {{ $selected_service->icon ?? 'fa-church' }} text-primary"></i>
                                    {{ $selected_service->name }} Application
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">Please fill in all required details carefully.</p>
                            </div>
                            <span
                                class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-bold border border-green-200 dark:border-green-800">
                                Fee: &#8369;{{ number_format($selected_service->fee, 2) }}
                            </span>
                        </div>

                        <form x-data="formPersistence('{{ $selected_service->name }}')"
                            @input.debounce.500ms="persist($event.target)" @change="persist($event.target)" @submit="clear()"
                            action="{{ route('service-requests.store') }}" method="POST"
                            class="px-6 pb-6 pt-0 space-y-8 mt-6 w-full">
                            @csrf
                            <input type="hidden" name="service_type" value="{{ $selected_service->name }}">

                            @php
                                $allFields = $selected_service->custom_fields ?? [];
                                if (is_string($allFields)) {
                                    $allFields = json_decode($allFields, true) ?? [];
                                }

                                // Ensure IDs exist for keys if missing
                                foreach($allFields as $idx => &$fld) {
                                    if (empty($fld['id'])) $fld['id'] = 'fld_' . $idx;
                                }
                            @endphp

                            <!-- Application Form Fields Based on Service Type Settings -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 w-full">
                                @forelse($allFields as $index => $field)
                                    @php
                                        $isRequired = !empty($field['required']);
                                        $label = $field['label'] ?? 'Untitled Field';
                                        $type = $field['type'] ?? 'text';

                                        if ($type === 'header') {
                                            $colSpan = 'col-span-1 md:col-span-12 mt-8 mb-2 first:mt-0';
                                        } elseif (in_array(strtolower($label), ['first name', 'middle name', 'middle initial', 'last name', 'suffix']) || str_contains(strtolower($label), "first name") || str_contains(strtolower($label), "middle name") || str_contains(strtolower($label), "middle initial") || str_contains(strtolower($label), "last name") || str_contains(strtolower($label), "suffix")) {
                                            if (str_contains(strtolower($label), "suffix")) {
                                                $colSpan = 'col-span-1 md:col-span-2';
                                            } elseif (str_contains(strtolower($label), "first name")) {
                                                $colSpan = 'col-span-1 md:col-span-4 md:col-start-1';
                                            } else {
                                                $colSpan = 'col-span-1 md:col-span-3';
                                            }
                                        } elseif (str_contains(strtolower($label), "marriage license")) {
                                            $colSpan = 'col-span-1 md:col-span-6';
                                            if (strtolower($label) === "marriage license no.") {
                                                 $colSpan .= ' md:col-start-1';
                                            }
                                        } elseif ($type === 'textarea') {
                                            $colSpan = 'col-span-1 md:col-span-12';
                                        } else {
                                            $colSpan = 'col-span-1 md:col-span-6';
                                        }

                                        $fieldName = 'custom_data[' . \Illuminate\Support\Str::slug($label, '_') . ']';
                                        if (in_array(strtolower($label), ['first name', 'middle name', 'middle initial', 'last name', 'contact number', 'contact no.', 'email address'])) {
                                            $fieldName = str_replace(' ', '_', strtolower($label));
                                            if ($fieldName == 'email_address') $fieldName = 'email';
                                            if ($fieldName == 'contact_no.') $fieldName = 'contact_number';
                                            if ($fieldName == 'middle_initial') $fieldName = 'middle_name';
                                        }
                                    @endphp

                                    @if($type === 'header')
                                        <div class="{{ $colSpan }}">
                                            <div class="flex items-center gap-3">
                                                <h3 class="text-[12px] font-extrabold text-primary uppercase tracking-[0.2em] whitespace-nowrap">{{ $label }}</h3>
                                                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="{{ $colSpan }}">
                                            <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-2">
                                                {{ $label }}
                                                @if(str_contains(strtolower($label), 'middle name') || str_contains(strtolower($label), 'middle initial'))
                                                    <span class="text-red-500">*</span>
                                                @elseif($isRequired)
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>

                                            @if($type === 'text')
                                                @if(str_contains(strtolower($label), 'middle name') || str_contains(strtolower($label), 'middle initial'))
                                                    <div x-data="{ naChecked: false, middleValue: '' }">
                                                        <input type="text" name="{{ $fieldName }}"
                                                            x-model="middleValue"
                                                            :required="!naChecked"
                                                            :readonly="naChecked"
                                                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-800 dark:text-white placeholder-gray-400 read-only:opacity-60 read-only:cursor-not-allowed"
                                                            placeholder="">
                                                        <label class="inline-flex items-center gap-1.5 mt-2 cursor-pointer select-none">
                                                            <input type="checkbox" x-model="naChecked"
                                                                @change="middleValue = naChecked ? 'N/A' : ''"
                                                                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                                                            <span class="text-[11px] text-gray-500 font-medium">N/A (No middle name/initial)</span>
                                                        </label>
                                                    </div>
                                                @else
                                                    <input type="text" name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }}
                                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-800 dark:text-white placeholder-gray-400"
                                                        placeholder="">
                                                @endif
                                            @elseif($type === 'date')
                                                <input type="date" name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }}
                                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-800 dark:text-white">
                                            @elseif($type === 'number')
                                                <input type="number" name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }}
                                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-800 dark:text-white"
                                                    placeholder="0">
                                            @elseif($type === 'textarea')
                                                <textarea name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }} rows="3"
                                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-800 dark:text-white placeholder-gray-400"
                                                    placeholder="Enter details here..."></textarea>
                                            @elseif($type === 'select')
                                                <div class="relative">
                                                    <select name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }}
                                                        class="dropdown-btn w-full">
                                                        <option value="">Select Option</option>
                                                        @if(isset($field['options']) && is_array($field['options']))
                                                            @foreach($field['options'] as $option)
                                                                <option value="{{ $option }}">{{ $option }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @empty
                                    <div class="col-span-1 md:col-span-12">
                                        <div class="text-center py-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                                            <div class="text-gray-400 mb-2"><i class="fas fa-clipboard-list text-2xl"></i></div>
                                            <p class="text-sm text-gray-400">No custom fields configured for this service.</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Requirements Checklist -->
                            @if($selected_service->requirements && count($selected_service->requirements) > 0)
                                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-3 mb-4">
                                        <h4 class="text-[12px] font-extrabold text-primary uppercase tracking-[0.2em] whitespace-nowrap">Requirements Checklist</h4>
                                        <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                        @foreach($selected_service->requirements as $req)
                                            <label
                                                class="flex items-center p-3.5 bg-gray-50 dark:bg-gray-900 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors border border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600">
                                                <input type="checkbox" name="requirements[]" value="{{ $req }}"
                                                    class="w-5 h-5 text-primary rounded border-gray-300 focus:ring-primary">
                                                <span
                                                    class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $req }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Schedule & Priest -->
                            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700" x-data="scheduleManager()">
                                <div class="flex items-center gap-3 mb-4">
                                    <h4 class="text-[12px] font-extrabold text-primary uppercase tracking-[0.2em] whitespace-nowrap">Officiant & Schedule</h4>
                                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-widest mb-2">
                                            Select Officiating Priest First <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <select name="priest_id" required x-model="selectedPriest" @change="fetchSchedule()"
                                                class="dropdown-btn w-full">
                                                <option value="" disabled selected>-- Select Priest --</option>
                                                @foreach($active_priests as $priest)
                                                    <option value="{{ $priest->id }}">{{ $priest->title ?? 'Fr.' }} {{ $priest->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-widest mb-2">
                                            Preferred Date <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" name="scheduled_date" required x-ref="dateInput"
                                                placeholder="Select Preferred Date" readonly disabled
                                                class="datepicker-input w-full pl-12 pr-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-800 dark:text-white placeholder-gray-400 cursor-pointer disabled:opacity-50">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 z-10">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-widest mb-2">
                                            Preferred Time <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" name="scheduled_time" required x-ref="timeInput"
                                                placeholder="Select Preferred Time" readonly disabled
                                                class="timepicker-input w-full pl-12 pr-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-800 dark:text-white placeholder-gray-400 cursor-pointer disabled:opacity-50">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 z-10">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                        </div>
                                        <p x-show="workingHoursText" x-text="workingHoursText" class="text-xs text-gray-500 font-medium mt-1"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Details & Status Blocks -->
                            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3 mb-4">
                                    <h4 class="text-[12px] font-extrabold text-primary uppercase tracking-[0.2em] whitespace-nowrap">Notes</h4>
                                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                </div>
                                <textarea name="details" rows="3"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors text-gray-800 dark:text-white placeholder-gray-400"
                                    placeholder="Any special requests, instructions, or notes..."></textarea>
                            </div>


                            <div
                                class="sticky bottom-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm p-4 -mx-6 -mb-6 mt-8 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                                <button type="submit"
                                    class="bg-primary hover:bg-blue-600 text-white px-8 py-3 rounded-xl font-bold uppercase tracking-wider shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                                    <i class="fas fa-paper-plane text-sm"></i> File Application Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TAB 2: Recent Applications Ledger -->
                <div x-show="activeTab === 'records'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    class="w-full">

                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                        <div>
                            <h3 class="font-bold text-xl text-gray-900 dark:text-white">Recent Applications</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Viewing all applications for
                                {{ $selected_service->name }}.
                            </p>
                        </div>

                    <div class="flex items-center gap-4 w-full lg:w-auto">
                        <!-- Space for future header actions -->
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden search-results-container relative" x-data="tableSearch()" @click="handlePagination">

                    <!-- Removed blur loading state for instant feel -->
                    <!-- Applications List Filters -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <form action="{{ route('service-requests.index') }}" method="GET" @submit.prevent="submitSearch"
                            class="flex flex-wrap items-center gap-4 w-full search-form">
                            <input type="hidden" name="type" value="{{ $selected_service->name }}">
                            <input type="hidden" name="tab" value="records">

                            <div class="flex items-center gap-2 mr-2">
                                <label for="per_page_records" class="text-xs font-bold text-gray-400 uppercase tracking-wider">Show</label>
                                <select name="per_page" id="per_page_records" @change="submitSearch"
                                    class="dropdown-btn w-20 px-3 py-1.5 h-10">
                                    @foreach([5, 10, 15, 20, 50] as $n)
                                        <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Search Input -->
                            <div class="relative max-w-xs w-full lg:w-auto">
                                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search applicant..."
                                    @input.debounce.500ms="submitSearch"
                                    class="w-full lg:w-48 pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
                            </div>

                            <!-- Status Filter -->
                             <div class="relative w-full lg:w-auto">
                                 <select name="status" @change="submitSearch"
                                     class="dropdown-btn w-full lg:w-auto">
                                     <option value="">All Status</option>
                                     <option value="For Priest Review" {{ request('status') == 'For Priest Review' ? 'selected' : '' }}>For Priest Review</option>
                                     <option value="For Payment" {{ request('status') == 'For Payment' ? 'selected' : '' }}>For Payment</option>
                                     <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                     <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                     <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                 </select>
                             </div>

                            <div class="relative max-w-[150px] w-full lg:w-auto">
                                <input type="text" name="date_filter" value="{{ request('date_filter') }}"
                                    x-init="initAirDatepicker($el)" placeholder="Filter Date" readonly
                                    class="datepicker-input w-full lg:w-36 pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer text-gray-700 dark:text-gray-300 transition-all">
                                <i class="fas fa-calendar absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            </div>

                            <div class="flex items-center gap-2">
                                @if(request()->anyFilled(['status', 'date_filter', 'search']))
                                    <a href="{{ route('service-requests.index', ['type' => $selected_service->name, 'tab' => 'records']) }}"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-medium transition-all px-2">
                                        <i class="fas fa-times-circle mr-1"></i>Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="w-full overflow-x-auto overflow-y-auto max-h-[calc(100vh-320px)] custom-scrollbar">
                            <table class="w-full text-sm text-left">
                                <thead
                                    class="bg-gray-50/90 dark:bg-gray-900/90 backdrop-blur-md text-xs text-gray-600 uppercase tracking-wider font-bold border-b border-gray-100 dark:border-gray-800 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-4">Applicant</th>
                                        <th class="px-6 py-4">Service Scope</th>
                                        <th class="px-6 py-4">Date</th>
                                        <th class="px-6 py-4">Time</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse($requests as $req)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-gray-900 dark:text-white text-base">
                                                    {{ $req->applicant_name }}
                                                </div>
                                                <div class="text-xs text-gray-500 font-medium mt-0.5"><i
                                                        class="fas fa-phone mr-1 opacity-70"></i>
                                                    {{ $req->contact_number ?: 'No Contact' }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-3 py-1 font-bold text-xs rounded-lg border shadow-sm inline-block {{ ServiceHelper::getServiceBadgeClass($req->service_type) }}">
                                                    <i
                                                        class="fas {{ $selected_service->icon ?? 'fa-church' }} mr-1.5 opacity-50"></i>
                                                    {{ $req->service_type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-medium">
                                                <i class="fas fa-calendar-alt text-gray-400 mr-1.5 hidden sm:inline-block"></i>
                                                {{ $req->scheduled_date ? $req->scheduled_date->format('F d, Y') : 'TBD' }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-medium">
                                                <i class="fas fa-clock text-gray-400 mr-1.5 hidden sm:inline-block"></i>
                                                {{ $req->scheduled_time ? \Carbon\Carbon::parse($req->scheduled_time)->format('h:i A') : 'TBD' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $colors = [
                                                        'Pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                        'Approved' => 'bg-green-100 text-green-700 border-green-200',
                                                        'Completed' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                        'Cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                                        'Declined' => 'bg-red-50 text-red-700 border-red-200'
                                                    ];
                                                @endphp
                                                <span
                                                    class="px-3 py-1 rounded-md text-xs font-bold border {{ $colors[$req->status] ?? 'bg-gray-100 border-gray-200 text-gray-600' }} shadow-sm">
                                                    {{ $req->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button @click="openUpdateModal({{ $req }})"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-all shadow-sm"
                                                        title="View Details">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </button>
                                                    @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'Secretary' || in_array($req->status, ['Pending', 'For Priest Review']))
                                                        <button @click="openEditModal({{ $req }})"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-all shadow-sm"
                                                            title="Edit Application">
                                                            <i class="fas fa-pencil-alt text-xs"></i>
                                                        </button>
                                                    @endif
                                                    <button @click="openUpdateModal({{ $req }})"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white transition-all shadow-sm {{ in_array($req->status, ['For Payment', 'Approved', 'Cancelled', 'Completed']) ? 'bg-gray-400 cursor-not-allowed opacity-70' : 'bg-blue-600 hover:bg-blue-700' }}"
                                                        title="{{ in_array($req->status, ['For Payment', 'Approved', 'Cancelled', 'Completed']) ? 'Request already ' . $req->status : 'Confirm Request' }}"
                                                        {{ in_array($req->status, ['For Payment', 'Approved', 'Cancelled', 'Completed']) ? 'disabled' : '' }}>
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                                <div
                                                    class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200 dark:border-gray-700">
                                                    <i class="fas fa-inbox text-2xl text-gray-400"></i>
                                                </div>
                                                <h4 class="text-base font-bold text-gray-900 dark:text-white">No records found
                                                </h4>
                                                <p class="text-sm">No applications have been submitted for this service yet.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                            {{ $requests->links() }}
                        </div>
                    </div>
                </div>
            </div> <!-- END SCROLLABLE TAB CONTENT CONTAINER -->

        @elseif(auth()->user()->role !== 'Priest')
            <!-- SCROLLABLE CONTAINER -->
            <div class="flex-1 pb-12 pr-2 w-full">
                <!-- Global Empty State Pick/View -->
            <div
                class="text-center py-16 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm mb-12">
                <div
                    class="bg-blue-50 dark:bg-blue-900/30 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <i class="fas fa-folder-open text-4xl text-primary drop-shadow"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Select a Service Type</h2>
                <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto leading-relaxed">Please select a service from the
                    left menu to view its application form and records.</p>
            </div>

            <div>
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-database text-primary"></i> All Applications
                    </h3>
                    <div class="flex items-center gap-4 w-full lg:w-auto">
                        <!-- Space for future header actions -->
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden search-results-container relative" x-data="tableSearch()" @click="handlePagination">
                    <!-- Removed blur loading state for instant feel -->
                    <!-- Filters -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <form action="{{ route('service-requests.index') }}" method="GET" @submit.prevent="submitSearch"
                            class="flex flex-wrap items-center gap-4 w-full search-form">

                            <!-- Search Input -->
                            <div class="relative max-w-xs w-full lg:w-auto">
                                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search applicant..."
                                    @input.debounce.500ms="submitSearch"
                                    class="w-full lg:w-48 pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
                            </div>

                            <div class="relative max-w-[200px] w-full lg:w-auto">
                                <select name="type" @change="submitSearch"
                                    class="dropdown-btn w-full lg:w-auto">
                                    <option value="">All Service Types</option>
                                    @foreach($service_types as $service)
                                        <option value="{{ $service->name }}" {{ request('type') == $service->name ? 'selected' : '' }}>
                                            {{ $service->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="relative w-full lg:w-auto">
                                <select name="status" onchange="this.form.submit()"
                                    class="dropdown-btn w-full lg:w-auto">
                                    <option value="">All Status</option>
                                    <option value="For Priest Review" {{ request('status') == 'For Priest Review' ? 'selected' : '' }}>For Priest Review</option>
                                    <option value="For Payment" {{ request('status') == 'For Payment' ? 'selected' : '' }}>For Payment</option>
                                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                @if(request()->anyFilled(['status', 'type', 'search']))
                                    <a href="{{ route('service-requests.index') }}"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-medium transition-all px-2">
                                        <i class="fas fa-times-circle mr-1"></i>Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="w-full overflow-x-auto overflow-y-auto max-h-[calc(100vh-320px)] custom-scrollbar">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="bg-gray-50/90 dark:bg-gray-900/90 backdrop-blur-md text-xs text-gray-600 uppercase tracking-wider font-bold border-b border-gray-100 dark:border-gray-800 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-4">Applicant</th>
                                    <th class="px-6 py-4">Service Scope</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Time</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($requests as $req)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white text-base">
                                                {{ $req->applicant_name }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-medium mt-0.5"><i
                                                    class="fas fa-phone mr-1 opacity-70"></i>
                                                {{ $req->contact_number ?: 'No Contact' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-1 font-bold text-xs rounded-lg border inline-block shadow-sm {{ ServiceHelper::getServiceBadgeClass($req->service_type) }}">
                                                {{ $req->service_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-medium whitespace-nowrap">
                                            {{ $req->scheduled_date ? $req->scheduled_date->format('F d, Y') : 'TBD' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-medium">
                                            {{ $req->scheduled_time ? \Carbon\Carbon::parse($req->scheduled_time)->format('h:i A') : 'TBD' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $colors = [
                                                    'Pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                    'Approved' => 'bg-green-100 text-green-700 border-green-200',
                                                    'Completed' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                    'Cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                                    'Declined' => 'bg-red-50 text-red-700 border-red-200'
                                                ];
                                            @endphp
                                            <span
                                                class="px-3 py-1 rounded-md text-xs font-bold border {{ $colors[$req->status] ?? 'bg-gray-100 border-gray-200 text-gray-600' }} shadow-sm relative whitespace-nowrap">
                                                {{ $req->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button @click="openUpdateModal({{ $req }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-all shadow-sm"
                                                    title="View Details">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </button>
                                                @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'Secretary' || in_array($req->status, ['Pending', 'For Priest Review']))
                                                    <button @click="openEditModal({{ $req }})"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-all shadow-sm"
                                                        title="Edit Application">
                                                        <i class="fas fa-pencil-alt text-xs"></i>
                                                    </button>
                                                @endif
                                                <button @click="openUpdateModal({{ $req }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white transition-all shadow-sm {{ in_array($req->status, ['For Payment', 'Approved', 'Cancelled', 'Completed']) ? 'bg-gray-400 cursor-not-allowed opacity-70' : 'bg-blue-600 hover:bg-blue-700' }}"
                                                    title="{{ in_array($req->status, ['For Payment', 'Approved', 'Cancelled', 'Completed']) ? 'Request already ' . $req->status : 'Confirm Request' }}"
                                                    {{ in_array($req->status, ['For Payment', 'Approved', 'Cancelled', 'Completed']) ? 'disabled' : '' }}>
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            <div
                                                class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200 dark:border-gray-700">
                                                <i class="fas fa-archive text-2xl text-gray-400"></i>
                                            </div>
                                            <h4 class="text-base font-bold text-gray-900 dark:text-white">Registry Empty</h4>
                                            <p class="text-sm">No applications have been generated across any services yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
            </div> <!-- END SCROLLABLE CONTAINER -->
        @endif

        <!-- Update Status Modal -->
        <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="modalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-4xl p-0 relative animate-fade-in-up max-h-[90vh] overflow-hidden flex flex-col">
                    <div
                        class="flex justify-between items-center p-6 bg-white dark:bg-gray-800 z-10 border-b border-gray-100 dark:border-gray-700 flex-none">
                        <h3 class="font-bold text-xl text-gray-800 dark:text-white">Request Details & Status</h3>
                        <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors"><i
                                class="fas fa-times text-lg"></i></button>
                    </div>

                    <form :action="updateUrl" method="POST" x-data="{ submitStatus: 'For Payment' }" class="flex-1 flex flex-col overflow-hidden">
                        @csrf
                        @method('PUT')
                        
                        <!-- Scrollable Content -->
                        <div class="flex-1 overflow-y-auto custom-scrollbar">
                            <div class="px-6 pb-0 pt-4">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <template x-for="(field, index) in getFields(selectedRequest.service_type)" :key="field.key">
                                <div class="col-span-12" :class="field.type === 'header' ? (index === 0 ? 'mb-2 mt-0' : 'mt-6 mb-2') : (field.type === 'textarea' ? 'md:col-span-12' : (field.label.toLowerCase().includes('suffix') ? 'md:col-span-2' : (['first name', 'given name'].some(n => field.label.toLowerCase().includes(n)) ? 'md:col-span-4' : (['middle name', 'maiden', 'last name', 'surname'].some(n => field.label.toLowerCase().includes(n)) ? 'md:col-span-3' : 'md:col-span-6'))))">
                                    <template x-if="field.type === 'header'">
                                        <div class="flex items-center gap-3">
                                            <h3 class="text-[12px] font-extrabold text-primary uppercase tracking-[0.2em] whitespace-nowrap" x-text="field.label"></h3>
                                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                        </div>
                                    </template>
                                    
                                    <template x-if="field.type !== 'header'">
                                        <div class="bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 h-full">
                                            <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-1" x-text="field.label"></label>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-words" x-text="getFieldValue(field)"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Requirements Checklist -->
                        <template x-if="selectedRequest.requirements && selectedRequest.requirements.length > 0">
                            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <h4 class="text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] mb-4">Requirements Submitted</h4>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="req in selectedRequest.requirements">
                                        <span
                                            class="px-3 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-sm font-medium rounded-md border border-green-200 dark:border-green-800 flex items-center gap-1.5">
                                            <i class="fas fa-check-circle"></i> <span x-text="req"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Officiant & Schedule -->
                        <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                             <div class="flex items-center gap-3 mb-4">
                                <h4 class="text-[12px] font-extrabold text-primary uppercase tracking-[0.2em] whitespace-nowrap">Officiant & Schedule</h4>
                                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                <div class="col-span-12 bg-white dark:bg-gray-800/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-1">Officiating Priest</label>
                                    <div class="text-sm font-bold text-primary flex items-center gap-2">
                                        <i class="fas fa-user-tie opacity-70"></i>
                                        <span x-text="selectedRequest.priest ? (selectedRequest.priest.title || 'Fr.') + ' ' + selectedRequest.priest.name : 'Not Assigned'"></span>
                                    </div>
                                </div>
                                <div class="col-span-12 md:col-span-6 bg-white dark:bg-gray-800/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-1">Scheduled Date</label>
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="selectedRequest.scheduled_date ? new Date(selectedRequest.scheduled_date.slice(0, 10) + 'T00:00:00').toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' }) : 'N/A'"></div>
                                </div>
                                <div class="col-span-12 md:col-span-6 bg-white dark:bg-gray-800/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-1">Scheduled Time</label>
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="selectedRequest.scheduled_time ? new Date('1970-01-01T' + selectedRequest.scheduled_time.split(' ')[0]).toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit',hour12:true}) : 'N/A'"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes/Details display in View -->
                        <template x-if="selectedRequest.details">
                            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <h4 class="text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] mb-2">Notes</h4>
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-300 italic" x-text="selectedRequest.details"></div>
                            </div>
                        </template>

                        <!-- Status Badge -->
                        <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                            <div class="bg-white dark:bg-gray-800/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                <label class="text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em]">Application Status</label>
                                <div :class="{
                                    'bg-yellow-100 text-yellow-700 border-yellow-200': selectedRequest.status === 'Pending',
                                    'bg-blue-50 text-blue-700 border-blue-100': selectedRequest.status === 'For Priest Review',
                                    'bg-indigo-50 text-indigo-700 border-indigo-100': selectedRequest.status === 'For Payment',
                                    'bg-green-100 text-green-700 border-green-200': selectedRequest.status === 'Approved',
                                    'bg-emerald-100 text-emerald-700 border-emerald-200': selectedRequest.status === 'Completed',
                                    'bg-red-100 text-red-700 border-red-200': selectedRequest.status === 'Cancelled' || selectedRequest.status === 'Declined',
                                    'bg-gray-100 text-gray-600 border-gray-200': !['Pending', 'For Priest Review', 'For Payment', 'Approved', 'Completed', 'Cancelled', 'Declined'].includes(selectedRequest.status)
                                }" class="text-xs font-bold px-4 py-2 rounded-lg border shadow-sm" x-text="selectedRequest.status || 'N/A'"></div>
                            </div>
                        </div>

                        </div>

                            <div class="px-6 py-2 border-t border-gray-100 dark:border-gray-700">
                                <!-- Hidden inputs for validation -->

                        <!-- Hidden inputs for validation -->
                        <input type="hidden" name="service_type" :value="selectedRequest.service_type">
                        <input type="hidden" name="scheduled_date" :value="selectedRequest.scheduled_date">
                        <input type="hidden" name="first_name" :value="selectedRequest.first_name">
                        <input type="hidden" name="last_name" :value="selectedRequest.last_name">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <input type="hidden" name="status" :value="submitStatus">

                            @if(auth()->user()->role === 'Treasurer')
                                <div class="bg-white dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-widest mb-2">Payment Status Update</label>
                                    <select name="payment_status" x-model="selectedRequest.payment_status"
                                        class="dropdown-btn w-full">
                                        <option value="Pending">Pending</option>
                                        <option value="Paid">Paid</option>
                                        <option value="Waived">Waived</option>
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="payment_status" :value="selectedRequest.payment_status">
                            @endif
                        </div>

                        <!-- Remarks Section with Improved Spacing -->
                        <div class="space-y-3 mt-0">
                            <div class="flex items-center gap-3">
                                <h4 class="text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] whitespace-nowrap">Remarks / Comments (Optional)</h4>
                                <div class="flex-1 h-px bg-gray-100 dark:bg-gray-700"></div>
                            </div>
                            <textarea name="remarks" rows="2"
                                placeholder="Add a note or instruction for this update..."
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-800 dark:text-white resize-none shadow-sm placeholder-gray-400"></textarea>
                            <div class="flex items-center gap-2 text-[10px] text-gray-600 font-medium">
                                <i class="fas fa-info-circle text-blue-400"></i>
                                <span>Adding a remark will send an in-app notification to relevant staff.</span>
                            </div>
                        </div>
                        </div>
                    </div>

                        <div class="p-5 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700 flex flex-wrap justify-end gap-3 flex-none relative z-20">
                            <button type="button" @click="modalOpen = false"
                                class="px-6 py-2.5 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 font-bold transition-all text-sm">Cancel</button>

                            <button type="submit" @click="submitStatus = 'Declined'"
                                x-show="!['Approved', 'Cancelled', 'Completed', 'Declined'].includes(selectedRequest.status)"
                                class="px-6 py-2.5 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 border border-red-200 dark:border-red-800 font-bold transition-all text-sm shadow-sm">Reject Request</button>

                            <button type="submit" @click="submitStatus = 'For Payment'"
                                :class="['Approved', 'Cancelled', 'Completed', 'Declined'].includes(selectedRequest.status) ? 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed shadow-none border border-gray-100 dark:border-gray-800' : 'bg-primary hover:bg-blue-600 text-white shadow-lg shadow-blue-500/30'"
                                :disabled="['Approved', 'Cancelled', 'Completed', 'Declined'].includes(selectedRequest.status)"
                                class="px-8 py-2.5 rounded-xl font-bold transition-all text-sm">Confirm Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Application Modal -->
        <div x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="editModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-4xl p-0 relative animate-fade-in-up max-h-[90vh] overflow-hidden flex flex-col">
                    <div class="flex justify-between items-center p-6 sticky top-0 bg-white dark:bg-gray-800 z-30 border-b border-gray-100 dark:border-gray-700 flex-none">
                        <div>
                            <h3 class="font-bold text-xl text-gray-800 dark:text-white">Edit Application Details</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Correct typos or update information for <span class="font-bold text-primary" x-text="selectedRequest.applicant_name"></span></p>
                        </div>
                        <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                    <form :action="updateUrl" method="POST" class="flex-1 flex flex-col min-h-0 overflow-hidden">
                        @csrf
                        @method('PUT')
                        
                        <!-- Scrollable Content Area -->
                        <div class="flex-1 min-h-0 overflow-y-auto custom-scrollbar">
                            <!-- Hidden meta fields (Outside scrollable padding to avoid gaps) -->
                            <div class="hidden">
                                <input type="hidden" name="status" :value="selectedRequest.status">
                                <input type="hidden" name="payment_status" :value="selectedRequest.payment_status">
                                <input type="hidden" name="service_type" :value="selectedRequest.service_type">
                                <input type="hidden" name="priest_id" :value="selectedRequest.priest_id">
                            </div>

                            <div class="px-6 pb-0 pt-4">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 w-full">
                                  <!-- Dynamic Fields (Top of Edit) -->
                                  <template x-for="(field, index) in getFields(selectedRequest.service_type)" :key="field.key">
                                      <div class="col-span-12" :class="field.type === 'header' ? (index === 0 ? 'col-span-12 mb-2 mt-0' : 'col-span-12 mt-6 mb-2') : (field.type === 'textarea' ? 'md:col-span-12' : (field.label.toLowerCase().includes('suffix') ? 'md:col-span-2' : (['first name', 'given name'].some(n => field.label.toLowerCase().includes(n)) ? 'md:col-span-4 md:col-start-1' : (['middle name', 'maiden', 'last name', 'surname'].some(n => field.label.toLowerCase().includes(n)) ? 'md:col-span-3' : 'md:col-span-6'))))">
                                          <template x-if="field.type === 'header'">
                                              <div class="flex items-center gap-3">
                                                  <h3 class="text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] whitespace-nowrap" x-text="field.label"></h3>
                                                  <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                              </div>
                                          </template>

                                          <template x-if="field.type !== 'header'">
                                              <div>
                                                  <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-1">
                                                      <span x-text="field.label"></span>
                                                      <template x-if="field.required">
                                                          <span class="text-red-500">*</span>
                                                      </template>
                                                  </label>

                                                  <template x-if="field.type === 'text' || field.type === 'number'">
                                                      <input :type="field.type" :name="field.mapping" 
                                                          :value="getFieldValue(field, true)"
                                                          :required="field.required"
                                                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-800 dark:text-white placeholder-gray-400">
                                                  </template>

                                                  <template x-if="field.type === 'date'">
                                                      <input type="date" :name="field.mapping" 
                                                          :value="getFieldValue(field, true)"
                                                          :required="field.required"
                                                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-800 dark:text-white">
                                                  </template>

                                                  <template x-if="field.type === 'textarea'">
                                                      <textarea :name="field.mapping" :value="getFieldValue(field, true)"
                                                          :required="field.required"
                                                          rows="3"
                                                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-800 dark:text-white placeholder-gray-400"></textarea>
                                                  </template>

                                                  <template x-if="field.type === 'select'">
                                                      <div class="relative">
                                                          <select :name="field.mapping" :required="field.required"
                                                              class="dropdown-btn w-full">
                                                              <option value="">Select Option</option>
                                                              <template x-for="opt in field.options" :key="opt">
                                                                  <option :value="opt" :selected="opt === getFieldValue(field, true)" x-text="opt"></option>
                                                              </template>
                                                          </select>
                                                      </div>
                                                  </template>
                                              </div>
                                          </template>
                                      </div>
                                  </template>

                                 <!-- Requirements Section -->
                                 <template x-if="selectedRequest.requirements && selectedRequest.requirements.length > 0">
                                     <div class="col-span-12 mt-8">
                                         <div class="flex items-center gap-3 mb-4">
                                             <h4 class="text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] whitespace-nowrap">Requirements Submitted</h4>
                                             <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                         </div>
                                         <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                             <template x-for="req in selectedRequest.requirements" :key="req">
                                                 <div class="flex items-center p-3.5 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-700">
                                                     <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                                     <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="req"></span>
                                                     <input type="hidden" name="requirements[]" :value="req">
                                                 </div>
                                             </template>
                                         </div>
                                     </div>
                                 </template>

                                 <div class="col-span-12 mt-8 mb-4">
                                     <div class="flex items-center gap-3">
                                         <h3 class="text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] whitespace-nowrap">Officiant & Schedule</h3>
                                         <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                     </div>
                                 </div>

                                 <!-- Officiant & Schedule (Fixed) -->
                                 <div class="col-span-12 md:col-span-12 bg-gray-50/50 dark:bg-gray-900/30 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                     <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-1">Officiating Priest (Fixed)</label>
                                     <div class="relative">
                                         <input type="text" readonly
                                             :value="selectedRequest.priest ? (selectedRequest.priest.title || 'Fr.') + ' ' + selectedRequest.priest.name : 'Not Assigned'"
                                             class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-xl text-gray-400 cursor-not-allowed opacity-70">
                                     </div>
                                 </div>

                                 <div class="col-span-12 md:col-span-6 bg-gray-50/50 dark:bg-gray-900/30 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                     <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-1">Scheduled Date (Fixed)</label>
                                     <div class="relative">
                                         <input type="date" name="scheduled_date" x-model="editData.scheduled_date" readonly
                                             class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-xl text-gray-400 cursor-not-allowed opacity-70"
                                             value="">
                                     </div>
                                 </div>
                                 <div class="col-span-12 md:col-span-6 bg-gray-50/50 dark:bg-gray-900/30 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                     <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-widest mb-1">Scheduled Time (Fixed)</label>
                                     <div class="relative">
                                         <input type="time" name="scheduled_time" x-model="editData.scheduled_time" readonly
                                             class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-xl text-gray-400 cursor-not-allowed opacity-70"
                                             value="">
                                     </div>
                                 </div>

                                 <!-- Notes/Details -->
                                 <div class="col-span-12 mt-8">
                                     <div class="flex items-center gap-3 mb-4">
                                         <h4 class="text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] whitespace-nowrap">Notes</h4>
                                         <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                     </div>
                                     <textarea name="details" rows="3" x-model="editData.details"
                                         class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-800 dark:text-white placeholder-gray-400"
                                         placeholder="Any special requests, instructions, or notes..."></textarea>
                                 </div>{{-- /notes div --}}
                             </div>{{-- /grid --}}
                            </div>{{-- /px-6 wrapper --}}
                        </div>{{-- /scrollable div --}}

                        <!-- Fixed Footer -->
                        <div class="p-5 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 flex-none relative z-20">
                            <button type="button" @click="editModalOpen = false"
                                class="px-6 py-2.5 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 font-bold transition-all text-sm">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-8 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all text-sm flex items-center gap-2">
                                <i class="fas fa-save text-xs"></i> Save Changes
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>


    @push('scripts')
        <script>

        </script>
    @endpush
    <!-- Flatpickr Initialization -->
    <script>
        <!-- Global     Datepicker Initializer -->
        function initAirDatepicker(el) {
            new AirDatepicker(el, {
                locale: {
                    days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                    daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    daysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    today: 'Today',
                    clear: 'Clear',
                    dateFormat: 'yyyy-MM-dd',
                    timeFormat: 'hh:ii aa',
                    firstDay: 0
                },
                dateFormat: 'yyyy-MM-dd',
                autoClose: true,
                isMobile: false,
                buttons: ['today', 'clear'],
                container: 'body'
            });
        }

        function initFlatpickrTime(el) {
            flatpickr(el, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i K",
                time_24hr: false,
                disableMobile: true,
                static: false,
                appendTo: document.body
            });
        }
    </script>
    <style>
        /* Show and fix date picker icons for dark mode */
        .dark input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(1.5) contrast(1.2);
            cursor: pointer;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
        }

        .air-datepicker,
        .air-datepicker-global-container {
            z-index: 9999 !important;
        }
        .flatpickr-calendar {
            z-index: 9999 !important;
            position: fixed !important;
        }
    </style>
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('formPersistence', (serviceName) => ({
                    // ... existing persistence logic ...
                    storageKey: `service_request_${serviceName}`,
                    formData: {},
                    // ...
                    init() {
                        // Load data from localStorage
                        const savedData = localStorage.getItem(this.storageKey);
                        if (savedData) {
                            this.formData = JSON.parse(savedData);
                            this.restoreFormData();
                        }
                    },

                    persist(el) {
                        if (!el.name) return;

                        // Handle different input types
                        if (el.type === 'checkbox') {
                            if (!this.formData[el.name]) this.formData[el.name] = [];
                            // Handle array of checkboxes (requirements[])
                            if (el.name.endsWith('[]')) {
                                // Current logic simplistic for array, but sufficient for restoring checked state if we iterate
                                // Actually, for arrays, we need to store the array of values.
                                // Let's rely on reading the form state fully or updating the specific value.
                                // Simpler: Just map name -> value (or array of values for checkboxes)
                                // For simplicity in this specific form, let's just store the value.
                                // But wait, requirements[] needs special handling.
                                // Let's grab all checked boxes for that name.
                                const checkboxes = document.querySelectorAll(`input[name="${el.name}"]:checked`);
                                this.formData[el.name] = Array.from(checkboxes).map(cb => cb.value);
                            } else {
                                this.formData[el.name] = el.checked;
                            }
                        } else {
                            this.formData[el.name] = el.value;
                        }

                        localStorage.setItem(this.storageKey, JSON.stringify(this.formData));
                    },

                    restoreFormData() {
                        Object.keys(this.formData).forEach(name => {
                            const value = this.formData[name];
                            const inputs = document.querySelectorAll(`[name="${name}"]`);

                            if (inputs.length === 0) return;

                            inputs.forEach(el => {
                                if (el.type === 'checkbox' || el.type === 'radio') {
                                    if (Array.isArray(value)) {
                                        el.checked = value.includes(el.value);
                                    } else {
                                        el.checked = (el.value === value) || (value === true); // Handle boolean or value match
                                    }
                                } else {
                                    el.value = value;
                                }
                            });
                        });
                    },

                    clear() {
                        localStorage.removeItem(this.storageKey);
                    }
                }));

                Alpine.data('requestManager', () => ({
                    modalOpen: false,
                    editModalOpen: false,
                    selectedRequest: {},
                    editData: {},
                    updateUrl: '',
                    serviceDefinitions: @json($service_types),
                    selectedService: '{{ request('type') }}',

                    init() {
                        this.$watch('activeTab', value => {
                            if (value) localStorage.setItem('service_requests_tab', value);
                        });

                        this.$watch('selectedService', value => {
                            if (value && value !== '{{ request('type') }}') {
                                window.location.href = '{{ route('service-requests.index') }}?type=' + value;
                            }
                        });

                        @if(isset($autoOpenRequest) && $autoOpenRequest)
                            setTimeout(() => {
                                this.openUpdateModal(@json($autoOpenRequest));
                            }, 100);
                        @endif
                    },

                    openEditModal(req) {
                        this.selectedRequest = req;
                        this.editData = { ...req };
                        
                        // Fix for Date formatting in input[type="date"]
                        if (this.editData.scheduled_date) {
                            this.editData.scheduled_date = this.editData.scheduled_date.slice(0, 10);
                        }

                        // Parse custom_data safely for both editData and selectedRequest
                        let parsed = {};
                        if (typeof req.custom_data === 'string') {
                            try {
                                parsed = JSON.parse(req.custom_data);
                            } catch(e) { }
                        } else {
                            parsed = req.custom_data || {};
                        }
                        this.editData.custom_data = parsed;
                        this.selectedRequest.custom_data_parsed = parsed;
                        
                        this.updateUrl = `{{ route('service-requests.update', 'ID') }}`.replace('ID', req.id);
                        this.editModalOpen = true;
                    },

                    openUpdateModal(req) {
                        this.selectedRequest = req;
                        
                        // Parse custom_data safely for the view modal
                        if (typeof this.selectedRequest.custom_data === 'string') {
                            try {
                                this.selectedRequest.custom_data_parsed = JSON.parse(this.selectedRequest.custom_data);
                            } catch(e) {
                                this.selectedRequest.custom_data_parsed = {};
                            }
                        } else {
                            this.selectedRequest.custom_data_parsed = this.selectedRequest.custom_data || {};
                        }
                        
                        this.updateUrl = `{{ route('service-requests.update', 'ID') }}`.replace('ID', req.id);
                        this.modalOpen = true;
                    },

                    getFields(serviceTypeName) {
                        const serviceDef = this.serviceDefinitions.find(s => s.name === serviceTypeName);
                        let customFields = [];
                        if (serviceDef && serviceDef.custom_fields) {
                            try {
                                customFields = typeof serviceDef.custom_fields === 'string' 
                                    ? JSON.parse(serviceDef.custom_fields) 
                                    : serviceDef.custom_fields;
                            } catch (e) {
                                console.error('Error parsing custom fields', e);
                            }
                        }

                        if (!Array.isArray(customFields)) customFields = [];

                        // Always include standard fields that are NOT part of customized fields if they are missing
                        // but actually the user wants THE ORDER of the custom_fields.
                        // So we just map the custom_fields.

                        return customFields.map(cf => {
                            let label = cf.label || 'Untitled';
                            let key = label.toLowerCase()
                                            .replace(/'/g, '')
                                            .replace(/[^a-z0-9]+/g, '_')
                                            .replace(/(^_|_$)/g, '');
                            
                            // Mapping logic corresponding to the PHP side
                            let mapping = 'custom_data[' + key + ']';
                            let column = null;
                            
                            const standardLabels = ['first name', 'middle name', 'middle initial', 'last name', 'contact number', 'contact no.', 'email address'];
                            if (standardLabels.includes(label.toLowerCase())) {
                                column = label.toLowerCase().replace(/ /g, '_');
                                if (column === 'email_address') column = 'email';
                                if (column === 'contact_no.') column = 'contact_number';
                                if (column === 'middle_initial') column = 'middle_name';
                                mapping = column;
                            }

                            return {
                                label: label,
                                key: key,
                                type: cf.type || 'text',
                                options: cf.options || [],
                                required: !!cf.required,
                                column: column,
                                mapping: mapping
                            };
                        });
                    },

                    getFieldValue(field, isEdit = false) {
                        const fallback = 'N/A';
                        if (!this.selectedRequest) return fallback;
                        
                        // If it's a root column
                        let val = '';
                        if (field.column && this.selectedRequest[field.column] !== undefined && this.selectedRequest[field.column] !== null) {
                            val = String(this.selectedRequest[field.column]).trim();
                        } else {
                            // If it's in custom_data
                            const customData = this.selectedRequest.custom_data_parsed || {};
                            if (customData[field.key] !== undefined && customData[field.key] !== null) {
                                val = String(customData[field.key]).trim();
                            }
                        }
                        
                        // Skip "N/A" for date/select if they are empty
                        if (isEdit && (field.type === 'date' || field.type === 'select') && (val === '' || val === 'N/A')) {
                            return '';
                        }
                        
                        return (val === '' || val === null) ? fallback : val;
                    }
                }));

                Alpine.data('scheduleManager', () => ({
                    selectedPriest: '',
                    priests: @json($active_priests),
                    bookedEvents: { active_requests: [], active_schedules: [] },
                    datePickerInstance: null,
                    timePickerInstance: null,
                    workingHoursText: '',
                    
                    init() {
                        // Allow persistence data to load first
                        setTimeout(() => {
                            if (this.selectedPriest) {
                                this.fetchSchedule();
                            }
                        }, 500);
                    },

                    fetchSchedule() {
                        if (!this.selectedPriest) return;
                        
                        this.$refs.dateInput.value = '';
                        this.$refs.timeInput.value = '';
                        this.$refs.dateInput.disabled = true;
                        this.$refs.timeInput.disabled = true;
                        this.workingHoursText = '';

                        if (this.selectedPriest === 'any') {
                            this.initPickers(null);
                            return;
                        }

                        const priest = this.priests.find(p => p.id == this.selectedPriest);
                        
                        fetch('/api/priest-schedule/' + this.selectedPriest)
                            .then(res => res.json())
                            .then(data => {
                                this.bookedEvents = data;
                                // Merge latest fetched config with local priests array just in case
                                priest.working_days = data.working_days;
                                priest.working_hours = data.working_hours;
                                priest.max_services_per_day = data.max_services;
                                
                                this.initPickers(priest);
                            })
                            .catch(err => {
                                console.error('Error fetching priest schedule:', err);
                                this.initPickers(priest);
                            });
                    },

                    initPickers(priest) {
                        if (this.datePickerInstance) this.datePickerInstance.destroy();
                        if (this.timePickerInstance) this.timePickerInstance.destroy();

                        this.$refs.dateInput.disabled = false;
                        this.$refs.timeInput.disabled = false;

                        let workingDays = [];
                        let minTime = null;
                        let maxTime = null;
                        let maxServices = null;

                        if (priest) {
                            workingDays = priest.working_days || [];
                            if (priest.working_hours && priest.working_hours.start) {
                                minTime = priest.working_hours.start;
                                maxTime = priest.working_hours.end;
                                this.workingHoursText = `Available: ${this.tConvert(minTime)} - ${this.tConvert(maxTime)}`;
                            }
                            maxServices = priest.max_services_per_day;
                        }

                        // Calculate fully booked dates (if any date has >= maxServices active requests)
                        const dateCounts = {};
                        if (this.bookedEvents.active_requests) {
                            this.bookedEvents.active_requests.forEach(req => {
                                const d = req.scheduled_date.split('T')[0]; // Extract YYYY-MM-DD
                                dateCounts[d] = (dateCounts[d] || 0) + 1;
                            });
                        }
                        if (this.bookedEvents.active_schedules) {
                            this.bookedEvents.active_schedules.forEach(sch => {
                                const d = sch.start_datetime.split(' ')[0]; // Extract YYYY-MM-DD
                                dateCounts[d] = (dateCounts[d] || 0) + 1;
                            });
                        }

                        const fullyBookedDates = Object.keys(dateCounts).filter(dateStr => dateCounts[dateStr] >= maxServices);

                        this.datePickerInstance = new AirDatepicker(this.$refs.dateInput, {
                            locale: {
                                days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                                daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                                daysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                                months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                                monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                                today: 'Today',
                                clear: 'Clear',
                                dateFormat: 'yyyy-MM-dd',
                                firstDay: 0
                            },
                            minDate: new Date(),
                            autoClose: true,
                            isMobile: false,
                            container: 'body',
                            onSelect: ({date}) => {
                                if (!date) {
                                    if (minTime && maxTime) {
                                        this.workingHoursText = `Available: ${this.tConvert(minTime)} - ${this.tConvert(maxTime)}`;
                                    }
                                    return;
                                }

                                const dateStr = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
                                
                                let bookedTimes = [];
                                
                                if (this.bookedEvents.active_requests) {
                                    this.bookedEvents.active_requests.forEach(req => {
                                        if (req.scheduled_date && req.scheduled_date.startsWith(dateStr) && req.scheduled_time) {
                                            bookedTimes.push(this.tConvert(req.scheduled_time));
                                        }
                                    });
                                }
                                
                                if (this.bookedEvents.active_schedules) {
                                    this.bookedEvents.active_schedules.forEach(sch => {
                                        if (sch.start_datetime && sch.start_datetime.startsWith(dateStr)) {
                                            let timePart = sch.start_datetime.split(' ')[1];
                                            if (timePart) bookedTimes.push(this.tConvert(timePart));
                                        }
                                    });
                                }
                                
                                let uniqueBookedTimes = [...new Set(bookedTimes)];
                                
                                if (minTime && maxTime) {
                                    let baseText = `Available: ${this.tConvert(minTime)} - ${this.tConvert(maxTime)}`;
                                    if (uniqueBookedTimes.length > 0) {
                                        this.workingHoursText = `${baseText} (Already Booked: ${uniqueBookedTimes.join(', ')})`;
                                    } else {
                                        this.workingHoursText = baseText;
                                    }
                                }
                            },
                            onRenderCell: ({date, cellType}) => {
                                if (cellType === 'day') {
                                    const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
                                    const dateStr = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
                                    
                                    // Disable un-working days
                                    if (workingDays.length > 0 && !workingDays.includes(dayName)) {
                                        return { disabled: true, classes: 'bg-gray-100 opacity-50 cursor-not-allowed' };
                                    }
                                    
                                    // Disable fully booked dates
                                    if (fullyBookedDates.includes(dateStr)) {
                                        return { disabled: true, classes: 'bg-red-50 text-red-300 opacity-50 cursor-not-allowed text-xs font-bold' };
                                    }
                                }
                            }
                        });

                        let timeConfig = {
                            enableTime: true,
                            noCalendar: true,
                            dateFormat: "h:i K",
                            time_24hr: false,
                            disableMobile: true,
                            static: false,
                            appendTo: document.body
                        };
                        
                        if (minTime && maxTime) {
                            timeConfig.minTime = minTime;
                            timeConfig.maxTime = maxTime;
                        }
                        
                        this.timePickerInstance = flatpickr(this.$refs.timeInput, timeConfig);
                    },
                    
                    tConvert(time) {
                        if (!time) return '';
                        let [h, m] = time.substring(0,5).split(':');
                        let part = h >= 12 ? 'PM' : 'AM';
                        h = h % 12 || 12;
                        return `${h}:${m} ${part}`;
                    }
                }));
            });
        </script>
    @endpush
@endsection
