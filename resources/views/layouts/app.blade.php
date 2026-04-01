<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $global_settings['system_short_name'] ?? 'IFI CMS' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Merriweather:wght@300;400;700&display=swap"
        rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/air-datepicker@3.4.0/air-datepicker.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/air-datepicker@3.4.0/air-datepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <script src="{{ asset('assets/js/tailwind-config.js') }}"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 20px;
        }

        [x-cloak] {
            display: none !important;
        }

        /* FullCalendar Dark Mode Overrides */
        .dark .fc {
            --fc-page-bg-color: #1f2937;
            --fc-neutral-bg-color: #374151;
            --fc-neutral-text-color: #e5e7eb;
            --fc-border-color: #374151;
            --fc-button-text-color: #fff;
            --fc-button-bg-color: #374151;
            --fc-button-border-color: #4b5563;
            --fc-button-hover-bg-color: #4b5563;
            --fc-button-hover-border-color: #6b7280;
            --fc-button-active-bg-color: #1f2937;
            --fc-button-active-border-color: #374151;
            color: #e5e7eb;
        }

        .dark .fc-theme-standard td,
        .dark .fc-theme-standard th {
            border-color: #374151;
        }

        .dark .fc-day-today {
            background-color: rgba(59, 130, 246, 0.1) !important;
        }

        .dark .fc-col-header-cell-cushion,
        .dark .fc-daygrid-day-number {
            color: #e5e7eb;
            text-decoration: none;
        }
    </style>
    <!-- Prevent Flash of Wrong Theme -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @stack('head_scripts')
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <link rel="icon" href="{{ isset($global_settings['church_logo']) ? asset('uploads/' . $global_settings['church_logo']) : asset('assets/img/logo.png') }}" type="image/x-icon">
</head>

<body class="bg-gray-50 dark:bg-gray-900 font-poppins text-gray-800 dark:text-gray-200 transition-colors duration-200"
    x-data="{ 
        sidebarOpen: false, 
        darkMode: localStorage.getItem('theme') === 'dark',
        toggleTheme() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            window.dispatchEvent(new Event('theme-toggled'));
        }
    }" :class="{ 'dark': darkMode }" x-cloak>

    <div class="flex h-[100dvh] overflow-hidden" x-cloak>
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col transition-transform duration-300 ease-in-out transform md:static md:inset-auto md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            @include('layouts.partials.sidebar')
        </aside>

        <!-- Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-40 backdrop-blur-sm md:hidden" x-transition.opacity></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            @include('layouts.partials.header')

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-950 p-6">
                @yield('content')
            </main>
        </div>
    </div>
    <!-- Global Confirmation Modal -->
    <div x-data="{ 
            open: false, 
            title: '', 
            message: '', 
            btnClass: '', 
            confirmText: 'Confirm', 
            cancelText: 'Cancel', 
            isAlert: false, 
            onConfirm: null,
            init() {
                @if(session('success'))
                    this.open = true;
                    this.title = 'Success';
                    this.message = '{{ session('success') }}';
                    this.btnClass = 'bg-green-600 hover:bg-green-700';
                    this.confirmText = 'Okay';
                    this.isAlert = true;
                @endif
                @if(session('error'))
                    this.open = true;
                    this.title = 'Error';
                    this.message = '{{ session('error') }}';
                    this.btnClass = 'bg-red-600 hover:bg-red-700';
                    this.confirmText = 'Okay';
                    this.isAlert = true;
                @endif
                @if($errors->any())
                    this.open = true;
                    this.title = 'Validation Error';
                    this.message = '{!! implode('\n', $errors->all()) !!}';
                    this.btnClass = 'bg-red-600 hover:bg-red-700';
                    this.confirmText = 'Okay';
                    this.isAlert = true;
                @endif
            }
        }" x-on:show-confirm.window="
            open = true;
            title = $event.detail.title;
            message = $event.detail.message;
            btnClass = $event.detail.btnClass || 'bg-blue-600 hover:bg-blue-700';
            confirmText = $event.detail.confirmText || 'Confirm';
            cancelText = $event.detail.cancelText || 'Cancel';
            isAlert = $event.detail.isAlert || false;
            onConfirm = $event.detail.onConfirm;
         " x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm"
        style="display: none;" x-transition.opacity>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 relative border border-gray-100 dark:border-gray-700 transform transition-all"
            x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
            @click.away="if(!isAlert) open = false">

            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full mb-6 border-4 transition-colors duration-300"
                    :class="btnClass.includes('red') ? 'bg-orange-50 border-orange-100' : (btnClass.includes('green') ? 'bg-emerald-50 border-emerald-100' : 'bg-blue-50 border-blue-100')">
                    <template x-if="btnClass.includes('green')">
                        <i class="fas fa-check text-4xl text-emerald-500"></i>
                    </template>
                    <template x-if="btnClass.includes('red')">
                        <i class="fas fa-exclamation text-4xl text-orange-400"></i>
                    </template>
                    <template x-if="!btnClass.includes('green') && !btnClass.includes('red')">
                        <i class="fas fa-info text-4xl text-blue-500"></i>
                    </template>
                </div>

                <h3 class="text-xl font-bold text-gray-800 dark:text-white" x-text="title"></h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2" x-text="message"></p>
            </div>

            <div class="mt-8 grid gap-4" :class="isAlert ? 'grid-cols-1' : 'grid-cols-2'">
                <button type="button" @click="open = false" x-show="!isAlert"
                    class="w-full inline-flex justify-center rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 shadow-sm px-6 py-2.5 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none transition-all transform hover:-translate-y-0.5 whitespace-nowrap"
                    x-text="cancelText">
                </button>
                <button type="button" @click="if(onConfirm) onConfirm(); open = false" :class="btnClass"
                    class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg px-6 py-2.5 text-sm font-bold text-white focus:outline-none transition-all transform hover:-translate-y-0.5 whitespace-nowrap"
                    x-text="confirmText">
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global helper to trigger the Alpine modal
        window.showConfirmModal = function (data) {
            window.dispatchEvent(new CustomEvent('show-confirm', {
                detail: data
            }));
        }

        // Wrapper for the legacy/inline calls (like in Logout)
        window.showConfirm = function (title, message, btnClass, callback, confirmText = 'Confirm', cancelText = 'Cancel') {
            window.showConfirmModal({
                title: title,
                message: message,
                btnClass: btnClass,
                confirmText: confirmText,
                cancelText: cancelText,
                isAlert: false,
                onConfirm: callback
            });
        }
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('tableSearch', function() {
                let abortController = null;
                
                return {
                    isLoading: false,
                    submitSearch() {
                        if (abortController) {
                            abortController.abort();
                        }
                        abortController = new AbortController();
                        const reqSignal = abortController.signal;

                        this.isLoading = true;
                        
                        let primaryForm = this.$root.querySelector('form.search-form') || this.$root.querySelector('form');
                        // Base URL without existing query params
                        let url = new URL(primaryForm ? (primaryForm.action || window.location.href).split('?')[0] : window.location.href.split('?')[0]);
                        let params = new URLSearchParams();
                    
                    // 1. Identify all keys present in our search/filter forms
                    let controlledKeys = new Set();
                    this.$root.querySelectorAll('form').forEach(f => {
                        new FormData(f).forEach((v, k) => controlledKeys.add(k));
                    });

                    // 2. Start with current URL parameters, but exclude anything our forms control
                    let currentUrlParams = new URLSearchParams(window.location.search);
                    currentUrlParams.forEach((value, key) => {
                        if (!controlledKeys.has(key) && key !== 'page') {
                            params.set(key, value);
                        }
                    });

                    // 3. Add values from the forms
                    this.$root.querySelectorAll('form').forEach(f => {
                        let formData = new FormData(f);
                        for (let [key, value] of formData.entries()) {
                            if (value !== '') {
                                params.set(key, value);
                            } else {
                                // If empty, explicitly ensure it's removed from final params
                                params.delete(key);
                            }
                        }
                    });

                    // 4. Always reset page to 1 on any search/filter change
                    params.delete('page');
                    
                    url.search = params.toString();
                    window.history.pushState({}, '', url);
                    
                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: reqSignal
                    })
                    .then(res => res.text())
                    .then(html => {
                        let parser = new DOMParser();
                        let doc = parser.parseFromString(html, 'text/html');
                        let newContainer = doc.querySelector('.search-results-container');
                        let oldContainer = this.$root.classList.contains('search-results-container') ? this.$root : this.$root.querySelector('.search-results-container');
                        
                        if (newContainer && oldContainer) {
                            // Preserve active element state to prevent keystroke loss
                            let activeEl = document.activeElement;
                            let preserveFocus = false;
                            let activeSelector = '';
                            let activeValue = '';
                            let selectionStart = null;
                            let selectionEnd = null;
                            
                            if (activeEl && oldContainer.contains(activeEl) && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT')) {
                                preserveFocus = true;
                                if (activeEl.name) {
                                    activeSelector = activeEl.tagName.toLowerCase() + '[name="' + activeEl.name + '"]';
                                } else if (activeEl.id) {
                                    activeSelector = '#' + activeEl.id;
                                }
                                activeValue = activeEl.value;
                                if (activeEl.tagName === 'INPUT' && (activeEl.type === 'text' || activeEl.type === 'search' || activeEl.type === 'email')) {
                                    selectionStart = activeEl.selectionStart;
                                    selectionEnd = activeEl.selectionEnd;
                                }
                            }

                            oldContainer.innerHTML = newContainer.innerHTML;

                            if (preserveFocus && activeSelector) {
                                setTimeout(() => {
                                    let newEl = oldContainer.querySelector(activeSelector);
                                    if (newEl) {
                                        newEl.focus();
                                        if (newEl.tagName === 'INPUT' && (newEl.type === 'text' || newEl.type === 'search')) {
                                            newEl.value = activeValue; // Preserve letters typed during AJAX
                                            if (selectionStart !== null) {
                                                newEl.setSelectionRange(selectionStart, selectionEnd);
                                            }
                                        }
                                    }
                                }, 10);
                            }
                        }
                    })
                    .catch(err => {
                        if (err.name === 'AbortError') {
                            return; // Do nothing if aborted
                        }
                        console.error('Search failed:', err);
                    })
                    .finally(() => {
                        // Only remove loading state if the request wasn't aborted
                        if (abortController && reqSignal === abortController.signal && !reqSignal.aborted) {
                            this.isLoading = false;
                        }
                    });
                },
                clearFilters() {
                    this.$root.querySelectorAll('form').forEach(f => {
                        f.querySelectorAll('select, input[type="date"]').forEach(el => {
                            if (el.name !== 'per_page') {
                                el.value = '';
                            }
                        });
                        f.querySelectorAll('.datepicker-input').forEach(el => {
                            el.value = '';
                        });
                    });
                    this.submitSearch();
                },
                handlePagination(e) {
                    let link = e.target.closest('a');
                    if (link && link.href && link.href.includes('page=')) {
                        e.preventDefault();
                        this.isLoading = true;
                        
                        let url = new URL(link.href);
                        window.history.pushState({}, '', url);
                        
                        fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => res.text())
                        .then(html => {
                            let parser = new DOMParser();
                            let doc = parser.parseFromString(html, 'text/html');
                            let newContainer = doc.querySelector('.search-results-container');
                            let oldContainer = this.$root.classList.contains('search-results-container') ? this.$root : this.$root.querySelector('.search-results-container');
                            
                            if (newContainer && oldContainer) {
                                oldContainer.innerHTML = newContainer.innerHTML;
                            }
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                    }
                }
            };
        });
    });
    </script>
    @stack('scripts')
</body>

</html>