<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - {{ $global_settings['system_short_name'] ?? 'IFI CMS' }}</title>
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
        }
    </style>
    <link rel="icon" href="{{ isset($global_settings['church_logo']) ? asset('uploads/' . $global_settings['church_logo']) : asset('assets/img/logo.png') }}" type="image/x-icon">
</head>

<body class="bg-gray-900 min-h-screen w-full m-0 p-0 font-poppins text-gray-800 relative overflow-hidden">

    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <img src="{{ isset($global_settings['login_background']) ? asset('uploads/' . $global_settings['login_background']) : asset('assets/img/login-bg.jpg') }}"
            alt="Church Background"
            class="w-full h-full object-cover transform scale-[1.1] transition-transform duration-[30s] hover:scale-125 origin-center">
        <!-- Deep Dark Overlay for Maximum Contrast -->
        <div class="absolute inset-0 bg-gradient-to-b from-gray-900/80 via-black/70 to-black/95"></div>
    </div>

    <!-- Main Content Container -->
    <div class="relative z-10 w-full min-h-screen flex flex-col items-center justify-center p-6 text-center">

        <!-- Logo -->
        <div class="mb-8 transform hover:scale-105 transition-transform duration-500 animate-fade-in-up">
            <img src="{{ isset($global_settings['church_logo']) ? asset('uploads/' . $global_settings['church_logo']) : asset('assets/img/logo.png') }}"
                alt="Church Logo"
                class="w-32 md:w-40 lg:w-48 h-auto object-contain drop-shadow-2xl mx-auto filter brightness-110">
        </div>

        <!-- System Taglines -->
        <div class="max-w-4xl mx-auto space-y-4 animate-fade-in-up" style="animation-delay: 150ms;">
            <span
                class="text-primary font-bold uppercase tracking-[0.25em] text-xs md:text-sm drop-shadow-md block mb-4">
                {{ $global_settings['parish_name'] ?? 'Parokya ng San Geronimo' }}
            </span>
            <h1
                class="text-4xl md:text-5xl lg:text-7xl font-bold tracking-tight text-white font-serif uppercase drop-shadow-[0_0_20px_rgba(0,0,0,0.8)] leading-tight">
                {!! nl2br(e($global_settings['system_name'] ?? 'Iglesia Filipina Independiente')) !!}
            </h1>
            <p
                class="text-base md:text-lg lg:text-xl text-gray-300 font-light tracking-wide max-w-2xl mx-auto mt-6 px-4">
                {{ $global_settings['system_tagline'] ?? 'Church Management System' }}
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="mt-14 flex flex-col sm:flex-row gap-4 justify-center items-center w-full max-w-md animate-fade-in-up"
            style="animation-delay: 300ms;">
            @auth
                <a href="{{ url('/dashboard') }}"
                    class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-bold text-sm uppercase tracking-wider shadow-[0_0_30px_rgba(37,99,235,0.4)] hover:shadow-[0_0_40px_rgba(37,99,235,0.6)] transition-all flex items-center justify-center gap-3 transform hover:-translate-y-1">
                    <span>Go to Dashboard</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="w-full sm:w-auto px-10 py-4 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-bold text-sm uppercase tracking-wider shadow-[0_0_30px_rgba(37,99,235,0.4)] hover:shadow-[0_0_40px_rgba(37,99,235,0.6)] transition-all flex items-center justify-center gap-3 transform hover:-translate-y-1">
                    <span>Sign In</span>
                    <i class="fas fa-sign-in-alt"></i>
                </a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                        class="w-full sm:w-auto px-10 py-4 rounded-xl glass-panel text-white font-bold text-sm uppercase tracking-wider hover:bg-white/20 transition-all flex items-center justify-center gap-3 transform hover:-translate-y-1">
                        <span>Register</span>
                    </a>
                @endif
            @endauth
        </div>

    </div>

    <!-- Footer -->
    <div class="absolute bottom-6 left-0 w-full text-center z-20 animate-fade-in-up" style="animation-delay: 450ms;">
        <p class="text-[10px] md:text-xs text-gray-500 font-light tracking-widest uppercase">
            &copy; {{ date('Y') }} {{ str_replace(["\r", "\n"], ' ', $global_settings['system_name'] ?? 'Iglesia Filipina Independiente') }}, All rights reserved.
        </p>
    </div>

</body>

</html>