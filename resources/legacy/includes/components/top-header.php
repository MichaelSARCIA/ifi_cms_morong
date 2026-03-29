<?php
// Default values if not set
$page_title = isset($page_title) ? $page_title : 'Dashboard';
$page_subtitle = isset($page_subtitle) ? $page_subtitle : 'Welcome back!';
$user_name = isset($user_name) ? $user_name : (isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User');
$profile_pic = isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic']) ? '../../uploads/' . $_SESSION['profile_pic'] : '../../uploads/default_avatar.jpg';

// Theme color based on role
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Staff';
$theme_color = 'text-primary';
$theme_bg = 'bg-primary';
$theme_border = 'focus:border-primary';
$theme_ring = 'focus:ring-primary/10';

if ($role === 'Treasurer') {
    $theme_color = 'text-green-600';
    $theme_bg = 'bg-green-600';
    $theme_border = 'focus:border-green-600';
    $theme_ring = 'focus:ring-green-50';
} elseif ($role === 'Priest') {
    $theme_color = 'text-purple-600';
    $theme_bg = 'bg-purple-600';
    $theme_border = 'focus:border-purple-600';
    $theme_ring = 'focus:ring-purple-50';
}
?>

<header
    class="h-24 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200/50 dark:border-gray-800/50 flex items-center justify-between px-8 shadow-sm transition-colors duration-200 sticky top-0 z-40">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" onclick="toggleSidebar()"
        class="fixed inset-0 bg-black/50 z-40 hidden backdrop-blur-sm transition-opacity opacity-0"></div>

    <!-- Left: Title & Subtitle -->
    <div class="flex items-center gap-4">
        <button onclick="toggleSidebar()"
            class="md:hidden p-2 -ml-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <div>
            <h1
                class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white leading-tight font-serif tracking-tight truncate max-w-[200px] md:max-w-none">
                <?php echo $page_title; ?>
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide mt-1 hidden sm:block">
                <?php echo $page_subtitle; ?>
            </p>
        </div>
    </div>

    <!-- Right: Actions & User -->
    <div class="flex items-center gap-6">

        <!-- Search Bar -->
        <div class="hidden md:block relative group">
            <span
                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:<?php echo $theme_color; ?> transition-colors">
                <i class="fas fa-search text-sm"></i>
            </span>
            <input type="text"
                class="w-full bg-gray-50 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 border border-transparent dark:border-gray-700 rounded-2xl pl-11 pr-4 py-3 focus:outline-none focus:bg-white dark:focus:bg-gray-900 <?php echo $theme_border; ?> focus:ring-4 <?php echo $theme_ring; ?> transition-all placeholder-gray-400 w-72 shadow-inner"
                placeholder="Search...">
        </div>

        <!-- Date Display -->
        <div
            class="hidden lg:flex items-center gap-3 px-4 py-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-300 shadow-sm">
            <div class="p-1.5 rounded-lg bg-gray-50 dark:bg-gray-700 text-green-600">
                <i class="far fa-calendar-alt"></i>
            </div>
            <span>
                <?php echo date("F d, Y"); ?>
            </span>
        </div>

        <!-- Notification Bell -->
        <button
            class="w-11 h-11 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:<?php echo $theme_color; ?> hover:shadow-md transition-all relative group">
            <i class="far fa-bell text-lg group-hover:animate-swing"></i>
            <span
                class="absolute top-3 right-3 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-gray-800"></span>
        </button>

        <!-- User Profile (No Dropdown) -->
        <div class="flex items-center gap-4 pl-2">
            <div class="hidden sm:block text-right">
                <p class="text-sm font-bold text-gray-800 dark:text-white leading-tight">
                    <?php echo $user_name; ?>
                </p>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mt-0.5">
                    <?php echo $role; ?>
                </p>
            </div>
            <img src="<?php echo $profile_pic; ?>" onerror="this.src='../../uploads/default_avatar.jpg'" alt="User"
                class="w-11 h-11 rounded-full border-2 border-white dark:border-gray-700 object-cover shadow-md ring-2 ring-gray-100 dark:ring-gray-800">
        </div>
    </div>
</header>

<script>
    // Theme Toggle Logic
    const html = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');

    // Check for saved preference or user's system preference
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }

    function toggleTheme() {
        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            localStorage.theme = 'light';
        } else {
            html.classList.add('dark');
            localStorage.theme = 'dark';
        }
    }

    // Sidebar Toggle Logic
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        if (sidebar.classList.contains('-translate-x-full')) {
            // Open Sidebar
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
            }, 10);
        } else {
            // Close Sidebar
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        }
    }
</script>