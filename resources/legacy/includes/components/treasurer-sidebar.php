<aside id="mainSidebar"
    class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col transition-transform duration-300 ease-in-out transform -translate-x-full md:translate-x-0 md:static md:inset-auto">
    <div class="h-24 flex items-center px-8 border-b border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-4 text-primary relative group">
            <div
                class="absolute -inset-2 bg-green-50 dark:bg-green-900/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            </div>
            <img src="../../assets/img/logo.png" alt="IFI Logo" class="h-10 w-auto relative z-10 drop-shadow-sm">
            <div class="relative z-10">
                <h2 class="font-bold text-base leading-tight text-gray-800 dark:text-white tracking-tight font-serif">
                    Iglesia Filipina<br>Independiente</h2>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mt-0.5 block">Finance</span>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-8 px-4 space-y-2 custom-scrollbar">
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>

        <a href="dashboard.php"
            class="flex items-center gap-4 px-5 py-3.5 <?php echo $current_page == 'dashboard.php' ? 'bg-gradient-to-r from-green-600 to-green-500 text-white shadow-lg shadow-green-500/30' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-green-600 dark:text-gray-400 dark:hover:text-white'; ?> rounded-xl font-medium transition-all group duration-200">
            <i
                class="fas fa-th-large w-5 text-center text-lg <?php echo $current_page == 'dashboard.php' ? 'text-white' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="tracking-wide text-sm">Dashboard</span>
        </a>

        <a href="collections.php"
            class="flex items-center gap-4 px-5 py-3.5 <?php echo $current_page == 'collections.php' ? 'bg-gradient-to-r from-green-600 to-green-500 text-white shadow-lg shadow-green-500/30' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-green-600 dark:text-gray-400 dark:hover:text-white'; ?> rounded-xl font-medium transition-all group duration-200">
            <i
                class="fas fa-hand-holding-usd w-5 text-center text-lg <?php echo $current_page == 'collections.php' ? 'text-white' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="tracking-wide text-sm">Collections</span>
        </a>

        <a href="donations.php"
            class="flex items-center gap-4 px-5 py-3.5 <?php echo $current_page == 'donations.php' ? 'bg-gradient-to-r from-green-600 to-green-500 text-white shadow-lg shadow-green-500/30' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-green-600 dark:text-gray-400 dark:hover:text-white'; ?> rounded-xl font-medium transition-all group duration-200">
            <i
                class="fas fa-heart w-5 text-center text-lg <?php echo $current_page == 'donations.php' ? 'text-white' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="tracking-wide text-sm">Donations</span>
        </a>

        <a href="reports.php"
            class="flex items-center gap-4 px-5 py-3.5 <?php echo $current_page == 'reports.php' ? 'bg-gradient-to-r from-green-600 to-green-500 text-white shadow-lg shadow-green-500/30' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-green-600 dark:text-gray-400 dark:hover:text-white'; ?> rounded-xl font-medium transition-all group duration-200">
            <i
                class="fas fa-file-invoice-dollar w-5 text-center text-lg <?php echo $current_page == 'reports.php' ? 'text-white' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="tracking-wide text-sm">Financial Reports</span>
        </a>

        <a href="settings.php"
            class="flex items-center gap-4 px-5 py-3.5 <?php echo $current_page == 'settings.php' ? 'bg-gradient-to-r from-green-600 to-green-500 text-white shadow-lg shadow-green-500/30' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-green-600 dark:text-gray-400 dark:hover:text-white'; ?> rounded-xl font-medium transition-all group duration-200">
            <i
                class="fas fa-cog w-5 text-center text-lg <?php echo $current_page == 'settings.php' ? 'text-white' : 'group-hover:scale-110 transition-transform'; ?>"></i>
            <span class="tracking-wide text-sm">Settings</span>
        </a>
    </nav>

    <div class="p-6 border-t border-gray-100 dark:border-gray-800 space-y-3 bg-gray-50/50 dark:bg-gray-900/50">
        <!-- Appearance Toggle Button -->
        <button type="button" onclick="toggleTheme()"
            class="w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-green-600 dark:hover:border-green-600 text-gray-600 dark:text-gray-300 rounded-xl transition-all shadow-sm group">
            <div class="flex items-center gap-3">
                <div class="relative w-5 h-5 flex items-center justify-center">
                    <i
                        class="fas fa-sun text-yellow-500 absolute transition-all duration-300 transform rotate-0 scale-100 dark:-rotate-90 dark:scale-0 dark:opacity-0"></i>
                    <i
                        class="fas fa-moon text-blue-500 absolute transition-all duration-300 transform rotate-90 scale-0 opacity-0 dark:rotate-0 dark:scale-100 dark:opacity-100"></i>
                </div>
                <span class="text-sm font-medium">Appearance</span>
            </div>
            <div class="w-8 h-4 bg-gray-200 dark:bg-gray-600 rounded-full relative transition-colors">
                <div
                    class="absolute w-4 h-4 bg-white rounded-full shadow-md transform transition-transform duration-300 left-0 dark:translate-x-full">
                </div>
            </div>
        </button>

        <button onclick="confirmLogout()"
            class="w-full flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors text-sm font-bold justify-center border border-transparent hover:border-red-100 dark:hover:border-red-900/30">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>

    <script>
        // Init theme check
        const html = document.documentElement;
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        function toggleTheme() {
            // Add transition class
            html.classList.add('smooth-transition');

            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                html.classList.add('dark');
                localStorage.theme = 'dark';
            }

            // Remove transition class after animation finishes to prevent performance hit on resize
            setTimeout(() => {
                html.classList.remove('smooth-transition');
            }, 500);
        }


    </script>
</aside>