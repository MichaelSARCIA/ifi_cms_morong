<header
    class="h-20 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-8 shadow-sm transition-colors duration-200 sticky top-0 z-40">
    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen"
            class="md:hidden p-2 -ml-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <div>
            <h1
                class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white leading-tight tracking-tight truncate max-w-[200px] md:max-w-none">
                @yield('page_title', 'Dashboard')
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-wide mt-1 hidden sm:block">
                @yield('page_subtitle', 'Welcome back!')
            </p>
        </div>
    </div>

    <div class="flex items-center gap-6">

        <!-- Search Bar Removed per request -->

        <div
            class="hidden lg:flex items-center gap-3 px-4 py-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 text-sm font-bold text-gray-600 dark:text-gray-300 shadow-sm">
            <div class="p-1.5 rounded-lg bg-gray-50 dark:bg-gray-700 text-green-600">
                <i class="far fa-calendar-alt"></i>
            </div>
            <span>{{ date('F d, Y') }}</span>
        </div>

        <!-- Notification Bell -->
        <div class="relative" x-data="{ 
                        notificationsOpen: false, 
                        notifications: [], 
                        unreadCount: 0,
                        async fetchNotifications() {
                            try {
                                const res = await fetch('{{ route('notifications') }}');
                                const data = await res.json();
                                this.notifications = data;
                                
                                // Calculate unread count based on is_read flag from DB
                                this.unreadCount = data.filter(n => !n.is_read).length;
                                
                                if (this.notificationsOpen && this.unreadCount > 0) {
                                     this.markAsRead();
                                }
                            } catch(e) { console.error(e); }
                        },
                        async markAsRead() {
                            if (this.unreadCount > 0) {
                                try {
                                    await fetch('{{ route('notifications.read') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    });
                                    this.unreadCount = 0;
                                    this.notifications = this.notifications.map(n => ({...n, is_read: true}));
                                } catch(e) { console.error(e); }
                            }
                        },
                        init() {
                            setTimeout(() => this.fetchNotifications(), 1000); // Defer init fetch to prioritize LCP
                            setInterval(() => this.fetchNotifications(), 30000); // Poll every 30s instead of 60s
                        }
                    }"
                    @notification-updated.window="fetchNotifications()">
            <button @click="notificationsOpen = !notificationsOpen; if(notificationsOpen) markAsRead()"
                class="w-11 h-11 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-primary hover:shadow-md transition-all relative group">
                <i class="far fa-bell text-lg group-hover:animate-swing"></i>
                <span x-show="unreadCount > 0" x-text="unreadCount"
                    class="absolute top-3 right-3 flex items-center justify-center min-w-[1.25rem] h-5 px-1 bg-red-500 text-white text-xs font-bold rounded-full border-2 border-white dark:border-gray-800 -mt-2 -mr-2 shadow-sm"></span>
            </button>

            <!-- Dropdown -->
            <div x-show="notificationsOpen" @click.away="notificationsOpen = false"
                class="absolute top-full right-0 mt-4 w-96 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 z-50 overflow-hidden transform origin-top-right transition-all"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" style="display: none;">

                <div
                    class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 dark:text-white">Notifications</h3>
                    <button @click="fetchNotifications()" class="text-sm text-primary hover:underline">Refresh</button>
                </div>

                <div class="max-h-[400px] overflow-y-auto custom-scrollbar">
                    <template x-if="notifications.length === 0">
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="far fa-bell-slash text-2xl mb-2"></i>
                            <p class="text-sm">No new notifications</p>
                        </div>
                    </template>

                    <template x-for="notif in notifications" :key="notif.id">
                        <a :href="notif.link"
                            class="p-4 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors flex gap-4 cursor-pointer block"
                            :class="{'bg-blue-50/30 dark:bg-blue-900/10': !notif.is_read}">
                            
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 text-white shadow-sm" :class="notif.color || 'bg-blue-500'">
                                <i class="fas" :class="notif.icon || 'fa-bell'"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 dark:text-gray-200 leading-snug">
                                    <span class="font-bold" x-text="notif.action"></span>
                                    <br>
                                    <span class="text-gray-600 dark:text-gray-400 text-sm" x-text="notif.details"></span>
                                </p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-1.5 py-0.5 rounded font-medium"
                                        x-text="notif.time_ago"></span>
                                </div>
                            </div>
                            
                            <!-- Unread dot -->
                            <div x-show="!notif.is_read" class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0 shadow-sm"></div>
                        </a>
                    </template>
            </div>

            <div class="p-2 bg-gray-50 dark:bg-gray-900 text-center">
                <a href="{{ route('notifications.index') }}" class="text-sm font-bold text-primary hover:text-blue-700">View All
                    Notifications</a>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4 pl-2 relative" x-data="{ open: false }">
        <button @click="open = !open" @click.away="open = false"
            class="dropdown-btn !pr-4 flex items-center gap-3 group focus:outline-none transition-all">
            <div class="hidden sm:block text-right">
                <p
                    class="text-sm font-bold text-gray-800 dark:text-white leading-tight group-hover:text-primary transition-colors">
                    {{ Auth::user()->name ?? 'User' }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mt-0.5">
                    {{ Auth::user()->role ?? 'Staff' }}
                </p>
            </div>
            <div class="relative">
                <div
                    class="w-11 h-11 rounded-full overflow-hidden border-2 border-white dark:border-gray-700 shadow-md ring-2 ring-gray-100 dark:ring-gray-800 group-hover:ring-primary/50 transition-all bg-white dark:bg-gray-800">
                    @if(Auth::user()->profile_pic)
                        <img src="{{ asset('uploads/' . Auth::user()->profile_pic) }}" alt="User"
                            class="w-full h-full object-cover">
                    @else
                        <div
                            class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary to-blue-600 text-white font-bold text-sm">
                            {{ Auth::user()->initials }}
                        </div>
                    @endif
                </div>
            </div>
        </button>

        <!-- Profile Dropdown -->
        <div x-show="open"
            class="absolute top-full right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden z-50 transform origin-top-right transition-all"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" style="display: none;">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 sm:hidden">
                <p class="text-sm font-bold text-gray-800 dark:text-white">{{ Auth::user()->name }}</p>
                <p class="text-sm text-gray-500">{{ Auth::user()->role }}</p>
            </div>
            <a href="{{ route('profile') }}"
                class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium border-b border-gray-100 dark:border-gray-700">
                <i class="fas fa-user-circle mr-2 text-primary"></i> My Profile
            </a>
            <form action="{{ route('logout') }}" method="POST"
                onsubmit="event.preventDefault(); const form = this; showConfirm('Logout System', 'Are you sure you want to end your current session?', 'bg-red-600 hover:bg-red-700', () => form.submit(), 'Logout')">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-start gap-3 px-4 py-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 font-bold transition-colors text-sm group">
                    <i class="fas fa-sign-out-alt w-5 text-center text-lg group-hover:scale-110 transition-transform"></i>
                    <span class="tracking-wide">Logout</span>
                </button>
            </form>
        </div>
    </div>
    </div>
</header>