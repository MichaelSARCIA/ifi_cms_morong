@extends('layouts.app')

@section('title', 'System Settings')
@section('page_title', 'System Configuration')
@section('page_subtitle', 'Manage church identity, services, and database')
@section('role_label', Auth::user()->role)

@section('content')
    <style>
        @keyframes field-flash {
            0% { background-color: rgba(59, 130, 246, 0.15); box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.4); }
            100% { background-color: transparent; box-shadow: none; }
        }
        .animate-field-highlight {
            animation: field-flash 1.5s ease-out forwards;
        }
    </style>
    <div x-data="serviceSettings"
        class="flex-1 flex flex-col h-full overflow-hidden">

         <!-- TABS -->
         <div class="w-full -mt-2 mb-5 border-b border-gray-200 dark:border-gray-800 px-1 overflow-x-auto scrollbar-hide">
             <nav class="flex space-x-8 min-w-max pb-0.5" aria-label="Tabs">
                 @if(Auth::user()->hasModule('system_settings_general'))
                 <button @click="activeTab = 'general'"
                     :class="activeTab === 'general' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-700'"
                     class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2.5 outline-none">
                     <i class="fas fa-church"></i> General Settings
                 </button>
                 @endif
                 @if(Auth::user()->hasModule('system_settings_priests'))
                 <button @click="activeTab = 'priests'"
                     :class="activeTab === 'priests' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-700'"
                     class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2.5 outline-none">
                     <i class="fas fa-calendar-check"></i> Priest Schedules
                 </button>
                 @endif
                 @if(Auth::user()->hasModule('system_settings_services'))
                 <button @click="activeTab = 'services'"
                     :class="activeTab === 'services' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-700'"
                     class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2.5 outline-none">
                     <i class="fas fa-scroll"></i> Services & Requirements
                 </button>
                 @endif
                 @if(Auth::user()->hasModule('system_settings_payment_methods'))
                 <button @click="activeTab = 'payment_methods'"
                     :class="activeTab === 'payment_methods' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-700'"
                     class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2.5 outline-none">
                     <i class="fas fa-credit-card"></i> Payment Methods
                 </button>
                 @endif
                 @if(Auth::user()->hasModule('system_settings_database'))
                 <button @click="activeTab = 'database'"
                     :class="activeTab === 'database' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-700'"
                     class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2.5 outline-none">
                     <i class="fas fa-database"></i> Backup &amp; Database
                 </button>
                 @endif
             </nav>
         </div>

        <!-- CONTENT AREA -->
        <div class="flex-1 overflow-y-auto pr-2">

            <!-- GENERAL CONFIGURATION -->
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="max-w-6xl mx-auto space-y-12">

                <form action="{{ route('system-settings.update-general') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-12 pb-10">
                    @csrf

                    <!-- Branding Section -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-12">
                        <div class="md:col-span-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Branding & Media</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Update your church's
                                official logo and customize the login background image shown to the users.</p>
                        </div>
                        <div
                            class="md:col-span-8 bg-white dark:bg-gray-800 p-6 md:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
                            <div class="flex flex-col sm:flex-row gap-8">
                                <!-- Main Logo -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Main
                                        Church Logo</label>
                                    <div
                                        class="w-32 h-32 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-center relative overflow-hidden group hover:border-primary/50 transition-colors">
                                        @if(isset($settings['church_logo']) && !empty($settings['church_logo']))
                                            <img src="{{ asset('uploads/' . $settings['church_logo']) }}"
                                                class="w-full h-full object-contain p-2" id="logoPreview">
                                        @else
                                            <img src="{{ asset('assets/img/logo.png') }}"
                                                class="w-full h-full object-contain p-2" id="logoPreview">
                                        @endif
                                        <div class="absolute inset-0 bg-gray-900/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer backdrop-blur-sm"
                                            onclick="document.getElementById('logoInput').click()">
                                            <i class="fas fa-camera text-white text-xl shadow-lg drop-shadow-lg"></i>
                                        </div>
                                    </div>
                                    <input type="file" name="logo" id="logoInput" class="hidden" accept="image/*">
                                </div>

                                <!-- Login Background -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Login
                                        Background</label>
                                    <div
                                        class="w-48 h-32 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-center relative overflow-hidden group hover:border-primary/50 transition-colors">
                                        @if(isset($settings['login_background']) && !empty($settings['login_background']))
                                            <img src="{{ asset('uploads/' . $settings['login_background']) }}"
                                                class="w-full h-full object-cover" id="loginBgPreview">
                                        @else
                                            <img src="{{ asset('assets/img/login-bg.jpg') }}"
                                                class="w-full h-full object-cover" id="loginBgPreview">
                                        @endif
                                        <div class="absolute inset-0 bg-gray-900/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer backdrop-blur-sm"
                                            onclick="document.getElementById('loginBgInput').click()">
                                            <i class="fas fa-camera text-white text-xl shadow-lg drop-shadow-lg"></i>
                                        </div>
                                    </div>
                                    <input type="file" name="login_background" id="loginBgInput" class="hidden"
                                        accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800/60">

                    <!-- System Information -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-12">
                        <div class="md:col-span-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">System Details</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Configure the main
                                headings and tags used throughout the dashboard and printed reports.</p>
                        </div>
                        <div
                            class="md:col-span-8 bg-white dark:bg-gray-800 p-6 md:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">System
                                    Title / Main Heading</label>
                                <input type="text" name="system_name"
                                    value="{{ $settings['system_name'] ?? 'Iglesia Filipina Independiente' }}"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white dark:focus:bg-gray-800 transition-all font-medium text-gray-900 dark:text-white">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Short
                                        Name (For Tags)</label>
                                    <input type="text" name="system_short_name"
                                        value="{{ $settings['system_short_name'] ?? 'IFI CMS' }}"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white dark:focus:bg-gray-800 transition-all font-medium text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Login
                                        Form Tagline</label>
                                    <input type="text" name="system_tagline"
                                        value="{{ $settings['system_tagline'] ?? 'Church Management System' }}"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white dark:focus:bg-gray-800 transition-all font-medium text-gray-900 dark:text-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800/60">

                    <!-- Parish Details -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-12">
                        <div class="md:col-span-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Parish Details</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Ensure your official
                                registry data is correct. This information is displayed in generated certificates and
                                official documents.</p>
                        </div>
                        <div
                            class="md:col-span-8 bg-white dark:bg-gray-800 p-6 md:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Official
                                        Church Name</label>
                                    <input type="text" name="church_name" value="{{ $settings['church_name'] ?? '' }}"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white dark:focus:bg-gray-800 transition-all font-medium text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Parish
                                        Subtitle</label>
                                    <input type="text" name="parish_name"
                                        value="{{ $settings['parish_name'] ?? 'Parokya ng San Geronimo' }}"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white dark:focus:bg-gray-800 transition-all font-medium text-gray-900 dark:text-white">
                                </div>
                                <div class="col-span-1 md:col-span-2">
                                    <label
                                        class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Complete
                                        Address</label>
                                    <input type="text" name="church_address" value="{{ $settings['church_address'] ?? '' }}"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white dark:focus:bg-gray-800 transition-all font-medium text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Contact
                                        Number</label>
                                    <input type="text" name="church_contact" value="{{ $settings['church_contact'] ?? '' }}"
                                        placeholder="e.g. +63 912 345 6789"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white dark:focus:bg-gray-800 transition-all font-medium text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Parish
                                        Email</label>
                                    <input type="email" name="parish_email" value="{{ $settings['parish_email'] ?? '' }}"
                                        placeholder="e.g. contact@parish.com"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white dark:focus:bg-gray-800 transition-all font-medium text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Current
                                        Parish Priest</label>
                                    <input type="text" name="church_priest" value="{{ $settings['church_priest'] ?? '' }}"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white dark:focus:bg-gray-800 transition-all font-medium text-gray-900 dark:text-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Action Panel -->
                    <div
                        class="sticky bottom-4 z-10 flex justify-end p-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border border-gray-200 dark:border-gray-800 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] ml-auto w-max max-w-full mr-4">
                        <button type="submit"
                            class="bg-primary hover:bg-blue-600 text-white px-8 py-3 rounded-xl font-bold tracking-wide shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                            <i class="fas fa-save"></i> Save All Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- SERVICE MANAGEMENT -->
            <div x-show="activeTab === 'services'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="max-w-6xl mx-auto space-y-6">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white">Service & Sacrament Modules</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage all available services, their fees,
                            requirements, and custom request forms.</p>
                    </div>
                    <button @click="openServiceModal(false, {})"
                        class="bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-600 transition-all flex items-center gap-2 shrink-0">
                        <i class="fas fa-plus"></i> Add New Service
                    </button>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead
                            class="bg-gray-50/80 dark:bg-gray-900/80 border-b border-gray-100 dark:border-gray-800 text-xs font-bold text-gray-500 uppercase tracking-wider backdrop-blur-sm">
                            <tr>
                                <th class="px-6 py-4">Service Details</th>
                                <th class="px-6 py-4">Standard Fee</th>
                                <th class="px-6 py-4">Requirements</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($services as $s)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm border border-black/10"
                                                style="background-color: {{ $s->color ?? '#6366f1' }}; color: #fff;">
                                                <i class="fas {{ $s->icon ?? 'fa-church' }} text-lg"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-white text-base">{{ $s->name }}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-1.5">
                                                    <span class="inline-block w-3 h-3 rounded-full border border-black/10" style="background-color: {{ $s->color ?? '#6366f1' }}"></span>
                                                    {{ $s->color ?? '#6366f1' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="font-mono text-gray-900 dark:text-white font-bold text-sm bg-gray-50 dark:bg-gray-900 px-3 py-1.5 rounded-lg border border-gray-100 dark:border-gray-700 inline-block shadow-inner">
                                            ₱{{ number_format($s->fee, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            @php
                                                $reqs = is_array($s->requirements) ? $s->requirements : explode(',', $s->requirements ?? '');
                                                $reqs = array_filter(array_map('trim', $reqs));
                                            @endphp
                                            @if(empty($reqs))
                                                <span class="text-sm text-gray-400 italic">No specific requirements</span>
                                            @else
                                                @foreach(array_slice($reqs, 0, 3) as $req)
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 bg-gray-50 dark:bg-gray-700 font-medium text-xs text-gray-600 dark:text-gray-300 rounded-md border border-gray-200 dark:border-gray-600">{{ $req }}</span>
                                                @endforeach
                                                @if(count($reqs) > 3)
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 bg-blue-50 dark:bg-blue-900/30 font-bold text-xs text-primary rounded-md border border-blue-100 dark:border-blue-800"
                                                        title="{{ implode(', ', array_slice($reqs, 3)) }}">
                                                        +{{ count($reqs) - 3 }} more
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                @click="openServiceModal(true, { id: {{ $s->id }}, name: '{{ addslashes($s->name) }}', fee: {{ $s->fee }}, requirements: {{ json_encode($s->requirements ?? []) }}, custom_fields: {{ json_encode($s->custom_fields ?? []) }}, icon: '{{ $s->icon ?? 'fa-church' }}', color: '{{ $s->color ?? 'blue' }}', payment_methods: {{ json_encode($s->payment_methods ?? []) }} })"
                                                class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center justify-center transition-all tooltip"
                                                title="Edit Service">
                                                <i class="fas fa-pen text-xs"></i>
                                            </button>
                                            <form action="{{ route('system-settings.destroy-service', $s->id) }}" method="POST"
                                                class="inline-block"
                                                onsubmit="event.preventDefault(); showConfirm('Archive Service', 'Are you sure you want to archive this service type? Current requests linked to this service might be affected.', 'bg-amber-600 hover:bg-amber-700', () => this.submit(), 'Archive')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-lg bg-amber-600 hover:bg-amber-700 text-white shadow-sm flex items-center justify-center transition-all tooltip"
                                                    title="Archive Service">
                                                    <i class="fas fa-box-archive text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div
                                            class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200 dark:border-gray-700">
                                            <i class="fas fa-clipboard-list text-2xl text-gray-400"></i>
                                        </div>
                                        <h4 class="text-base font-bold text-gray-900 dark:text-white">No services configured
                                        </h4>
                                        <p class="text-sm text-gray-500 mt-1 mb-4">Start by adding your first church service or
                                            sacrament.</p>
                                        <button @click="openServiceModal(false, {})"
                                            class="text-primary font-bold hover:underline text-sm">Add New Service</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($archivedServices->count() > 0)
                    <div class="mt-12 pt-8 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 flex items-center justify-center">
                                <i class="fas fa-box-archive text-sm"></i>
                            </div>
                            <h4 class="font-bold text-gray-900 dark:text-white">Archived Services</h4>
                            <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-500 text-[10px] font-bold rounded-md uppercase tracking-tighter">{{ $archivedServices->count() }} Hidden</span>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-6 py-3">Archived Service</th>
                                        <th class="px-6 py-3">Original Fee</th>
                                        <th class="px-6 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @foreach($archivedServices as $as)
                                        <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-700/20 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-10 h-10 rounded-2xl bg-gray-50 dark:bg-gray-900/50 text-gray-400 flex items-center justify-center border border-gray-100 dark:border-gray-700 shrink-0">
                                                        <i class="fas {{ $as->icon ?? 'fa-church' }} text-lg"></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-gray-900 dark:text-white">{{ $as->name }}</div>
                                                        <div class="text-xs text-gray-400">Archived on {{ $as->deleted_at->format('M d, Y') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="font-mono text-gray-900 dark:text-white font-bold text-sm bg-gray-50 dark:bg-gray-900 px-3 py-1.5 rounded-lg border border-gray-100 dark:border-gray-700 inline-block">
                                                    ₱{{ number_format($as->fee, 2) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form action="{{ route('system-settings.restore-service', $as->id) }}" method="POST" class="inline-block"
                                                        onsubmit="event.preventDefault(); showConfirm('Restore Service', 'Are you sure you want to restore this service? It will become active and visible again.', 'bg-green-600 hover:bg-green-700', () => this.submit(), 'Restore')">
                                                        @csrf
                                                        <button type="submit" 
                                                            class="w-8 h-8 rounded-lg bg-green-600 hover:bg-green-700 text-white shadow-sm flex items-center justify-center transition-all tooltip"
                                                            title="Restore Service">
                                                            <i class="fas fa-rotate-left text-xs"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('system-settings.force-delete-service', $as->id) }}" method="POST" class="inline-block"
                                                        onsubmit="event.preventDefault(); showConfirm('Permanently Delete Service', 'Are you sure? This service will be GONE FOREVER and cannot be restored.', 'bg-red-600 hover:bg-red-700', () => this.submit(), 'Delete Permanently')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" 
                                                            class="w-8 h-8 rounded-lg bg-red-600 hover:bg-red-700 text-white shadow-sm flex items-center justify-center transition-all tooltip"
                                                            title="Delete Permanently">
                                                            <i class="fas fa-trash-alt text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- DATABASE MANAGEMENT -->
            <div x-show="activeTab === 'database'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="max-w-6xl mx-auto space-y-8">

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white">Data Management</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Safeguard your church records by creating
                            backups or configuring automated schedules.</p>
                    </div>

                    <a href="{{ route('system-settings.backup') }}"
                        class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-blue-500/30">
                        <i class="fas fa-download"></i> Generate New Backup
                    </a>
                </div>

                <!-- Backups Table -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white">Existing Archives</h3>

                        <!-- Invisible Restore Form (triggered by action button) -->
                        <form action="{{ route('system-settings.restore') }}" method="POST" enctype="multipart/form-data"
                            class="hidden" id="hiddenRestoreForm" @submit="confirmRestore">
                            @csrf
                            <input type="file" name="backup_file" id="hiddenBackupFile" accept=".sql"
                                onchange="document.getElementById('hiddenRestoreForm').submit()">
                        </form>
                        <button type="button" onclick="document.getElementById('hiddenBackupFile').click()"
                            class="text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 px-3 py-1.5 rounded-lg transition-colors border border-red-200 dark:border-red-800/50">
                            <i class="fas fa-upload mr-1"></i> Upload External Backup
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                                    <th
                                        class="px-6 py-4 text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        File Name</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Date Generated</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Size</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($backups as $backup)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-8 h-8 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500">
                                                    <i class="fas fa-file-code"></i>
                                                </div>
                                                <span
                                                    class="font-semibold text-gray-900 dark:text-white text-sm">{{ $backup['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ \Carbon\Carbon::parse($backup['last_modified'])->format('F d, Y h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $backup['size'] }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div
                                                class="flex items-center justify-end gap-2">
                                                <a href="{{ route('system-settings.backup.download', $backup['name']) }}"
                                                    class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-500 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 flex items-center justify-center tooltip transition-all"
                                                    title="Download">
                                                    <i class="fas fa-download text-xs"></i>
                                                </a>
                                                <a href="{{ route('system-settings.backup.delete', $backup['name']) }}"
                                                    onclick="event.preventDefault(); window.showConfirm('Delete Backup', 'Caution: Are you sure you want to permanently delete this database backup? This action cannot be undone.', 'bg-red-600 hover:bg-red-700', () => { window.location.href = '{{ route('system-settings.backup.delete', $backup['name']) }}'; }, 'Delete')"
                                                    class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-500 hover:text-red-600 hover:border-red-300 hover:bg-red-50 flex items-center justify-center tooltip transition-all"
                                                    title="Delete">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div
                                                class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200 dark:border-gray-700">
                                                <i class="fas fa-database text-2xl text-gray-400"></i>
                                            </div>
                                            <h4 class="text-base font-bold text-gray-900 dark:text-white">No backups available
                                            </h4>
                                            <p class="text-sm text-gray-500 mt-1 mb-4">You haven't generated any database
                                                backups yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-800">
                        <p class="text-xs text-gray-500 italic flex items-center gap-1.5">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            Scheduled backups will automatically appear in this list once they are processed by the server.
                        </p>
                    </div>
                </div>

                <!-- Automated Backup Schedule -->
                <div
                    class="col-span-1 md:col-span-2 bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-8 items-center">
                    <div
                        class="w-20 h-20 bg-green-50 dark:bg-green-900/20 rounded-2xl flex flex-shrink-0 items-center justify-center text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800/30">
                        <i class="fas fa-cloud-upload-alt text-3xl"></i>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-1">Automated Cloud Backups</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 md:mb-0 max-w-xl">
                            Never worry about losing data. Configure how often the system securely uploads a backup
                            archive directly to your connected Google Drive account.
                        </p>
                    </div>

                    <form action="{{ route('system-settings.update-general') }}" method="POST"
                        x-data="{ backupFreq: '{{ $settings['backup_schedule'] ?? 'none' }}' }"
                        class="w-full md:w-auto flex-shrink-0 flex flex-col sm:flex-row gap-4 items-center form-validate">
                        @csrf
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-500 uppercase">Frequency</label>
                            <div class="w-full sm:w-48 relative">
                                <select name="backup_schedule" x-model="backupFreq"
                                    class="dropdown-btn w-full">
                                    <option value="none" {{ (isset($settings['backup_schedule']) && $settings['backup_schedule'] == 'none') ? 'selected' : '' }}>Disabled (Manual Only)
                                    </option>
                                    <option value="daily" {{ (isset($settings['backup_schedule']) && $settings['backup_schedule'] == 'daily') ? 'selected' : '' }}>Daily Backup</option>
                                    <option value="weekly" {{ (isset($settings['backup_schedule']) && $settings['backup_schedule'] == 'weekly') ? 'selected' : '' }}>Weekly Backup</option>
                                    <option value="monthly" {{ (isset($settings['backup_schedule']) && $settings['backup_schedule'] == 'monthly') ? 'selected' : '' }}>Monthly Backup
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 relative" x-show="backupFreq === 'weekly'" x-cloak>
                            <label class="text-sm font-bold text-gray-500 uppercase">Day of Week</label>
                            <div class="w-full sm:w-40 relative">
                                <select name="backup_day_of_week" class="dropdown-btn w-full">
                                    <option value="Monday" {{ ($settings['backup_day_of_week'] ?? '') == 'Monday' ? 'selected' : '' }}>Monday</option>
                                    <option value="Tuesday" {{ ($settings['backup_day_of_week'] ?? '') == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="Wednesday" {{ ($settings['backup_day_of_week'] ?? '') == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="Thursday" {{ ($settings['backup_day_of_week'] ?? '') == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                                    <option value="Friday" {{ ($settings['backup_day_of_week'] ?? '') == 'Friday' ? 'selected' : '' }}>Friday</option>
                                    <option value="Saturday" {{ ($settings['backup_day_of_week'] ?? '') == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                                    <option value="Sunday" {{ ($settings['backup_day_of_week'] ?? '') == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 relative" x-show="backupFreq === 'monthly'" x-cloak>
                            <label class="text-sm font-bold text-gray-500 uppercase">Day of Month</label>
                            <div class="w-full sm:w-32 relative">
                                <select name="backup_day_of_month" class="dropdown-btn w-full">
                                    @for($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}" {{ ($settings['backup_day_of_month'] ?? '1') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-500 uppercase">Time</label>
                            <div class="w-full sm:w-36">
                                <input type="time" name="backup_time" value="{{ $settings['backup_time'] ?? '00:00' }}"
                                    :disabled="backupFreq === 'none'"
                                    :class="backupFreq === 'none' ? 'opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-800' : ''"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white dark:focus:bg-gray-800 transition-all font-medium text-gray-700 dark:text-gray-300">
                            </div>
                        </div>
                        <div class="flex flex-col justify-end mt-6">
                            <button type="submit"
                                class="w-full sm:w-auto bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-black dark:hover:bg-gray-100 transition-all">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PRIEST SCHEDULES MANAGEMENT -->
            <div x-show="activeTab === 'priests'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="max-w-6xl mx-auto space-y-8" x-data="priestScheduleManager()" x-init="initPriests({{ Js::from($active_priests) }})">

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white">Priest Availability & Limits</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure working days, time constraints, and maximum daily services for each assigned Priest.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-12">
                    <!-- Left Sidebar: Priest Selection -->
                    <div class="md:col-span-4 space-y-4">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
                            <h4 class="font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wider text-sm">Select a Priest</h4>
                            <div class="space-y-2">
                                <template x-for="p in priests" :key="p.id">
                                    <button @click="selectPriest(p)"
                                        class="w-full text-left px-4 py-3 rounded-xl transition-all flex items-center gap-3 border"
                                        :class="selectedPriest && selectedPriest.id === p.id ? 'bg-primary/10 border-primary text-primary font-bold' : 'bg-gray-50 dark:bg-gray-900/50 border-transparent hover:border-gray-200 dark:hover:border-gray-700 text-gray-700 dark:text-gray-300'">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center shrink-0 overflow-hidden">
                                            <template x-if="p.profile_picture">
                                                <img :src="'/uploads/profiles/' + p.profile_picture" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!p.profile_picture">
                                                <i class="fas fa-user text-gray-400"></i>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="text-sm leading-tight" x-text="p.name"></div>
                                            <div class="text-xs text-gray-500 font-normal" x-text="p.email"></div>
                                        </div>
                                    </button>
                                </template>
                                <div x-show="priests.length === 0" class="text-center py-6 text-gray-500 text-sm italic">
                                    No active priests found in the system.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Settings Form -->
                    <div class="md:col-span-8">
                        <div x-show="!selectedPriest" class="h-full min-h-[400px] flex flex-col items-center justify-center text-gray-400 bg-gray-50/50 dark:bg-gray-900/20 rounded-3xl border border-gray-200 border-dashed dark:border-gray-800">
                            <i class="fas fa-hand-pointer text-4xl mb-4 text-gray-300"></i>
                            <p>Select a priest from the list to view and edit their schedule.</p>
                        </div>

                        <div x-show="selectedPriest" style="display: none;" class="bg-white dark:bg-gray-800 p-6 md:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-8 animate-fade-in-up">
                            
                            <!-- Header -->
                            <div class="flex items-center gap-4 border-b border-gray-100 dark:border-gray-700 pb-6">
                                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-primary text-2xl shrink-0 overflow-hidden shadow-sm">
                                    <template x-if="selectedPriest && selectedPriest.profile_picture">
                                        <img :src="'/uploads/profiles/' + selectedPriest.profile_picture" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!selectedPriest || !selectedPriest.profile_picture">
                                        <i class="fas fa-user-tie"></i>
                                    </template>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="selectedPriest ? selectedPriest.name : ''"></h3>
                                    <p class="text-sm text-gray-500">Manage rules and availability for this assignee.</p>
                                </div>
                            </div>

                            <form @submit.prevent="saveScheduleSettings">
                                
                                <!-- Weekly Working Days -->
                                <div class="mb-8">
                                    <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Availability (Working Days)</label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <template x-for="day in ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']" :key="day">
                                            <label class="flex items-center cursor-pointer group">
                                                <div class="relative flex items-center">
                                                    <input type="checkbox" :value="day" x-model="formData.working_days"
                                                        class="peer sr-only">
                                                    <div class="relative w-full h-12 px-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 group-hover:border-primary/50 peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all flex items-center justify-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                                                        <i class="fas fa-check absolute left-4 opacity-0 peer-checked:opacity-100 transition-opacity text-xs"></i>
                                                        <span x-text="day" class="text-center"></span>
                                                    </div>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2"><i class="fas fa-info-circle mr-1"></i> Deselected days will be disabled in the Date Picker for clients requesting this priest.</p>
                                </div>

                                <!-- Time & Capacity -->
                                <div class="space-y-8 mb-8">
                                    <div class="max-w-2xl">
                                        <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Standard Working Hours</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr] items-center gap-3">
                                            <div class="relative">
                                                <input type="time" x-model="formData.working_hours.start" required
                                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary font-medium text-gray-700 dark:text-white">
                                            </div>
                                            <span class="text-gray-400 font-bold px-2 text-center">to</span>
                                            <div class="relative">
                                                <input type="time" x-model="formData.working_hours.end" required
                                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary font-medium text-gray-700 dark:text-white">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="max-w-md">
                                        <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Max Services Per Day</label>
                                        <div class="relative group">
                                            <input type="number" min="1" max="50" x-model="formData.max_services_per_day" required
                                                class="w-full pl-4 pr-32 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-gray-900 dark:text-white text-lg">
                                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400 text-xs font-bold uppercase tracking-widest bg-gray-50 dark:bg-gray-900 pl-2">
                                                appointments
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-2 leading-tight">Once this limit is reached for a specific date, clients won't be able to select that date for this priest.</p>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <button type="submit" :disabled="isSaving"
                                        class="bg-primary hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white px-8 py-3 rounded-xl font-bold tracking-wide shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                                        <i class="fas fa-spinner fa-spin" x-show="isSaving"></i>
                                        <i class="fas fa-save" x-show="!isSaving"></i>
                                        <span x-text="isSaving ? 'Saving...' : 'Save Priest Schedule'"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAYMENT METHODS MANAGEMENT -->
            <div x-show="activeTab === 'payment_methods'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="max-w-6xl mx-auto space-y-6">

                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white">Mode of Payment</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage accepted payment methods for service fees. Active methods will appear as options in the Process Payment form.</p>
                    </div>
                    <button @click="openPmModal(false, {})"
                        class="bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-600 transition-all flex items-center gap-2 shrink-0">
                        <i class="fas fa-plus"></i> Add Payment Method
                    </button>
                </div>

                <!-- Payment Methods Table -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/80 border-b border-gray-100 dark:border-gray-800 text-xs font-bold text-gray-500 uppercase tracking-wider backdrop-blur-sm">
                            <tr>
                                <th class="px-6 py-4">Payment Method</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Order</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($paymentMethods as $pm)
                                <tr x-data="{ isActive: {{ $pm->is_active ? 'true' : 'false' }}, toggling: false }"
                                    class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-primary flex items-center justify-center border border-blue-100 dark:border-blue-800/30 shrink-0 shadow-sm">
                                                        <i class="fas {{ $pm->icon ?? 'fa-money-bill' }} text-lg"></i>
                                                    </div>
                                                    <div class="font-bold text-gray-900 dark:text-white">{{ $pm->name }}</div>
                                                </div>
                                            </td>
                                    <td class="px-6 py-4">
                                        <span x-show="isActive" style="display: {{ $pm->is_active ? 'inline-flex' : 'none' }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-100 dark:border-green-800/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Active
                                        </span>
                                        <span x-show="!isActive" style="display: {{ $pm->is_active ? 'none' : 'inline-flex' }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span> Inactive
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-mono text-gray-500 font-bold text-sm bg-gray-50 dark:bg-gray-900 px-3 py-1.5 rounded-lg border border-gray-100 dark:border-gray-700 inline-block shadow-inner">
                                            {{ $pm->sort_order }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Toggle Active -->
                                            <button
                                                @click="
                                                    toggling = true;
                                                    fetch('{{ route('system-settings.toggle-payment-method', $pm->id) }}', {
                                                        method: 'POST',
                                                        headers: {
                                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                            'Content-Type': 'application/json'
                                                        }
                                                    }).then(r => { if (r.ok) isActive = !isActive; })
                                                      .finally(() => toggling = false);
                                                "
                                                :disabled="toggling"
                                                :class="isActive ? 'text-green-600 hover:text-orange-500 hover:border-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/30' : 'text-gray-400 hover:text-green-600 hover:border-green-300 hover:bg-green-50 dark:hover:bg-green-900/30'"
                                                class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center transition-all tooltip"
                                                :title="isActive ? 'Deactivate' : 'Activate'">
                                                <i class="fas text-sm" :class="isActive ? 'fa-toggle-on' : 'fa-toggle-off'"></i>
                                            </button>
                                            
                                            <!-- Edit (Plain gray, consistent with services edit button) -->
                                            <button
                                                @click="openPmModal(true, { id: {{ $pm->id }}, name: '{{ addslashes($pm->name) }}', icon: '{{ $pm->icon ?? 'fa-money-bill' }}', sort_order: {{ $pm->sort_order }} })"
                                                class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center justify-center transition-all tooltip"
                                                title="Edit">
                                                <i class="fas fa-pen text-xs"></i>
                                            </button>

                                            <!-- Archive -->
                                            <form action="{{ route('system-settings.destroy-payment-method', $pm->id) }}" method="POST" class="inline-block"
                                                onsubmit="event.preventDefault(); showConfirm('Archive Payment Method', 'Are you sure you want to archive &quot;{{ addslashes($pm->name) }}&quot;? It will be hidden from payment forms.', 'bg-amber-600 hover:bg-amber-700', () => this.submit(), 'Archive')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-lg bg-amber-600 hover:bg-amber-700 text-white shadow-sm flex items-center justify-center transition-all tooltip"
                                                    title="Archive">
                                                    <i class="fas fa-box-archive text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200 dark:border-gray-700">
                                            <i class="fas fa-credit-card text-2xl text-gray-400"></i>
                                        </div>
                                        <h4 class="text-base font-bold text-gray-900 dark:text-white">No payment methods configured</h4>
                                        <p class="text-sm text-gray-500 mt-1 mb-4">Add your first mode of payment to get started.</p>
                                        <button @click="openPmModal(false, {})" class="text-primary font-bold hover:underline text-sm">Add Payment Method</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ARCHIVED PAYMENT METHODS -->
                @if($archivedPaymentMethods->count() > 0)
                    <div class="mt-12 pt-8 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 flex items-center justify-center">
                                <i class="fas fa-box-archive text-sm"></i>
                            </div>
                            <h4 class="font-bold text-gray-900 dark:text-white">Archived Payment Methods</h4>
                            <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-500 text-[10px] font-bold rounded-md uppercase tracking-tighter">{{ $archivedPaymentMethods->count() }} Hidden</span>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-6 py-3 tracking-widest">Archived Method</th>
                                        <th class="px-6 py-3 tracking-widest text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($archivedPaymentMethods as $apm)
                                        <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-700/20 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-10 h-10 rounded-2xl bg-gray-50 dark:bg-gray-900/50 text-gray-400 flex items-center justify-center border border-gray-100 dark:border-gray-700 shrink-0">
                                                        <i class="fas {{ $apm->icon ?? 'fa-money-bill' }} text-lg"></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-gray-900 dark:text-white text-base leading-none mb-1">{{ $apm->name }}</div>
                                                        <div class="text-xs font-medium text-gray-400 dark:text-gray-500">Archived on {{ $apm->deleted_at->format('M d, Y') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form action="{{ route('system-settings.restore-payment-method', $apm->id) }}" method="POST" class="inline-block"
                                                        onsubmit="event.preventDefault(); showConfirm('Restore Payment Method', 'Restore &quot;{{ addslashes($apm->name) }}&quot; to active payment methods?', 'bg-green-600 hover:bg-green-700', () => this.submit(), 'Restore')">
                                                        @csrf
                                                        <button type="submit" 
                                                            class="w-8 h-8 rounded-lg bg-green-600 hover:bg-green-700 text-white shadow-sm flex items-center justify-center transition-all tooltip"
                                                            title="Restore">
                                                            <i class="fas fa-rotate-left text-xs"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('system-settings.force-delete-payment-method', $apm->id) }}" method="POST" class="inline-block"
                                                        onsubmit="event.preventDefault(); showConfirm('Permanently Delete Method', 'Are you sure? This payment method will be GONE FOREVER.', 'bg-red-600 hover:bg-red-700', () => this.submit(), 'Delete Permanently')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" 
                                                            class="w-8 h-8 rounded-lg bg-red-600 hover:bg-red-700 text-white shadow-sm flex items-center justify-center transition-all tooltip"
                                                            title="Delete Permanently">
                                                            <i class="fas fa-trash-alt text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

                <!-- Add/Edit Payment Method Modal -->
                <div x-show="pmModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="pmModalOpen = false"></div>
                    <div class="relative min-h-screen flex items-center justify-center p-4">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-8 relative transform transition-all">
                            <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100 dark:border-gray-700">
                                <h3 class="font-bold text-xl text-gray-900 dark:text-white" x-text="pmEditMode ? 'Edit Payment Method' : 'Add Payment Method'"></h3>
                                <button type="button" @click="pmModalOpen = false"
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <form :action="pmEditMode ? '{{ url('system-settings/payment-methods') }}/' + pmMethod.id : '{{ route('system-settings.store-payment-method') }}'"
                                method="POST" class="space-y-5">
                                @csrf
                                <input type="hidden" name="_method" :value="pmEditMode ? 'PUT' : 'POST'">

                                <!-- Name -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Method Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" x-model="pmMethod.name" required placeholder="e.g. Cash, GCash, Maya"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary font-medium text-gray-900 dark:text-white">
                                </div>

                                <!-- Icon -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Icon</label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center border border-blue-100 text-primary shrink-0">
                                            <i :class="'fas ' + (pmMethod.icon || 'fa-money-bill') + ' text-xl'"></i>
                                        </div>
                                        <select name="icon" x-model="pmMethod.icon" class="dropdown-btn flex-1">
                                            <option value="fa-money-bill-wave">💵 Cash (fa-money-bill-wave)</option>
                                            <option value="fa-university">🏦 Online Banking (fa-university)</option>
                                            <option value="fa-mobile-alt">📱 E-Wallet / GCash (fa-mobile-alt)</option>
                                            <option value="fa-wallet">👛 Maya / Wallet (fa-wallet)</option>
                                            <option value="fa-credit-card">💳 Credit/Debit Card (fa-credit-card)</option>
                                            <option value="fa-qrcode">📷 QR Code (fa-qrcode)</option>
                                            <option value="fa-money-check-alt">📄 Check (fa-money-check-alt)</option>
                                            <option value="fa-coins">🪙 Coins (fa-coins)</option>
                                            <option value="fa-money-bill">💵 Bill (fa-money-bill)</option>
                                        </select>
                                    </div>
                                </div>



                                <!-- Active toggle -->
                                <div class="flex items-center justify-between pt-2">
                                    <div>
                                        <div class="font-bold text-sm text-gray-700 dark:text-gray-300">Active Status</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Inactive methods won't appear in payment forms.</div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 dark:bg-gray-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <button type="button" @click="pmModalOpen = false"
                                        class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-primary hover:bg-blue-600 shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                                        <i class="fas fa-save"></i>
                                        <span x-text="pmEditMode ? 'Update' : 'Add Method'"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

    <!-- Service Modal -->
    <div x-show="serviceModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="serviceModalOpen = false"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-3xl p-8 relative transform transition-all">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-xl text-gray-900 dark:text-white"
                        x-text="editMode ? 'Edit Service' : 'New Service'"></h3>
                    <button type="button" @click="serviceModalOpen = false"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form
                    :action="editMode ? '{{ route('system-settings.update-service', ['id' => ':id']) }}'.replace(':id', service.id) : '{{ route('system-settings.store-service') }}'"
                    method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="editMode ? 'PUT' : 'POST'">

                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Service Name</label>
                        <input type="text" name="name" x-model="service.name" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Fee (₱)</label>
                        <input type="number" step="0.01" name="fee" x-model="service.fee" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 my-6"></div>

                    <!-- Dynamic Requirements Builder -->
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-500 uppercase mb-2 flex justify-between items-center">
                            Requirements
                            <button type="button" @click="addRequirement()"
                                class="text-xs text-blue-600 hover:text-blue-700 font-bold">
                                <i class="fas fa-plus mr-1"></i> Add Requirement
                            </button>
                        </label>

                        <div class="space-y-2 mb-4 max-h-48 overflow-y-auto custom-scrollbar pr-1">
                            <template x-for="(req, index) in requirements" :key="index">
                                <div class="flex items-center gap-2">
                                    <input type="text" x-model="requirements[index]" name="requirements[]"
                                        placeholder="Requirement (e.g. PSA Birth Certificate)"
                                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    <button type="button" @click="removeRequirement(index)"
                                        class="text-red-500 hover:text-red-700 p-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </template>
                            <div x-show="requirements.length === 0"
                                class="text-center py-3 text-xs text-gray-400 border border-dashed border-gray-200 rounded-lg">
                                No requirements added
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 my-6"></div>

                    <!-- Icon & Color Customization -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Icon</label>
                        <div class="relative">
                            <select name="icon" x-model="service.icon"
                                class="dropdown-btn w-full pl-10">
                                <option value="fa-church">Church (Default)</option>
                                <option value="fa-water">Water (Baptism)</option>
                                <option value="fa-fire">Fire/Dove (Confirmation)</option>
                                <option value="fa-heart">Heart (Wedding)</option>
                                <option value="fa-cross">Cross (Funeral)</option>
                                <option value="fa-hand-holding-medical">Hands (Anointing)</option>
                                <option value="fa-star">Star (Blessing)</option>
                                <option value="fa-book-bible">Bible</option>
                                <option value="fa-dove">Dove</option>
                                <option value="fa-hands-praying">Praying Hands</option>
                            </select>
                            <div
                                class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-500">
                                <i :class="'fas ' + (service.icon || 'fa-church')"></i>
                            </div>
                        </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Calendar Color</label>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shrink-0 shadow-sm">
                                    <input type="color" name="color" x-model="service.color"
                                        class="w-14 h-14 -translate-x-2 -translate-y-2 cursor-pointer border-none outline-none bg-transparent"
                                        title="Pick a color for this service">
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs font-mono font-bold text-gray-700 dark:text-gray-200" x-text="service.color || '#6366f1'"></div>
                                    <div class="text-xs text-gray-400 mt-0.5">Used in calendar &amp; legend</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 my-6"></div>

                    <!-- Accepted Payment Methods -->
                    <div>
                        <label class="block text-sm font-bold text-gray-500 uppercase mb-3">Accepted Payment Methods</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($paymentMethods as $pm)
                                @if($pm->is_active)
                                <label class="flex items-center gap-3 p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:border-primary/50 transition-colors group">
                                    <input type="checkbox" name="payment_methods[]" value="{{ $pm->id }}" x-model="service.payment_methods" class="w-5 h-5 text-primary bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded focus:ring-primary">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-primary flex items-center justify-center border border-blue-100 dark:border-blue-800/30">
                                            <i class="fas {{ $pm->icon ?? 'fa-money-bill' }} text-sm"></i>
                                        </div>
                                        <span class="font-bold text-gray-900 dark:text-gray-200 text-sm">{{ $pm->name }}</span>
                                    </div>
                                </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 my-6"></div>

                    <!-- Custom Fields Builder -->
                    <div>
                        <div class="mb-3">
                            <label class="block text-sm font-bold text-gray-500 uppercase mb-2 flex justify-between">
                                Quick Presets
                                <button type="button" @click="saveAsPreset()"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-bold">
                                    <i class="fas fa-save mr-1"></i> Save Current as Preset
                                </button>
                            </label>
                            <div class="flex flex-wrap gap-2"
                                x-init="$watch('customPresets', val => localStorage.setItem('user_service_presets', JSON.stringify(val)))">
                                <!-- Default Presets -->
                                <button x-show="!hasCustomPreset('Baptism')" type="button" @click="applyPreset('Baptism')"
                                    class="px-2 py-1 text-xs bg-blue-50 text-blue-600 rounded border border-blue-100 hover:bg-blue-100 font-bold">Baptism</button>
                                <button x-show="!hasCustomPreset('Wedding')" type="button" @click="applyPreset('Wedding')"
                                    class="px-2 py-1 text-xs bg-pink-50 text-pink-600 rounded border border-pink-100 hover:bg-pink-100 font-bold">Wedding</button>
                                <button x-show="!hasCustomPreset('Burial')" type="button" @click="applyPreset('Burial')"
                                    class="px-2 py-1 text-xs bg-gray-50 text-gray-600 rounded border border-gray-100 hover:bg-gray-100 font-bold">Burial</button>
                                <button x-show="!hasCustomPreset('Confirmation')" type="button" @click="applyPreset('Confirmation')"
                                    class="px-2 py-1 text-xs bg-purple-50 text-purple-600 rounded border border-purple-100 hover:bg-purple-100 font-bold">Confirmation</button>

                                <!-- Custom Presets -->
                                <template x-for="(preset, name) in customPresets" :key="name">
                                    <div class="relative group">
                                        <button type="button" @click="applyCustomPreset(name)"
                                            class="px-2 py-1 text-xs bg-green-50 text-green-600 rounded border border-green-100 hover:bg-green-100 font-bold">
                                            <span x-text="name"></span>
                                        </button>
                                        <button type="button" @click="deletePreset(name)"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <label
                            class="block text-sm font-bold text-gray-500 uppercase mb-2 flex justify-between items-center">
                            Custom Fields
                            <div class="flex gap-3">
                                <button type="button" @click="$dispatch('open-preview', fields)"
                                    class="text-xs text-gray-600 hover:text-gray-900 font-bold bg-gray-100 px-2 py-1 rounded">
                                    <i class="fas fa-eye mr-1"></i> Preview Form
                                </button>
                                <button type="button" @click="clearFields()"
                                    class="text-xs text-red-600 hover:text-red-700 font-bold bg-red-50 hover:bg-red-100 px-2 py-1 rounded transition-colors">
                                    <i class="fas fa-eraser mr-1"></i> Clear Fields
                                </button>
                                <button type="button" @click="addField()"
                                    class="text-xs text-blue-600 hover:text-blue-700 font-bold bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded transition-colors">
                                    <i class="fas fa-plus mr-1"></i> Add Field
                                </button>
                            </div>
                        </label>

                        <input type="hidden" name="custom_fields" :value="JSON.stringify(fields)">

                        <div class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar pr-1">
                            <template x-for="(field, index) in fields" :key="field.id || index">
                                <div class="flex flex-col bg-gray-50 dark:bg-gray-900 p-2 rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-200 relative"
                                    :class="{ 
                                        'opacity-50 dashed border-2 border-primary': draggingIndex === index, 
                                        'animate-field-highlight': field.is_new,
                                        'border-l-4 border-l-blue-500': field.origin === 'Baptism',
                                        'border-l-4 border-l-pink-500': field.origin === 'Wedding',
                                        'border-l-4 border-l-gray-500': field.origin === 'Burial',
                                        'border-l-4 border-l-purple-500': field.origin === 'Confirmation',
                                        'border-l-4 border-l-green-500': field.origin && !['Baptism', 'Wedding', 'Burial', 'Confirmation'].includes(field.origin)
                                    }">
                                    
                                    <div class="flex items-start gap-2 w-full">
                                        <div class="flex items-center gap-2 shrink-0 pr-1">
                                            <div class="flex flex-col gap-0.5 border-r border-gray-200 dark:border-gray-800 pr-1.5 mr-1.5">
                                                <button type="button" @click="moveUp(index)" 
                                                    :class="index === 0 ? 'opacity-20 cursor-not-allowed' : 'hover:text-primary-600'" 
                                                    :disabled="index === 0" 
                                                    class="text-[11px] text-gray-400 transition-colors" title="Move Up">
                                                    <i class="fas fa-chevron-up"></i>
                                                </button>
                                                <button type="button" @click="moveDown(index)" 
                                                    :class="index === fields.length - 1 ? 'opacity-20 cursor-not-allowed' : 'hover:text-primary-600'" 
                                                    :disabled="index === fields.length - 1" 
                                                    class="text-[11px] text-gray-400 transition-colors" title="Move Down">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                            </div>
                                            <div class="cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600"
                                                draggable="true" @dragstart="draggingIndex = index" @dragover.prevent
                                                @drop="drop(index)" @dragend="draggingIndex = null">
                                                <i class="fas fa-grip-vertical"></i>
                                            </div>
                                        </div>
                                    <div class="grid grid-cols-2 gap-2 flex-1">
                                        <input type="text" x-model="field.label" placeholder="Field Label (e.g. Birth Date)"
                                            :id="'field_label_' + (field.id || index)"
                                            class="text-sm px-2 py-1 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                            required px-1>
                                        <select x-model="field.type"
                                            class="dropdown-btn text-sm px-2 py-1">
                                            <option value="text">Text Input</option>
                                            <option value="date">Date Picker</option>
                                            <option value="number">Number</option>
                                            <option value="textarea">Text Area</option>
                                            <option value="select">Select/Dropdown</option>
                                            <option value="header">Section / Header</option>
                                        </select>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <label x-show="field.type !== 'header'" class="flex items-center cursor-pointer" title="Required">
                                            <input type="checkbox" x-model="field.required"
                                                class="w-4 h-4 text-primary rounded border-gray-300">
                                        </label>
                                        <button type="button" @click="removeField(index)"
                                            class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                    </div>

                                    <!-- Options for Select types -->
                                    <div x-show="field.type === 'select'" class="mt-2 ml-7 bg-blue-50/50 dark:bg-blue-900/10 p-2 rounded-lg border border-blue-100 dark:border-blue-900/30">
                                        <label class="block text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-1 px-1">Dropdown Options (Comma separated)</label>
                                        <input type="text" x-model="field.options" 
                                            placeholder="e.g. Option 1, Option 2, Option 3"
                                            class="w-full text-xs px-2 py-1.5 bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-800 rounded focus:ring-2 focus:ring-blue-500/20">
                                    </div>
                                </div>
                            </template>
                            <div x-show="fields.length === 0"
                                class="text-center py-4 text-xs text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                                No custom fields defined
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="serviceModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold">Cancel</button>
                        <button type="submit"
                            class="px-6 py-2 rounded-xl bg-primary text-white font-bold shadow-lg shadow-blue-500/30">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirmation Modal (Nested) -->
        <div x-show="confirmModalOpen" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="confirmModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm p-6 relative transform transition-all animate-fade-in-up">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white">Apply Preset?</h3>
                        <button type="button" @click="confirmModalOpen = false"
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="text-center mb-6">
                        <div
                            class="w-16 h-16 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center mx-auto mb-4 text-primary">
                            <i class="fas fa-question text-3xl"></i>
                        </div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white mb-2">Apply Preset?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            This will append standard fields to your current list. This action cannot be undone
                            automatically.
                        </p>
                    </div>
                    <div class="flex justify-center gap-3">
                        <button @click="confirmModalOpen = false"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button @click="executePreset()"
                            class="px-5 py-2.5 rounded-xl bg-primary text-white font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-600 transition-colors">
                            Yes, Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal (Nested) -->
        <div x-data="{ previewModalOpen: false, fields: [] }"
            @open-preview.window="previewModalOpen = true; fields = $event.detail" x-show="previewModalOpen"
            class="fixed inset-0 z-[70] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="previewModalOpen = false">
            </div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <!-- Form Paper Look -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-5xl relative transform transition-all animate-fade-in-up border-t-8 border-primary">

                    <!-- Paper Header -->
                    <div
                        class="flex items-center gap-6 p-8 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 flex-none relative">
                        <div
                            class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center text-primary shadow-sm flex-none">
                            <i class="fas fa-file-invoice text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-2xl text-gray-900 dark:text-white">Service Request
                                Form</h3>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">Please fill in the details
                                    below</p>
                        </div>
                        <button @click="previewModalOpen = false"
                            class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="p-8 space-y-6">
                        <!-- Helper Tip -->
                        <div
                            class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 rounded-xl flex items-center gap-4 text-primary">
                            <div
                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center flex-none">
                                <i class="fas fa-info-circle text-sm"></i>
                            </div>
                            <p class="text-[13px] font-bold uppercase tracking-wider">Preview Mode: <span
                                    class="font-medium normal-case opacity-70">This is how the form will appear to the
                                    requestor.</span></p>
                        </div>

                        <!-- Fields Container -->
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <template x-for="(field, index) in fields" :key="index">
                                    <div :class="
                                                    (field.label.toLowerCase().includes('suffix')) ? 'col-span-12 md:col-span-2' :
                                                    (['first name', 'given name'].some(n => field.label.toLowerCase().includes(n))) ? 'col-span-12 md:col-span-4 md:col-start-1' :
                                                    (['middle name', 'maiden name', 'last name', 'surname'].some(n => field.label.toLowerCase().includes(n))) ? 'col-span-12 md:col-span-3' :
                                                    (['textarea', 'header'].includes(field.type)) ? 'col-span-12' : 
                                                    'col-span-12 md:col-span-6'
                                                                                ">
                                        <template x-if="field.type !== 'header'">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                                                <span x-text="field.label || 'Untitled Field'"></span>
                                                <span x-show="field.required" class="text-red-500 ml-0.5">*</span>
                                            </label>
                                        </template>

                                        <template x-if="field.type === 'header'">
                                            <div class="flex items-center gap-3 mt-4 mb-2 first:mt-0">
                                                <h3 class="text-[12px] font-extrabold text-primary uppercase tracking-[0.2em] whitespace-nowrap" x-text="field.label || 'SECTION TITLE'"></h3>
                                                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                            </div>
                                        </template>

                                        <template x-if="field.type === 'text'">
                                            <div>
                                                <div x-show="['Middle Name', 'Middle Initial'].includes(field.label)">
                                                    <input type="text"
                                                        class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                                                        placeholder="Your Answer">
                                                    <label class="inline-flex items-center gap-1.5 mt-1.5 cursor-pointer select-none">
                                                        <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-primary">
                                                        <span class="text-xs text-gray-500 font-medium">N/A (No middle name)</span>
                                                    </label>
                                                </div>
                                                <div x-show="!['Middle Name', 'Middle Initial'].includes(field.label)">
                                                    <input type="text"
                                                        x-data="{ 
                                                            val: '',
                                                            touched: false,
                                                            isContact: field.label.toLowerCase().includes('contact'),
                                                            get invalid() {
                                                                if (!this.touched) return false;
                                                                if (field.required && !this.val) return true;
                                                                if (this.isContact && this.val && this.val.length < 11) return true;
                                                                return false;
                                                            }
                                                        }"
                                                        x-model="val"
                                                        @blur="touched = true"
                                                        @input="if(isContact) { touched = true; val = val.replace(/[^0-9]/g, '').slice(0, 11); }"
                                                        :maxlength="isContact ? 11 : ''"
                                                        :class="invalid ? 'border-red-500 ring-2 ring-red-500/20 bg-red-50/50 dark:bg-red-900/10' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900'"
                                                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                                                        :placeholder="isContact ? '09XXXXXXXXX' : 'Your Answer'">
                                                    <p x-show="invalid" class="text-[10px] text-red-500 mt-1 font-bold">
                                                        <span x-text="!val && field.required ? 'This field is required.' : (isContact ? 'Contact number must be exactly 11 digits.' : '')"></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="field.type === 'date'">
                                            <input type="date"
                                                class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                                        </template>

                                        <template x-if="field.type === 'number'">
                                            <input type="number"
                                                class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                                                placeholder="0">
                                        </template>

                                        <template x-if="field.type === 'textarea'">
                                            <textarea
                                                class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                                                rows="3" placeholder="Enter details here..."></textarea>
                                        </template>

                                        <template x-if="field.type === 'select'">
                                            <select
                                                class="dropdown-btn w-full">
                                                <option>Select Option</option>
                                            </select>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <div x-show="fields.length === 0"
                                class="text-center py-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl mt-4">
                                <div class="text-gray-400 mb-1"><i class="fas fa-clipboard-list text-2xl"></i></div>
                                <div class="text-xs text-gray-400">No custom fields defined</div>
                            </div>
                        </div>
                    </div>

                    <!-- Close Button -->
                    <div
                        class="px-8 py-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                        <button @click="previewModalOpen = false"
                            class="px-6 py-2 bg-gray-800 text-white rounded-lg font-bold hover:bg-gray-900 transition-colors">
                            Close Preview
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Preset Modal -->
        <div x-show="savePresetModalOpen" class="fixed inset-0 z-[80] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="savePresetModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm p-6 relative animate-fade-in-up">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white">Save Preset</h3>
                        <button type="button" @click="savePresetModalOpen = false"
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Preset Name</label>
                        <input type="text" x-model="newPresetName"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
                            placeholder="e.g. My Custom Requirements" @keydown.enter.prevent="confirmSavePreset()">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button @click="savePresetModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-gray-100 text-gray-600 font-bold">Cancel</button>
                        <button @click="confirmSavePreset()"
                            class="px-4 py-2 rounded-xl bg-primary text-white font-bold shadow-lg shadow-blue-500/30">Save
                            Preset</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Preset Confirmation Modal -->
        <div x-show="deletePresetModalOpen" class="fixed inset-0 z-[80] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="deletePresetModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm p-6 relative animate-fade-in-up">
                    <div class="text-center mb-6">
                        <div
                            class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4 text-red-600">
                            <i class="fas fa-trash-alt text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-xl text-gray-800 dark:text-white mb-2">Delete Preset?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Are you sure you want to delete <strong x-text="presetToDelete"></strong>?
                        </p>
                    </div>
                    <div class="flex justify-center gap-3">
                        <button @click="deletePresetModalOpen = false"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button @click="confirmDeletePreset()"
                            class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold shadow-lg shadow-red-500/30 hover:bg-red-700 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('head_scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('serviceSettings', () => ({
@php
    $defaultSettingsTab = Auth::user()->hasModule('system_settings_general') ? 'general' : 
                          (Auth::user()->hasModule('system_settings_priests') ? 'priests' : 
                          (Auth::user()->hasModule('system_settings_services') ? 'services' : 
                          (Auth::user()->hasModule('system_settings_payment_methods') ? 'payment_methods' : 
                          (Auth::user()->hasModule('system_settings_database') ? 'database' : ''))));
@endphp
        activeTab: new URLSearchParams(window.location.search).get('tab') || localStorage.getItem('system_settings_tab') || '{{ $defaultSettingsTab }}',

        init() {
            this.$watch('activeTab', val => localStorage.setItem('system_settings_tab', val));
        },

        serviceModalOpen: false,
        editMode: false,
        service: { payment_methods: [] },
        requirements: [],
        fields: [],
        confirmModalOpen: false,
        pendingPresetType: '',
        customPresets: JSON.parse(localStorage.getItem('user_service_presets') || '{}'),
        savePresetModalOpen: false,
        deletePresetModalOpen: false,
        // Modal states handled by layouts.app global modal
        notificationModalOpen: false,
        newPresetName: '',
        presetToDelete: null,
        draggingIndex: null,

        // Payment Methods Management
        pmModalOpen: false,
        pmEditMode: false,
        pmMethod: { id: null, name: '', icon: 'fa-money-bill', sort_order: 0 },
        openPmModal(isEdit, method) {
            this.pmEditMode = isEdit;
            this.pmMethod = isEdit ? { ...method } : { id: null, name: '', icon: 'fa-money-bill', sort_order: 0 };
            this.pmModalOpen = true;
        },

        openServiceModal(isEdit, serviceData) {
            this.editMode = isEdit;
            
            // Normalize service data
            const baseService = {
                id: null,
                name: '',
                fee: 0,
                icon: 'fa-church',
                color: '#6366f1',
                payment_methods: [],
                requirements: [],
                custom_fields: []
            };

            this.service = { ...baseService, ...(serviceData || {}) };
            
            // Robust normalization for payment_methods array
            let pms = this.service.payment_methods;
            if (!pms) {
                pms = [];
            } else if (typeof pms === 'string') {
                try {
                    pms = JSON.parse(pms);
                } catch(e) {
                    pms = pms.split(',').map(s => s.trim()).filter(Boolean);
                }
            }
            this.service.payment_methods = Array.isArray(pms) ? pms.map(String) : [];

            this.initRequirements();
            this.initFields();
            this.serviceModalOpen = true;
        },

        initRequirements() {
            if (this.service.requirements && this.service.requirements.length > 0) {
                this.requirements = typeof this.service.requirements === 'string'
                    ? this.service.requirements.split(',').map(r => r.trim())
                    : [...this.service.requirements];
            } else {
                this.requirements = [];
            }
        },

        addRequirement() { this.requirements.push(''); },
        removeRequirement(index) { this.requirements.splice(index, 1); },

        initFields() {
            try {
                let customFields = [];
                if (this.service.custom_fields) {
                    if (Array.isArray(this.service.custom_fields)) {
                        customFields = this.service.custom_fields;
                    } else if (typeof this.service.custom_fields === 'string') {
                        let parsed = JSON.parse(this.service.custom_fields);
                        customFields = Array.isArray(parsed) ? parsed : [];
                    }
                }
                
                if (customFields.length > 0) {
                    this.fields = customFields.map((f, i) => ({ 
                        ...f, 
                        is_standard: false, 
                        id: f.id || ('fld_' + Date.now() + '_' + i) 
                    }));
                } else {
                    // Default fields for new services
                    this.fields = [
                        { id: 'fld_first', label: 'First Name', type: 'text', required: true },
                        { id: 'fld_middle', label: 'Middle Name', type: 'text', required: false },
                        { id: 'fld_last', label: 'Last Name', type: 'text', required: true },
                        { id: 'fld_contact', label: 'Contact Number', type: 'text', required: false }
                    ];
                }
            } catch(e) {
                console.error('Error parsing custom fields:', e);
                this.fields = [];
            }
        },

        addField() { 
            const newId = 'fld_' + Date.now() + Math.random().toString(36).substring(7);
            this.fields.push({ id: newId, label: '', type: 'text', required: false, is_new: true }); 
            this.$nextTick(() => {
                const el = document.getElementById('field_label_' + newId);
                if (el) {
                    el.focus();
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                setTimeout(() => {
                    const idx = this.fields.findIndex(f => f.id === newId);
                    if (idx !== -1) this.fields[idx].is_new = false;
                }, 2000);
            });
        },
        removeField(index) { this.fields.splice(index, 1); },
        moveUp(index) {
            if (index > 0) {
                const item = this.fields[index];
                this.fields.splice(index, 1);
                this.fields.splice(index - 1, 0, item);
            }
        },
        moveDown(index) {
            if (index < this.fields.length - 1) {
                const item = this.fields[index];
                this.fields.splice(index, 1);
                this.fields.splice(index + 1, 0, item);
            }
        },
        clearFields() { this.fields = []; },

        drop(targetIndex) {
            if (this.draggingIndex === null) return;
            const item = this.fields[this.draggingIndex];
            this.fields.splice(this.draggingIndex, 1);
            this.fields.splice(targetIndex, 0, item);
            this.draggingIndex = null;
        },

        showNotification(msg, type) {
            window.showConfirm(
                type === 'success' ? 'Success!' : (type === 'error' ? 'Error' : 'Information'),
                msg,
                type === 'success' ? 'bg-green-600 hover:bg-green-700' : (type === 'error' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'),
                null, 
                'OK', 
                '', 
                true
            );
        },

        saveAsPreset() {
            if (this.fields.filter(f => !f.is_standard).length === 0) {
                this.showNotification('Please add some fields first!', 'error');
                return;
            }
            this.savePresetModalOpen = true;
            this.newPresetName = '';
        },

        hasCustomPreset(name) {
            const lowerName = name.toLowerCase();
            return Object.keys(this.customPresets).some(k => k.toLowerCase() === lowerName);
        },

        confirmSavePreset() {
            if (!this.newPresetName.trim()) {
                this.showNotification('Please enter a preset name.', 'error');
                return;
            }
            const name = this.newPresetName.trim();
            const lowerName = name.toLowerCase();

            // Find existing custom preset (case-insensitive)
            const existingCustomName = Object.keys(this.customPresets).find(k => k.toLowerCase() === lowerName);
            
            // Check if it's a standard preset
            const standardPresets = ['baptism', 'wedding', 'burial', 'confirmation'];
            const isStandard = standardPresets.includes(lowerName);

            if (existingCustomName) {
                window.showConfirm(
                    'Overwrite Preset?',
                    `A custom preset named "${existingCustomName}" already exists. Do you want to overwrite it?`,
                    'bg-blue-600 hover:bg-blue-700',
                    () => { this.executeSavePreset(existingCustomName); },
                    'Overwrite'
                );
                return;
            }

            if (isStandard) {
                 window.showConfirm(
                    'Override Standard Preset?',
                    `"${name}" is a standard preset. Do you want to override it with your custom fields?`,
                    'bg-blue-600 hover:bg-blue-700',
                    () => { this.executeSavePreset(name); },
                    'Override'
                );
                return;
            }

            this.executeSavePreset(name);
        },

        executeSavePreset(name) {
            this.customPresets[name] = JSON.parse(JSON.stringify(this.fields.filter(f => !f.is_standard)));
            localStorage.setItem('user_service_presets', JSON.stringify(this.customPresets));
            this.savePresetModalOpen = false;
            this.showNotification("Preset '" + name + "' saved successfully!", 'success');
        },

        deletePreset(name) {
            this.presetToDelete = name;
            this.deletePresetModalOpen = true;
        },

        confirmDeletePreset() {
            if (this.presetToDelete) {
                delete this.customPresets[this.presetToDelete];
                this.customPresets = { ...this.customPresets };
                localStorage.setItem('user_service_presets', JSON.stringify(this.customPresets));
                this.deletePresetModalOpen = false;
                this.presetToDelete = null;
            }
        },

        applyCustomPreset(name) {
            const presetFields = this.customPresets[name];
            if (!presetFields) return;
            const now = Date.now();
            const newFields = presetFields.filter(nf => !this.fields.some(ef => ef.label === nf.label))
                                          .map((f, i) => ({ ...f, origin: name, id: 'preset_' + now + '_' + i, is_new: true }));
            
            if (newFields.length > 0) {
                this.fields = [...this.fields, ...newFields];
                this.$nextTick(() => {
                    const firstNewId = newFields[0].id;
                    const el = document.getElementById('field_label_' + firstNewId);
                    if (el) {
                        el.focus();
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    setTimeout(() => {
                        this.fields.forEach(f => { if (f.is_new) f.is_new = false; });
                    }, 2000);
                });
            }
        },

        applyPreset(type) {
            this.pendingPresetType = type;
            this.confirmModalOpen = true;
        },

        executePreset() {
            const type = this.pendingPresetType;
            const presets = {
                'Baptism': [
                    { label: 'Date of Birth', type: 'date', required: true, origin: 'Baptism' },
                    { label: 'Place of Birth', type: 'text', required: true, origin: 'Baptism' },
                    { label: "Father's Name", type: 'text', required: true, origin: 'Baptism' },
                    { label: "Mother's Name", type: 'text', required: true, origin: 'Baptism' },
                    { label: 'Godparents', type: 'textarea', required: true, origin: 'Baptism' }
                ],
                'Wedding': [
                    { label: "Groom's Details", type: 'header', origin: 'Wedding' },
                    { label: "Groom's First Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Groom's Middle Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Groom's Last Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Groom's Suffix", type: 'text', required: false, origin: 'Wedding' },
                    { label: "Groom's Civil Status", type: 'select', options: ['Single', 'Widowed', 'Annulled', 'Separated'], required: true, origin: 'Wedding' },
                    { label: "Groom's Date of Birth", type: 'date', required: true, origin: 'Wedding' },
                    { label: "Groom's Place of Birth", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Bride's Details", type: 'header', origin: 'Wedding' },
                    { label: "Bride's First Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Bride's Middle Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Bride's Last Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Bride's Suffix", type: 'text', required: false, origin: 'Wedding' },
                    { label: "Bride's Civil Status", type: 'select', options: ['Single', 'Widowed', 'Annulled', 'Separated'], required: true, origin: 'Wedding' },
                    { label: "Bride's Date of Birth", type: 'date', required: true, origin: 'Wedding' },
                    { label: "Bride's Place of Birth", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Parental Information", type: 'header', origin: 'Wedding' },
                    { label: "Groom's Father's First Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Groom's Father's Middle Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Groom's Father's Last Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Groom's Father's Suffix", type: 'text', required: false, origin: 'Wedding' },
                    { label: "Groom's Mother's First Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Groom's Mother's Middle Name (Maiden)", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Groom's Mother's Last Name (Maiden)", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Bride's Father's First Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Bride's Father's Middle Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Bride's Father's Last Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Bride's Father's Suffix", type: 'text', required: false, origin: 'Wedding' },
                    { label: "Bride's Mother's First Name", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Bride's Mother's Middle Name (Maiden)", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Bride's Mother's Last Name (Maiden)", type: 'text', required: true, origin: 'Wedding' },
                    { label: "Marriage License Details", type: 'header', origin: 'Wedding' },
                    { label: 'Marriage License No.', type: 'text', required: true, origin: 'Wedding' },
                    { label: 'Date of Marriage License', type: 'date', required: true, origin: 'Wedding' },
                    { label: 'Issuing City/Municipality', type: 'text', required: true, origin: 'Wedding' },
                    { label: "Sponsors & Others", type: 'header', origin: 'Wedding' },
                    { label: 'Principal Sponsors (Ninongs & Ninangs)', type: 'textarea', required: true, origin: 'Wedding' },
                    { label: 'Type of Wedding', type: 'text', required: false, origin: 'Wedding' },
                ],
                'Burial': [
                    { label: "Deceased's Full Name", type: 'text', required: true, origin: 'Burial' },
                    { label: 'Date of Death', type: 'date', required: true, origin: 'Burial' },
                    { label: 'Place of Death', type: 'text', required: true, origin: 'Burial' },
                    { label: 'Cause of Death', type: 'text', required: true, origin: 'Burial' },
                    { label: 'Age at Time of Death', type: 'number', required: true, origin: 'Burial' },
                    { label: 'Civil Status of Deceased', type: 'text', required: false, origin: 'Burial' },
                    { label: 'Place of Interment / Cemetery', type: 'text', required: true, origin: 'Burial' },
                    { label: 'Funeral Parlor (if any)', type: 'text', required: false, origin: 'Burial' },
                    { label: 'Name of Bereaved Family Representative', type: 'text', required: true, origin: 'Burial' },
                    { label: 'Relationship of Applicant to Deceased', type: 'text', required: true, origin: 'Burial' },
                    { label: 'Special Requests or Notes', type: 'textarea', required: false, origin: 'Burial' },
                ],
                'Confirmation': [
                    { label: 'Date of Baptism', type: 'date', required: true, origin: 'Confirmation' },
                    { label: 'Place of Baptism', type: 'text', required: true, origin: 'Confirmation' },
                    { label: 'Parish Where Baptized', type: 'text', required: true, origin: 'Confirmation' },
                    { label: "Father's Name", type: 'text', required: true, origin: 'Confirmation' },
                    { label: "Mother's Maiden Name", type: 'text', required: true, origin: 'Confirmation' },
                    { label: 'Date of Birth', type: 'date', required: true, origin: 'Confirmation' },
                    { label: 'Place of Birth', type: 'text', required: true, origin: 'Confirmation' },
                    { label: 'Confirmation Sponsor (Godparent)', type: 'text', required: true, origin: 'Confirmation' },
                    { label: 'School / Catechism Class', type: 'text', required: false, origin: 'Confirmation' },
                ]
            };
            if (presets[type]) {
                const now = Date.now();
                const newFields = presets[type].filter(nf => !this.fields.some(ef => ef.label === nf.label))
                                               .map((f, i) => ({ ...f, id: 'syspreset_' + now + '_' + i, is_new: true }));
                
                if (newFields.length > 0) {
                    this.fields = [...this.fields, ...newFields];
                    this.$nextTick(() => {
                        const firstNewId = newFields[0].id;
                        const el = document.getElementById('field_label_' + firstNewId);
                        if (el) {
                            el.focus();
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        setTimeout(() => {
                            this.fields.forEach(f => { if (f.is_new) f.is_new = false; });
                        }, 2000);
                    });
                }
            }
            this.confirmModalOpen = false;
        },

        confirmRestore(e) {
            e.preventDefault();
            window.showConfirm(
                'Restore Database',
                'WARNING: Restoring the database will OVERWRITE all current data. This cannot be undone. Are you sure?',
                'bg-red-600 hover:bg-red-700',
                () => { e.target.submit(); },
                'Restore'
            );
        }
    }));

    Alpine.data('priestScheduleManager', () => ({
        priests: [],
        selectedPriest: null,
        isSaving: false,
        formData: {
            working_days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            working_hours: { start: '08:00', end: '17:00' },
            max_services_per_day: 5
        },

        initPriests(priestsData) {
            this.priests = priestsData || [];
        },

        selectPriest(priest) {
            this.selectedPriest = priest;
            
            // Populate form with existing data or defaults
            // Ensure working_days is always an array
            let days = priest.working_days;
            if (!days) {
                days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            } else if (typeof days === 'string') {
                try {
                    days = JSON.parse(days);
                } catch(e) {
                    days = days.split(',').map(s => s.trim()).filter(Boolean);
                }
            }
            this.formData.working_days = Array.isArray(days) ? days : [];
            
            this.formData.working_hours = priest.working_hours || { start: '08:00', end: '17:00' };
            
            // Handle if working_hours was somehow saved as a string (legacy)
            if (typeof this.formData.working_hours === 'string') {
                try {
                    this.formData.working_hours = JSON.parse(this.formData.working_hours);
                } catch(e) {
                    this.formData.working_hours = { start: '08:00', end: '17:00' };
                }
            }

            this.formData.max_services_per_day = priest.max_services_per_day || 5;
        },

        async saveScheduleSettings() {
            if (!this.selectedPriest) return;
            
            if (this.formData.working_days.length === 0) {
                window.showConfirm('Information', 'Please select at least one working day.', 'bg-red-600', null, 'OK', '', true);
                return;
            }

            this.isSaving = true;

            try {
                const response = await fetch(`{{ url('/system-settings/priest-schedule') }}/${this.selectedPriest.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.formData)
                });

                const result = await response.json();

                if (response.ok) {
                    // Update the local priest array so clicking back shows updated data
                    const idx = this.priests.findIndex(p => p.id === this.selectedPriest.id);
                    if (idx !== -1) {
                        this.priests[idx].working_days = this.formData.working_days;
                        this.priests[idx].working_hours = this.formData.working_hours;
                        this.priests[idx].max_services_per_day = this.formData.max_services_per_day;
                    }

                    window.showConfirm('Success!', 'Priest schedule updated successfully!', 'bg-green-600 hover:bg-green-700', null, 'OK', '', true);
                } else {
                    window.showConfirm('Error', result.message || 'Validation Error', 'bg-red-600 hover:bg-red-700', null, 'OK', '', true);
                }
            } catch (error) {
                console.error(error);
                window.showConfirm('Error', 'An error occurred while saving. Please try again.', 'bg-red-600 hover:bg-red-700', null, 'OK', '', true);
            } finally {
                this.isSaving = false;
            }
        }
    }));
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('logoInput').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) { document.getElementById('logoPreview').src = e.target.result; }
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    document.getElementById('loginBgInput').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) { document.getElementById('loginBgPreview').src = e.target.result; }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
});
</script>
@endpush