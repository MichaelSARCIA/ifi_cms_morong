@extends('layouts.app')

@section('title', 'Reports')
@section('page_title', 'Reports')
@section('page_subtitle', 'Generate specific module reports')
@section('role_label', 'Admin')

@section('content')
    <div class="flex flex-col gap-6 max-w-7xl mx-auto">
        
        <!-- Top Bar / Filters -->
        <div class="w-full no-print">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-sliders-h text-primary"></i> 
                    <h3 class="font-bold text-lg text-gray-800 dark:text-white">Report Settings</h3>
                </div>
                
                <form id="report-form" action="{{ route('reports') }}" method="GET" class="flex flex-col xl:flex-row gap-4 items-end">
                    <div class="w-full xl:w-auto flex-1">
                        <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-2">Category</label>
                        <select name="category" class="dropdown-btn w-full">
                            @foreach($availableCategories as $key => $label)
                                <option value="{{ $key }}" {{ $category == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="service-type-container" class="w-full xl:w-auto flex-1" style="display: {{ in_array($category, ['applicants', 'services', 'fees']) ? 'block' : 'none' }};">
                        <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-2">Service Type</label>
                        <select name="service_type" class="dropdown-btn w-full">
                            <option value="all">All Services</option>
                            @foreach($serviceTypes ?? [] as $type)
                                <option value="{{ $type }}" {{ request('service_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="w-full xl:w-auto flex-1">
                        <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-2">From Date</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm font-medium transition-colors">
                    </div>
                    
                    <div class="w-full xl:w-auto flex-1">
                        <label class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block mb-2">To Date</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm font-medium transition-colors">
                    </div>
                    
                    <div class="w-full xl:w-auto flex flex-col sm:flex-row gap-2 xl:gap-4 shrink-0 mt-4 xl:mt-0">

                        
                        <a id="export-pdf-btn" href="{{ route('reports.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'category' => $category, 'service_type' => request('service_type')]) }}" class="w-full sm:w-auto px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold transition-colors shadow-md flex items-center justify-center gap-2">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Main Content Area / Document Preview -->
        <div id="preview-container" class="w-full flex-1 preview-print overflow-visible h-auto mb-10">
            <div class="bg-white text-black p-10 md:p-16 shadow-2xl shadow-gray-200/40 rounded-xl mx-auto border border-gray-100 dark:border-gray-700 transition-all duration-300" style="max-width: {{ $category === 'applicants' ? '1056px' : '816px' }}; min-height: {{ $category === 'applicants' ? '816px' : '1056px' }}; font-family: 'Times New Roman', Times, serif;">
                
                <!-- Document Header -->
                <div class="relative mb-8 border-b-2 border-gray-800 pb-5 text-center">
                    @if(isset($logo) && $logo)
                        <img src="{{ asset('uploads/' . $logo) }}" alt="Logo" class="absolute left-8 top-1 w-20 h-auto object-contain">
                    @else
                        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="absolute left-8 top-1 w-20 h-auto object-contain">
                    @endif
                    <div class="mx-auto max-w-lg mt-2">
                        <h1 class="text-2xl font-bold uppercase text-black" style="font-family: 'Georgia', serif;">{{ $churchName }}</h1>
                        <p class="text-sm font-bold text-gray-800 tracking-wide mb-1">{{ $dioceseName ?? 'Diocese of Rizal and Pampanga' }}</p>
                    @if($parishName)
                        <h2 class="text-base font-bold text-gray-800 mt-1">{{ $parishName }}</h2>
                    @endif
                    @if($address)
                        <p class="text-sm text-gray-800 mt-1">{{ $address }}</p>
                    @endif
                        <p class="text-sm text-gray-600 mt-1">
                            @if($contact)Contact: {{ $contact }}@endif
                            @if($contact && isset($parishEmail) && $parishEmail) | @endif
                            @if(isset($parishEmail) && $parishEmail)Email: {{ $parishEmail }}@endif
                        </p>
                    </div>
                </div>

                <!-- Document Title -->
                @php
                    $categoryNames = [
                        'services' => 'Services Availed Report',
                        'applicants' => 'List of Recipients',
                        'collections' => 'Collections & Mass Offerings Report',
                        'donations' => 'Donations & Tithes Report',
                        'fees' => 'Service Fees Processed Report'
                    ];
                    $reportTitle = $categoryNames[$category] ?? 'System Generated Report';
                    $serviceType = request('service_type');
                    if ($serviceType && $serviceType !== 'all') {
                        $reportTitle .= ' (' . $serviceType . ')';
                    }
                @endphp

                <div class="text-center mb-10">
                    <h2 class="text-xl font-bold uppercase underline">{{ $reportTitle }}</h2>
                    <p class="text-base mt-2 font-bold">Period: {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</p>
                </div>

                <!-- Render lists based on category -->
                
                @if($category === 'applicants')
                    <!-- 0. List of Recipients -->
                    <div class="mb-8 pl-4">
                        @if(isset($applicantList) && count($applicantList) > 0)
                            <table class="w-full text-[11px] text-left" style="border-collapse: collapse;">
                                <thead>
                                    <tr class="border-b-2 border-gray-800 text-gray-900 font-bold">
                                        <th class="py-2 px-2 text-center" style="width: 4%;">No.</th>
                                        <th class="py-2 px-2" style="width: 18%;">Name of Recipient</th>
                                        <th class="py-2 px-2" style="width: 15%;">Date of Birth & Age</th>
                                        <th class="py-2 px-2" style="width: 15%;">Place of Birth</th>
                                        <th class="py-2 px-2" style="width: 22%;">Parents' Names</th>
                                        <th class="py-2 px-2" style="width: 16%;">Address</th>
                                        <th class="py-2 px-2" style="width: 10%;">Service / Sacrament</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applicantList as $applicant)
                                    <tr class="border-b border-gray-300 align-top hover:bg-gray-50">
                                        <td class="py-3 px-2 text-center font-bold">{{ $loop->iteration }}.</td>
                                        <td class="py-3 px-2 font-bold">{{ $applicant->recipient_name_formal }}</td>
                                        <td class="py-3 px-2 text-gray-800">{{ $applicant->recipient_dob_age }}</td>
                                        <td class="py-3 px-2 text-gray-800">{{ $applicant->recipient_pob }}</td>
                                        <td class="py-3 px-2 text-gray-800 whitespace-pre-line">{{ $applicant->recipient_parents }}</td>
                                        <td class="py-3 px-2 text-gray-800">{{ $applicant->recipient_address }}</td>
                                        <td class="py-3 px-2 text-gray-800 font-medium">{{ $applicant->service_type }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="italic text-center text-gray-500">No recipients found in this period.</p>
                        @endif
                    </div>
                @endif

                @if($category === 'services')
                    <!-- 1. Services Availed -->
                    <div class="mb-8 pl-4">
                        @if(count($serviceRequestsList) > 0)
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="border-b border-gray-300">
                                        <th class="py-2 pr-2" style="width: 5%;">No.</th>
                                        <th class="py-2 px-2" style="width: 20%;">Date</th>
                                        <th class="py-2 px-2" style="width: 25%;">Service Type</th>
                                        <th class="py-2 px-2" style="width: 25%;">Requested By</th>
                                        <th class="py-2 px-2" style="width: 25%;">Subject / Beneficiary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceRequestsList as $req)
                                    <tr class="border-b border-gray-200">
                                        <td class="py-3 pr-2 font-bold text-center">{{ $loop->iteration }}.</td>
                                        <td class="py-3 px-2">{{ \Carbon\Carbon::parse($req->request_date)->format('F d, Y') }}</td>
                                        <td class="py-3 px-2 font-medium">{{ $req->service_type }}</td>
                                        <td class="py-3 px-2 font-bold text-gray-900">{{ $req->applicant_name }}</td>
                                        <td class="py-3 px-2 font-bold text-gray-800">{{ $req->subject_name }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="italic text-center text-gray-500">No services requested in this period.</p>
                        @endif
                    </div>
                @endif

                @if($category === 'collections')
                    <!-- 2. Collections -->
                    <div class="mb-8 pl-4">
                        @if(count($collections) > 0)
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="border-b border-gray-300">
                                        <th class="py-2 pr-2 w-8">No.</th>
                                        <th class="py-2 px-2">Date</th>
                                        <th class="py-2 px-2">Type</th>
                                        <th class="py-2 px-2">Source/Event</th>
                                        <th class="py-2 px-2 text-right">Amount (PHP)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($collections as $col)
                                    <tr class="border-b border-gray-200">
                                        <td class="py-3 pr-2 font-bold">{{ $loop->iteration }}.</td>
                                        <td class="py-3 px-2">{{ \Carbon\Carbon::parse($col->date_received)->format('F d, Y') }}</td>
                                        <td class="py-3 px-2"><span style="color: #666; font-weight: 500;">{{ $col->type }}</span></td>
                                        <td class="py-3 px-2 font-bold">
                                            {{ $col->remarks }}
                                            @if(isset($col->notes) && $col->notes)
                                                <br><span class="text-xs text-gray-500 font-normal italic">{{ $col->notes }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-right">PHP {{ number_format($col->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="italic text-center text-gray-500">No collections recorded in this period.</p>
                        @endif
                    </div>
                @endif

                @if($category === 'donations')
                    <!-- 3. Donations -->
                    <div class="mb-8 pl-4">
                        @if(count($donations) > 0)
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="border-b border-gray-300">
                                        <th class="py-2 pr-2 w-8">No.</th>
                                        <th class="py-2 px-2">Date</th>
                                        <th class="py-2 px-2">Donor Name</th>
                                        <th class="py-2 px-2">Fund / Purpose</th>
                                        <th class="py-2 px-2">Mode of Payment</th>
                                        <th class="py-2 px-2 text-right">Amount (PHP)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($donations as $don)
                                    <tr class="border-b border-gray-200">
                                        <td class="py-3 pr-2 font-bold">{{ $loop->iteration }}.</td>
                                        <td class="py-3 px-2">{{ \Carbon\Carbon::parse($don->date_received)->format('F d, Y') }}</td>
                                        <td class="py-3 px-2 font-bold">{{ $don->donor_name ?? 'Anonymous' }}</td>
                                        <td class="py-3 px-2">{{ $don->type ?? '—' }}</td>
                                        <td class="py-3 px-2">{{ $don->payment_method ?? '—' }}</td>
                                        <td class="py-3 px-2 text-right">PHP {{ number_format($don->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="italic text-center text-gray-500">No donations received in this period.</p>
                        @endif
                    </div>
                @endif

                @if($category === 'fees')
                    <!-- 4. Service Fees -->
                    <div class="mb-8 pl-4">
                        @if(count($serviceFees) > 0)
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="border-b border-gray-300">
                                        <th class="py-2 pr-2 w-8">No.</th>
                                        <th class="py-2 px-2">Date Paid</th>
                                        <th class="py-2 px-2">Service Type</th>
                                        <th class="py-2 px-2">Payor</th>
                                        <th class="py-2 px-2 text-right">Amount (PHP)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceFees as $fee)
                                    <tr class="border-b border-gray-200">
                                        <td class="py-3 pr-2 font-bold">{{ $loop->iteration }}.</td>
                                        <td class="py-3 px-2">{{ \Carbon\Carbon::parse($fee->paid_at)->format('F d, Y') }}</td>
                                        <td class="py-3 px-2">{{ $fee->service_type }}</td>
                                        <td class="py-3 px-2 font-bold">{{ $fee->payor_name ?? 'N/A' }}</td>
                                        <td class="py-3 px-2 text-right">PHP {{ number_format($fee->amount_paid, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="italic text-center text-gray-500">No service fees processed in this period.</p>
                        @endif
                    </div>
                @endif
                
                <div class="mt-16 text-right">
                    <p class="text-sm">Printed By: <strong>{{ auth()->user()->name ?? 'System Admin' }}</strong></p>
                    <p class="text-sm">Date Printed: {{ now()->format('F d, Y h:i A') }}</p>
                </div>
                
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('report-form');
            let timeoutId = null;

            form.addEventListener('change', function(e) {
                if(e.target.name === 'category') {
                    const container = document.getElementById('service-type-container');
                    if(container) {
                        const typesWithDropdown = ['applicants', 'services', 'fees'];
                        container.style.display = typesWithDropdown.includes(e.target.value) ? 'block' : 'none';
                    }
                }
                
                clearTimeout(timeoutId);
                timeoutId = setTimeout(updateReport, 300); // debounce slightly
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                updateReport();
            });

            function updateReport() {
                const url = new URL(form.action);
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                
                const typesWithDropdown = ['applicants', 'services', 'fees'];
                if (!typesWithDropdown.includes(formData.get('category'))) {
                    params.delete('service_type');
                }

                url.search = params.toString();
                window.history.pushState({}, '', url);

                fetch(url)
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        const newPreview = doc.getElementById('preview-container');
                        if (newPreview) {
                            document.getElementById('preview-container').innerHTML = newPreview.innerHTML;
                        }
                        
                        const newExportBtn = doc.getElementById('export-pdf-btn');
                        if (newExportBtn) {
                            document.getElementById('export-pdf-btn').href = newExportBtn.href;
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching preview:', err);
                    });
            }
        });
    </script>
@endsection