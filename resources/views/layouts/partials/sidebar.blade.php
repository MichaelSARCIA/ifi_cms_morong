@php $currentRoute = Route::currentRouteName(); @endphp

<div class="pt-8 pb-6 flex flex-col items-center px-4">
    <div class="flex flex-col items-center gap-3 text-primary relative group cursor-default">
        <div
            class="bg-white/50 dark:bg-gray-800/50 p-2 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/50">
            <img src="{{ isset($global_settings['church_logo']) ? asset('uploads/' . $global_settings['church_logo']) : asset('assets/img/logo.png') }}"
                alt="Church Logo"
                class="h-14 w-auto relative z-10 drop-shadow-sm transition-transform duration-300 group-hover:scale-105">
        </div>
        <div class="relative z-10 text-center mt-1">
            <h2 class="font-extrabold text-lg leading-tight text-gray-800 dark:text-white tracking-tight">
                {!! nl2br(e($global_settings['system_name'] ?? "Iglesia Filipina\nIndependiente")) !!}
            </h2>

        </div>
    </div>
</div>

<nav id="sidebar-nav" x-data="{ 
        activeMenu: null,
        toggle(menu) {
            this.activeMenu = (this.activeMenu === menu) ? null : menu;
        }
    }" class="flex-1 overflow-y-auto py-6 px-4 space-y-1 custom-scrollbar">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var sidebar = document.getElementById('sidebar-nav');
            if (!sidebar) return;

            // Find the currently active link (has active/highlight classes)
            var activeLink = sidebar.querySelector('a[class*="ring-"], a[class*="text-blue-7"], a[class*="text-purple-7"], a[class*="text-green-7"], a[class*="text-orange-7"]');

            if (activeLink) {
                // Use getBoundingClientRect to get true visual position (avoids offsetParent issues)
                // scrollIntoView scrolls the nearest overflow container — which is the sidebar
                sidebar.style.scrollBehavior = 'auto';

                // First ensure sidebar is at top so rects are accurate, then calculate
                sidebar.scrollTop = 0;

                // Now get the rect of the active link relative to the sidebar container
                var sidebarRect  = sidebar.getBoundingClientRect();
                var activeLinkRect = activeLink.getBoundingClientRect();

                // Walk up to find the section group container (Finance/Ministry/Admin)
                var sectionGroup = activeLink;
                var candidate = activeLink.parentElement;
                while (candidate && candidate !== sidebar) {
                    var cls = candidate.className || '';
                    if (cls.includes('mt-4') || cls.includes('mt-6')) {
                        sectionGroup = candidate;
                        break;
                    }
                    candidate = candidate.parentElement;
                }

                // Get the section group's rect
                var groupRect = sectionGroup.getBoundingClientRect();

                // Scroll so that the section group top is 12px from sidebar top
                sidebar.scrollTop = Math.max(0, groupRect.top - sidebarRect.top - 12);
            } else {
                // No active link detected — restore last saved position
                var savedPos = localStorage.getItem('sidebarScroll');
                if (savedPos) {
                    sidebar.style.scrollBehavior = 'auto';
                    sidebar.scrollTop = parseInt(savedPos);
                }
            }

            // Save position whenever any sidebar link is clicked (before navigation)
            sidebar.addEventListener('click', function (e) {
                var link = e.target.closest('a[href]');
                if (link) {
                    localStorage.setItem('sidebarScroll', sidebar.scrollTop);
                    // Clear tab states so they reset to "unahan" when navigating to a new menu
                    localStorage.removeItem('system_settings_tab');
                    localStorage.removeItem('service_requests_tab');
                }
            });

            // Save on scroll as a fallback
            sidebar.addEventListener('scroll', function () {
                localStorage.setItem('sidebarScroll', sidebar.scrollTop);
            });
        });
    </script>

    <!-- MAIN -->
    <div class="px-2 mt-2 mb-3">
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ $currentRoute == 'dashboard' ? 'ring-2 ring-blue-500/20 border-blue-200 text-blue-700 dark:bg-blue-900/40 dark:border-blue-700/50 dark:text-blue-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
            <div
                class="w-8 h-8 rounded-lg flex items-center justify-center {{ $currentRoute == 'dashboard' ? 'bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-200' : 'bg-gray-100 text-gray-500 group-hover:bg-primary group-hover:text-white dark:bg-gray-700 dark:text-gray-400 dark:group-hover:bg-primary transition-colors' }}">
                <i class="fas fa-th-large text-sm"></i>
            </div>
            <span class="tracking-wide text-[15px]">Dashboard</span>
        </a>
    </div>

    <!-- MINISTRY SERVICES -->
    @if(Auth::user()->hasModule('service_requests') || Auth::user()->hasModule('scheduling') || Auth::user()->hasModule('service_records'))
        <div class="px-2 mt-4 space-y-2">
            <div class="px-2 mb-3 flex items-center gap-3">
                <div class="w-1.5 h-4 bg-purple-500 rounded-full"></div>
                <span class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Ministry</span>
            </div>

            <div class="space-y-2">
                @if(Auth::user()->hasModule('service_requests'))
                    <div x-data="{ 
                        open: {{ Str::startsWith($currentRoute, 'service-requests') ? 'true' : 'false' }} 
                    }">
                        <button @click="open = !open"
                            class="flex items-center justify-between w-full px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ Str::startsWith($currentRoute, 'service-requests') ? 'ring-1 ring-purple-500/20 border-purple-200 text-purple-700 dark:bg-purple-900/20 dark:border-purple-700/50 dark:text-purple-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-8 h-8 rounded-lg flex items-center justify-center {{ Str::startsWith($currentRoute, 'service-requests') ? 'bg-purple-100 text-purple-600 dark:bg-purple-800 dark:text-purple-200' : 'bg-gray-100 text-gray-500 group-hover:bg-purple-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                                    <i class="fas fa-praying-hands text-sm"></i>
                                </div>
                                <span class="tracking-wide text-[15px]">Services Request</span>
                            </div>
                            <i class="fas fa-chevron-right text-sm text-gray-400 dark:text-gray-500 transition-transform duration-200"
                                :class="open ? 'rotate-90 text-purple-500 dark:text-purple-400' : ''"></i>
                        </button>

                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2" class="pl-14 pr-2 space-y-1 mt-2">
                            <!-- DEBUG: Sidebar Count {{ count($service_types) }} -->
                            @foreach($service_types as $type)
                                <!-- DEBUG: Rendering Service {{ $type->name }} -->
                                <a href="{{ route('service-requests.index', ['type' => $type->name]) }}"
                                    class="flex items-center gap-3 group px-3 py-2 text-sm font-semibold rounded-lg transition-all {{ request('type') == $type->name ? 'text-purple-700 bg-purple-50 dark:bg-purple-900/40 dark:text-purple-300' : 'text-gray-500 hover:text-purple-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-purple-400 dark:hover:bg-gray-800' }}">
                                    <i
                                        class="fas {{ $type->icon ?? 'fa-church' }} w-4 text-center {{ request('type') == $type->name ? 'text-purple-500 dark:text-purple-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-purple-400' }}"></i>
                                    {{ $type->name }}
                                </a>
                            @endforeach

                        </div>
                    </div>
                @endif

                @if(Auth::user()->hasModule('scheduling'))
                    <a href="{{ route('schedules') }}"
                        class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ Str::startsWith($currentRoute, 'schedules') ? 'ring-1 ring-purple-500/20 border-purple-200 text-purple-700 dark:bg-purple-900/20 dark:border-purple-700/50 dark:text-purple-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ Str::startsWith($currentRoute, 'schedules') ? 'bg-purple-100 text-purple-600 dark:bg-purple-800 dark:text-purple-200' : 'bg-gray-100 text-gray-500 group-hover:bg-purple-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                            <i class="fas fa-calendar-alt text-sm"></i>
                        </div>
                        <span class="tracking-wide text-[15px]">Schedule</span>
                    </a>
                @endif

                @if(Auth::user()->hasModule('service_records'))
                    <a href="{{ route('sacraments') }}"
                        class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ Str::startsWith($currentRoute, 'sacraments') ? 'ring-1 ring-purple-500/20 border-purple-200 text-purple-700 dark:bg-purple-900/20 dark:border-purple-700/50 dark:text-purple-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ Str::startsWith($currentRoute, 'sacraments') ? 'bg-purple-100 text-purple-600 dark:bg-purple-800 dark:text-purple-200' : 'bg-gray-100 text-gray-500 group-hover:bg-purple-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                            <i class="fas fa-book-open text-sm"></i>
                        </div>
                        <span class="tracking-wide text-[15px]">Services History</span>
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- FINANCE -->
    @if(Auth::user()->hasModule('collections') || Auth::user()->hasModule('donations') || Auth::user()->hasModule('services_fees'))
        <div class="px-2 mt-6 space-y-2">
            <div class="px-2 mb-3 flex items-center gap-3">
                <div class="w-1.5 h-4 bg-green-500 rounded-full"></div>
                <span class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Finance</span>
            </div>

            <div class="space-y-2">
                @if(Auth::user()->hasModule('collections'))
                    <a href="{{ route('collections') }}"
                        class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ Str::startsWith($currentRoute, 'collections') ? 'ring-1 ring-green-500/20 border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-700/50 dark:text-green-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ Str::startsWith($currentRoute, 'collections') ? 'bg-green-100 text-green-600 dark:bg-green-800 dark:text-green-200' : 'bg-gray-100 text-gray-500 group-hover:bg-green-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                            <i class="fas fa-hand-holding-usd text-sm"></i>
                        </div>
                        <span class="tracking-wide text-[15px]">Collections</span>
                    </a>
                @endif

                @if(Auth::user()->hasModule('donations'))
                    <a href="{{ route('donations') }}"
                        class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ Str::startsWith($currentRoute, 'donations') && !request()->has('type') ? 'ring-1 ring-green-500/20 border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-700/50 dark:text-green-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ Str::startsWith($currentRoute, 'donations') && !request()->has('type') ? 'bg-green-100 text-green-600 dark:bg-green-800 dark:text-green-200' : 'bg-gray-100 text-gray-500 group-hover:bg-green-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                            <i class="fas fa-hand-holding-heart text-sm"></i>
                        </div>
                        <span class="tracking-wide text-[15px]">Donations</span>
                    </a>
                @endif

                @if(Auth::user()->hasModule('services_fees'))
                    <a href="{{ route('donations', ['type' => 'fee']) }}"
                        class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ request()->get('type') == 'fee' ? 'ring-1 ring-green-500/20 border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-700/50 dark:text-green-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->get('type') == 'fee' ? 'bg-green-100 text-green-600 dark:bg-green-800 dark:text-green-200' : 'bg-gray-100 text-gray-500 group-hover:bg-green-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                            <i class="fas fa-file-invoice-dollar text-sm"></i>
                        </div>
                        <span class="tracking-wide text-[15px]">Services Fees</span>
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- ADMINISTRATION -->
    @if(Auth::user()->hasModule('system_settings') || Auth::user()->hasModule('system_roles') || Auth::user()->hasModule('user_accounts') || Auth::user()->hasModule('reports') || Auth::user()->hasModule('audit_trail'))
        <div class="px-2 mt-6 space-y-2 mb-8">
            <div class="px-2 mb-3 flex items-center gap-3">
                <div class="w-1.5 h-4 bg-orange-500 rounded-full"></div>
                <span class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Admin</span>
            </div>

            <div class="space-y-2">
                @if(Auth::user()->hasModule('system_settings'))
                    <a href="{{ route('system-settings.index') }}"
                        class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ Str::startsWith($currentRoute, 'system-settings') ? 'ring-1 ring-orange-500/20 border-orange-200 text-orange-700 dark:bg-orange-900/20 dark:border-orange-700/50 dark:text-orange-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ Str::startsWith($currentRoute, 'system-settings') ? 'bg-orange-100 text-orange-600 dark:bg-orange-800 dark:text-orange-200' : 'bg-gray-100 text-gray-500 group-hover:bg-orange-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                            <i class="fas fa-cogs text-sm"></i>
                        </div>
                        <span class="tracking-wide text-[15px]">System Settings</span>
                    </a>
                @endif

                @if(Auth::user()->hasModule('system_roles'))
                    <a href="{{ route('roles.index') }}"
                        class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ Str::startsWith($currentRoute, 'roles.') ? 'ring-1 ring-orange-500/20 border-orange-200 text-orange-700 dark:bg-orange-900/20 dark:border-orange-700/50 dark:text-orange-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ Str::startsWith($currentRoute, 'roles.') ? 'bg-orange-100 text-orange-600 dark:bg-orange-800 dark:text-orange-200' : 'bg-gray-100 text-gray-500 group-hover:bg-orange-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                            <i class="fas fa-user-shield text-sm"></i>
                        </div>
                        <span class="tracking-wide text-[15px]">System Roles</span>
                    </a>
                @endif

                @if(Auth::user()->hasModule('user_accounts'))
                    <a href="{{ route('users') }}"
                        class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ Str::startsWith($currentRoute, 'users') ? 'ring-1 ring-orange-500/20 border-orange-200 text-orange-700 dark:bg-orange-900/20 dark:border-orange-700/50 dark:text-orange-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ Str::startsWith($currentRoute, 'users') ? 'bg-orange-100 text-orange-600 dark:bg-orange-800 dark:text-orange-200' : 'bg-gray-100 text-gray-500 group-hover:bg-orange-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                            <i class="fas fa-users text-sm"></i>
                        </div>
                        <span class="tracking-wide text-[15px]">User Accounts</span>
                    </a>
                @endif

                @if(Auth::user()->hasModule('reports'))
                    <a href="{{ route('reports') }}"
                        class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ Str::startsWith($currentRoute, 'reports') ? 'ring-1 ring-orange-500/20 border-orange-200 text-orange-700 dark:bg-orange-900/20 dark:border-orange-700/50 dark:text-orange-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ Str::startsWith($currentRoute, 'reports') ? 'bg-orange-100 text-orange-600 dark:bg-orange-800 dark:text-orange-200' : 'bg-gray-100 text-gray-500 group-hover:bg-orange-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                            <i class="fas fa-chart-pie text-sm"></i>
                        </div>
                        <span class="tracking-wide text-[15px]">Reports</span>
                    </a>
                @endif

                @if(Auth::user()->hasModule('audit_trail'))
                    <a href="{{ route('audit-trail') }}"
                        class="flex items-center gap-4 px-4 py-3.5 bg-white border border-gray-100 shadow-sm {{ Str::startsWith($currentRoute, 'audit-trail') ? 'ring-1 ring-orange-500/20 border-orange-200 text-orange-700 dark:bg-orange-900/20 dark:border-orange-700/50 dark:text-orange-300' : 'text-gray-700 hover:border-gray-300 hover:shadow-md dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600' }} rounded-xl font-bold transition-all group duration-200">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ Str::startsWith($currentRoute, 'audit-trail') ? 'bg-orange-100 text-orange-600 dark:bg-orange-800 dark:text-orange-200' : 'bg-gray-100 text-gray-500 group-hover:bg-orange-500 group-hover:text-white dark:bg-gray-700 dark:text-gray-400 transition-colors' }}">
                            <i class="fas fa-list-ul text-sm"></i>
                        </div>
                        <span class="tracking-wide text-[15px]">Activity Logs</span>
                    </a>
                @endif
            </div>
        </div>
    @endif

</nav>

<div class="p-6 border-t border-gray-100 dark:border-gray-800 space-y-3 bg-gray-50/50 dark:bg-gray-900">
    <button type="button" @click.prevent="toggleTheme()"
        class="w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-primary dark:hover:border-primary text-gray-600 dark:text-gray-300 rounded-xl transition-all shadow-sm group">
        <div class="flex items-center gap-3">
            <div class="relative w-5 h-5 flex items-center justify-center">
                <i class="fas fa-sun text-yellow-500 absolute transition-all duration-300 transform"
                    :class="darkMode ? '-rotate-90 scale-0 opacity-0' : 'rotate-0 scale-100'"></i>
                <i class="fas fa-moon text-blue-500 absolute transition-all duration-300 transform"
                    :class="darkMode ? 'rotate-0 scale-100 opacity-100' : 'rotate-90 scale-0 opacity-0'"></i>
            </div>
            <span class="text-[15px] font-medium">Appearance</span>
        </div>
        <div class="w-8 h-4 bg-gray-200 dark:bg-gray-600 rounded-full relative transition-colors">
            <div class="absolute w-4 h-4 bg-white rounded-full shadow-md transform transition-transform duration-300 left-0"
                :class="darkMode ? 'translate-x-full' : ''"></div>
        </div>
    </button>

    <form action="{{ route('logout') }}" method="POST"
        onsubmit="event.preventDefault(); const form = this; showConfirm('Logout System', 'Are you sure you want to end your current session?', 'bg-red-600 hover:bg-red-700', () => form.submit(), 'Logout')">
        @csrf
        <button type="submit"
            class="w-full flex items-center justify-start gap-3 px-4 py-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors text-[15px] font-bold border border-transparent hover:border-red-100 dark:hover:border-red-900/30 group">
            <i class="fas fa-sign-out-alt w-5 text-center text-lg group-hover:scale-110 transition-transform"></i>
            <span class="tracking-wide">Logout</span>
        </button>
    </form>
</div>