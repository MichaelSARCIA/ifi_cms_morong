@extends('layouts.app')
@use('App\Helpers\ServiceHelper')

@section('title', 'Donations')
@section('role_label', 'Treasurer')



@php
    $pageTitle = $isFeePage ?? false ? 'Services Fees Registry' : 'Donations Registry';
    $pageSubtitle = $isFeePage ?? false ? 'Manage service fees and payments' : 'Manage donations and tithes';
    $btnLabel = $isFeePage ?? false ? 'Record Fee' : 'Record Donation';
    $payerLabel = $isFeePage ?? false ? 'Payer Name' : 'Donor Name';
@endphp

@section('page_title', $pageTitle)
@section('page_subtitle', $pageSubtitle)

    @section('content')
    <!-- ACTION BAR -->
    @if(!($isFeePage ?? false))
        <div class="flex justify-end shrink-0 mb-6">
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="flex items-center gap-2 bg-gradient-to-r from-primary to-blue-600 hover:from-blue-700 hover:to-primary text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                <i class="fas fa-plus"></i> {{ $btnLabel }}
            </button>
        </div>
    @endif
    <!-- TABLE -->
    <div x-data="tableSearch()">
        <!-- Filters -->
        <div class="p-6 bg-white dark:bg-gray-800 rounded-t-3xl border border-gray-100 dark:border-gray-700 border-b-0 shadow-sm relative z-20">
            @if($isFeePage ?? false)
                <form action="{{ route('donations') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full search-form" @submit.prevent="submitSearch">
                    <input type="hidden" name="type" value="fee">
                    

                    <!-- Search Input -->
                    <div class="relative max-w-xs w-full lg:w-auto">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search payer..."
                            @input.debounce.50ms="submitSearch"
                            class="w-full lg:w-48 pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
                    </div>

                    <div class="relative max-w-[200px] w-full lg:w-auto">
                        <select name="service_type" @change="submitSearch"
                            class="dropdown-btn w-full lg:w-auto">
                            <option value="">All Services</option>
                            @foreach($services as $service)
                                <option value="{{ $service->name }}" {{ request('service_type') == $service->name ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(Auth::user()->role !== 'Priest')
                    <div class="relative w-full lg:w-auto">
                        <select name="payment_status" @change="submitSearch"
                            class="dropdown-btn w-full lg:w-auto">
                            <option value="">All Status</option>
                            <option value="Paid" {{ request('payment_status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                            <option value="Unpaid" {{ request('payment_status') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    @endif

                    <div class="flex items-center gap-2">
                        @if(request()->anyFilled(['service_type', 'payment_status']))
                            <button type="button" @click="clearFilters()"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-bold transition-all px-2 flex items-center gap-1">
                                <i class="fas fa-times-circle"></i>Clear
                            </button>
                        @endif
                    </div>
                </form>
            @else
                <form action="{{ route('donations') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full search-form" @submit.prevent="submitSearch">
                    


                    <!-- Search Input -->
                    <div class="relative max-w-xs w-full lg:w-auto">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search donor..."
                            @input.debounce.50ms="submitSearch"
                            class="w-full lg:w-48 pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
                    </div>

                    <div class="relative max-w-[200px] w-full lg:w-auto">
                        <select name="donation_type" @change="submitSearch"
                            class="dropdown-btn w-full lg:w-auto">
                            <option value="">All Donation Types</option>
                            <option value="General Donation" {{ request('donation_type') == 'General Donation' ? 'selected' : '' }}>General Donation</option>
                            <option value="Tithes" {{ request('donation_type') == 'Tithes' ? 'selected' : '' }}>Tithes</option>
                            <option value="Love Offering" {{ request('donation_type') == 'Love Offering' ? 'selected' : '' }}>Love Offering</option>
                            <option value="Others" {{ request('donation_type') == 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(request()->anyFilled(['donation_type']))
                            <button type="button" @click="clearFilters()"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-bold transition-all px-2 flex items-center gap-1">
                                <i class="fas fa-times-circle"></i>Clear
                            </button>
                        @endif
                    </div>
                </form>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-b-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden search-results-container relative" @click="handlePagination">
            <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-320px)] custom-scrollbar">
            <table class="w-full text-left border-collapse relative">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 text-sm font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">{{ $payerLabel }}</th>
                        <th class="px-6 py-4">{{ $isFeePage ? 'Service Type' : 'Type' }}</th>
                        <th class="px-6 py-4 text-right">Amount</th>
                        @if($isFeePage)
                            @if(Auth::user()->role !== 'Priest')
                                <th class="px-6 py-4 text-center">Payment</th>
                            @endif
                            <th class="px-6 py-4 text-center">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse ($donations as $row)
                        <tr id="row-{{ $row->id }}" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                            @if($isFeePage ?? false)
                                <!-- SERVICE FEES TABLE ROW -->
                                <td class="px-6 py-4 text-base font-bold text-gray-900 dark:text-white">
                                    {{ $row->created_at->format('F d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm">
                                            {{ strtoupper(substr($row->first_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-base font-bold text-gray-900 dark:text-white leading-tight">{{ $row->first_name }}
                                                {{ $row->last_name }}
                                            </p>
                                            <p class="text-xs text-gray-400">Req #{{ $row->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-bold {{ ServiceHelper::getServiceBadgeClass($row->service_type) }}">
                                        {{ $row->service_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-lg text-gray-900 dark:text-white">
                                    @php
                                        // Find fee from service types
                                        $serviceFee = $services->firstWhere('name', $row->service_type)?->fee ?? 0;
                                    @endphp
                                    ₱{{ number_format($serviceFee, 2) }}
                                </td>
                                 @if(Auth::user()->role !== 'Priest')
                                <td class="px-6 py-4 text-center">
                                    @if($row->payment_status === 'Paid')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                            <i class="fas fa-check-circle"></i> Paid
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                            <i class="fas fa-clock"></i> Unpaid
                                        </span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($row->payment_status === 'Paid' && $row->payment)
                                            <button disabled
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed"
                                                title="Already Paid">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                            <a href="{{ route('payments.receipt', $row->payment->id) }}" target="_blank"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-500/30 transition-all"
                                                title="Print Receipt">
                                                <i class="fas fa-print text-xs"></i>
                                            </a>
                                        @elseif($row->payment_status === 'Paid' && !$row->payment)
                                            <button disabled
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed"
                                                title="Already Paid">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                            <button disabled
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed"
                                                title="Receipt (unavailable)">
                                                <i class="fas fa-print text-xs"></i>
                                            </button>
                                        @else
                                            <button
                                                data-row="{{ $row }}"
                                                onclick="handleProcessClick(this, {{ $serviceFee }})"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-600 hover:bg-green-700 text-white shadow-lg shadow-green-500/30 transition-all"
                                                title="Process Payment">
                                                <i class="fas fa-money-bill-wave text-xs"></i>
                                            </button>
                                            <button disabled
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed"
                                                title="Receipt (unavailable)">
                                                <i class="fas fa-print text-xs"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @else
                                <!-- DONATIONS TABLE ROW -->
                                <td class="px-6 py-4 text-base font-bold text-gray-900 dark:text-white">
                                    {{ date('F d, Y', strtotime($row->date_received)) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 font-bold text-sm">
                                            {{ strtoupper(substr($row->donor_name, 0, 1)) }}
                                        </div>
                                        <span
                                            class="text-base font-bold text-gray-900 dark:text-white">{{ $row->donor_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                    $donConfig = match($row->type) {
                                        'General Donation', 'Donation' => ['label' => 'General Donation', 'icon' => 'fa-heart'],
                                        'Tithes'                       => ['label' => 'Tithes',           'icon' => 'fa-hand-holding-usd'],
                                        'Others', 'Other'              => ['label' => 'Others',           'icon' => 'fa-circle-plus'],
                                        default                        => ['label' => $row->type,          'icon' => 'fa-star'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-bold {{ ServiceHelper::getDonationBadgeClass($row->type) }}">
                                    <i class="fas {{ $donConfig['icon'] }} text-[10px]"></i>
                                    {{ $donConfig['label'] }}
                                </span>
                                </td>
                                <td class="px-6 py-4 text-right text-lg font-bold text-gray-900 dark:text-white">₱
                                    {{ number_format($row->amount, 2) }}
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">No
                                {{ $isFeePage ?? false ? 'service fee' : 'donation' }} records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    <div class="mt-4 px-4 pb-4">
        {{ $donations->links() }}
    </div>
    </div>
    </div>

    <!-- Modal -->
    @if(!($isFeePage ?? false))
        <div id="addModal" class="hidden fixed inset-0 z-50 overflow-y-auto"
            style="background-color: rgba(0,0,0,0.5);">
            <div class="relative min-h-screen flex items-center justify-center p-4 backdrop-blur-sm">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md shadow-2xl p-6 border border-gray-100 dark:border-gray-700 relative animate-fade-in-up">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-xl text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-hand-holding-heart text-primary"></i> {{ $btnLabel }}
                    </h3>
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('donations.store') }}" method="POST"
                    onsubmit="return confirmDonationSubmit(event, this)">
                    @csrf
                    <div class="space-y-4">
                        <div x-data="{ isAnonymous: false, donorName: '' }">
                            <div class="flex items-center justify-between mb-2">
                                <label
                                    class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $payerLabel }}</label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="isAnonymous" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Anonymous</span>
                                </label>
                            </div>
                            <input type="text" name="donor_name"
                                x-bind:readonly="isAnonymous"
                                x-bind:value="isAnonymous ? 'Anonymous' : donorName"
                                @input="donorName = $event.target.value"
                                x-bind:class="isAnonymous ? 'opacity-70 cursor-not-allowed' : ''"
                                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-primary transition-all"
                                required placeholder="e.g. Juan De La Cruz">
                        </div>
                        <div
                            x-data="{ type: '{{ $isFeePage ?? false ? 'Service Fee' : 'Donation' }}', selectedService: '', services: {{ $services->toJson() }} }">
                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Type</label>
                                <div class="relative group mb-4">
                                    <select name="type" x-model="type" {{ $isFeePage ?? false ? 'readonly' : '' }}
                                        class="dropdown-btn w-full">
                                        @if($isFeePage ?? false)
                                            <option value="Service Fee" selected>Service Fee</option>
                                        @else
                                            <option value="General Donation">General Donation</option>
                                            <option value="Tithes">Tithes</option>
                                            <option value="Love Offering">Love Offering</option>
                                            <option value="Others">Others</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <!-- Service Type Dropdown (Only for Service Fee) -->
                            <div x-show="type === 'Service Fee'" style="display: none;" class="mb-4">
                                <label
                                    class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Service
                                    Type</label>
                                <div class="relative group">
                                    <select name="remarks" x-model="selectedService"
                                        @change="
                                                                                                                                            const service = services.find(s => s.name === selectedService);
                                                                                                                                            if(service) {
                                                                                                                                                document.getElementById('amountInput').value = service.fee;
                                                                                                                                            }
                                                                                                                                        "
                                        class="dropdown-btn w-full">
                                        <option value="">Select Service...</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->name }}">{{ $service->name }}
                                                (₱{{ number_format($service->fee, 2) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Amount
                                    (₱)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3.5 text-gray-400">₱</span>
                                    <input type="number" step="0.01" name="amount" id="amountInput"
                                        class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-8 pr-4 py-3 text-sm font-bold text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-primary transition-all"
                                        required placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Date</label>
                            <input type="date" name="date_received"
                                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-primary transition-all"
                                required value="{{ date('Y-m-d') }}">
                        </div>
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-primary to-blue-600 hover:from-blue-700 hover:to-primary text-white font-bold py-3.5 rounded-xl mt-2 shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                            Save Donation
                        </button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    @endif

    @include('partials.details_modal')
    @include('partials.payment_modal')

    <script>
        function confirmDonationSubmit(event, form) {
            event.preventDefault();
            showConfirm(
                'Save Donation?',
                'Please verify that the donor and amount are correct.',
                'bg-blue-600 hover:bg-blue-700',
                () => { form.submit(); },
                'Save'
            );
            return false;
        }

        function handleProcessClick(btn, fee) {
            try {
                const data = JSON.parse(btn.dataset.row);
                openPaymentModal(data, fee);
            } catch (e) {
                console.error("Error in handleProcessClick:", e);
            }
        }
    </script>
@endsection