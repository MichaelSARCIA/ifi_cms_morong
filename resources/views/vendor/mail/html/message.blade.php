<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            <img src="{{ asset('assets/img/logo.png') }}" class="logo" alt="IFI CMS Logo"
                style="max-height: 75px; width: auto; margin: 0 auto; display: block;">
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {!! $slot !!}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {!! $subcopy !!}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} Iglesia Filipina Independiente - Morong. All rights reserved.
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>