<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $global_settings['system_short_name'] ?? 'IFI CMS' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('assets/js/tailwind-config.js') }}"></script>
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
    <link rel="icon" href="{{ isset($global_settings['church_logo']) ? asset('uploads/' . $global_settings['church_logo']) : asset('assets/img/logo.png') }}" type="image/x-icon">
</head>

<body
    class="bg-gray-50 dark:bg-gray-900 min-h-screen w-full m-0 p-0 font-poppins text-gray-800 dark:text-gray-200 transition-colors duration-300">
    <div class="w-full min-h-screen flex flex-col lg:flex-row">
        <!-- Branding Side (Left) -->
        <div class="relative w-full h-72 lg:h-auto lg:w-[60%] overflow-hidden text-white block">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img src="{{ isset($global_settings['login_background']) ? asset('uploads/' . $global_settings['login_background']) : asset('assets/img/login-bg.jpg') }}"
                    alt="Church Background"
                    class="w-full h-full object-cover transform scale-105 transition-transform duration-[20s] hover:scale-110">
                <!-- Lightened Gradient for better picture visibility while keeping text readable -->
                <div class="absolute inset-0 bg-gradient-to-br from-blue-900/40 via-transparent to-black/40">
                </div>
            </div>

            <!-- Content Container -->
            <div class="relative z-10 w-full h-full p-6 lg:p-10 flex flex-col justify-start lg:block">
                <!-- Header Section (Logo + Text) Horizontal -->
                <div
                    class="absolute top-8 left-0 w-full flex flex-row items-center justify-center gap-4 z-10 pointer-events-none px-4 lg:px-6 text-shadow">
                    <img src="{{ isset($global_settings['church_logo']) ? asset('uploads/' . $global_settings['church_logo']) : asset('assets/img/logo.png') }}"
                        alt="Church Logo"
                        class="w-16 lg:w-20 h-auto object-contain drop-shadow-lg filter brightness-110 transform hover:scale-105 transition-transform pointer-events-auto shrink-0">
                    
                    <div class="flex flex-col items-center">
                        <h1
                            class="text-2xl lg:text-3xl font-bold tracking-tight drop-shadow-xl font-serif uppercase text-white/95 mb-0.5 leading-tight text-center">
                            {!! nl2br(e($global_settings['system_name'] ?? 'Iglesia Filipina Independiente')) !!}
                        </h1>
                        <p class="text-base lg:text-lg font-bold tracking-wide text-white uppercase text-center">
                            {{ $global_settings['parish_name'] ?? 'Parokya ng San Geronimo' }}
                        </p>
                    </div>
                </div>

                <!-- Footer Text (Optional, keeping it at bottom) -->
                <!-- Footer Text -->
                <div
                    class="absolute bottom-4 lg:bottom-10 left-0 w-full text-center text-[8px] lg:text-[10px] opacity-40 font-light tracking-widest uppercase hidden lg:block">
                    &copy; {{ date('Y') }} IFI Morong. Secure System.
                </div>
            </div>
        </div>

        <!-- Login Form Side (Right) -->
        <div
            class="w-full lg:w-[40%] bg-white dark:bg-gray-900 flex flex-col justify-center px-6 md:px-12 lg:px-24 py-10 shadow-2xl z-20 relative font-poppins transition-colors duration-300">
            <div class="w-full max-w-md mx-auto">
                <div class="mb-12">
                    <span class="text-primary font-bold uppercase tracking-widest text-base mb-2 block">
                        {{ $global_settings['system_tagline'] ?? 'Church Management System' }}
                    </span>
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-3 tracking-tight">Welcome</h2>
                    <p class="text-base text-gray-500 dark:text-gray-400 leading-relaxed">Please sign in with your
                        credentials to access the
                        secure portal.</p>
                </div>

                @if(session('error') || $errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 text-red-600 dark:text-red-400 p-4 mb-8 text-base rounded-r-lg flex items-center gap-4"
                        role="alert">
                        <div
                            class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/50 text-red-500 flex items-center justify-center shrink-0">
                            <i class="fas fa-exclamation text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold">Authentication Failed</p>
                            @if(session('error'))
                                <p class="text-sm mt-0.5 opacity-90">{{ session('error') }}</p>
                            @endif
                            @foreach($errors->all() as $error)
                                <p class="text-sm mt-0.5 opacity-90">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-6">
                    @csrf
                    <div class="relative group">
                        <label
                            class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5 uppercase tracking-wider group-focus-within:text-primary dark:group-focus-within:text-primary transition-colors">Email
                            Address</label>
                        <div class="relative">
                            <i
                                class="fas fa-envelope absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-300 dark:text-gray-600 text-lg transition-colors group-focus-within:text-primary"></i>
                            <input type="email" name="email"
                                class="w-full pl-14 pr-6 py-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:border-primary dark:focus:border-primary focus:ring-4 focus:ring-primary/10 dark:focus:ring-primary/20 transition-all text-base font-medium placeholder-gray-300 dark:placeholder-gray-600 text-gray-700 dark:text-gray-200 shadow-inner dark:shadow-none"
                                placeholder="example@gmail.com" required>
                        </div>
                    </div>

                    <div class="relative group">
                        <label
                            class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5 uppercase tracking-wider group-focus-within:text-primary dark:group-focus-within:text-primary transition-colors">Password</label>
                        <div class="relative">
                            <i
                                class="fas fa-lock absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-300 dark:text-gray-600 text-lg transition-colors group-focus-within:text-primary"></i>
                            <input type="password" name="password" id="password"
                                class="w-full pl-14 pr-12 py-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:border-primary dark:focus:border-primary focus:ring-4 focus:ring-primary/10 dark:focus:ring-primary/20 transition-all text-base font-medium placeholder-gray-300 dark:placeholder-gray-600 text-gray-700 dark:text-gray-200 shadow-inner dark:shadow-none"
                                placeholder="••••••••" required>
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-300 dark:text-gray-500 hover:text-primary dark:hover:text-primary transition-colors focus:outline-none">
                                <i class="fas fa-eye text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end items-center pt-2">
                        <a href="{{ route('password.request') }}"
                            class="text-base font-semibold text-gray-400 dark:text-gray-500 hover:text-primary dark:hover:text-primary transition-colors">Forgot
                            Password?</a>
                    </div>

                    <button type="submit" id="btnSubmit" onclick="showLoading()"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:to-blue-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 transition-all text-base uppercase tracking-wider flex justify-center items-center gap-3">
                        <span>Sign In</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>


            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const icon = event.currentTarget.querySelector('i');
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function showLoading() {
            const btn = document.getElementById('btnSubmit');
            const form = document.getElementById('loginForm');
            if (form.checkValidity()) {
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
                btn.classList.add('opacity-80', 'cursor-not-allowed', 'translate-y-0');
            }
        }
    </script>
</body>

</html>