@extends('layouts.app')

@section('title', 'My Profile')
@section('page_title', 'My Profile')
@section('page_subtitle', 'Manage your account settings')
@section('role_label', Auth::user()->role)

@section('content')
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <div class="max-w-4xl mx-auto w-full overflow-y-auto custom-scrollbar p-1">

            <div
                class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="p-8">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-3">
                        <i class="fas fa-user-circle text-primary text-2xl"></i> Profile & Account Settings
                    </h3>

                    <div class="flex flex-col md:flex-row gap-8">

                        <!-- LEFT: Profile Picture -->
                        <div
                            class="md:w-1/3 flex flex-col items-center border-b md:border-b-0 md:border-r border-gray-100 dark:border-gray-700 pb-8 md:pb-0 md:pr-8">
                            <form action="{{ route('profile.update-photo') }}" method="POST" enctype="multipart/form-data"
                                id="photoForm" class="w-full text-center">
                                @csrf
                                <div class="relative w-48 h-48 mx-auto mb-6 group cursor-pointer"
                                    onclick="document.getElementById('profilePicInput').click()">
                                    <div
                                        class="w-full h-full rounded-full overflow-hidden border-4 border-gray-100 dark:border-gray-700 shadow-inner group-hover:opacity-75 transition-opacity relative bg-gray-100 dark:bg-gray-800">
                                        @if(Auth::user()->profile_pic)
                                            <img src="{{ asset('uploads/' . Auth::user()->profile_pic) }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <div
                                                class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 text-gray-500 dark:text-gray-400 font-bold text-5xl">
                                                {{ Auth::user()->initials }}
                                            </div>
                                        @endif

                                        <div
                                            class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i class="fas fa-camera text-white text-3xl"></i>
                                        </div>
                                    </div>
                                    <input type="file" name="profile_pic" id="profilePicInput" class="hidden"
                                        accept="image/*" onchange="document.getElementById('photoForm').submit()">
                                </div>

                                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">{{ Auth::user()->name }}
                                </h2>
                                <span
                                    class="inline-block px-4 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold text-sm mb-4">
                                    {{ Auth::user()->role }}
                                </span>
                                <p class="text-sm text-gray-500 dark:text-gray-400 break-all mb-4">
                                    <i class="fas fa-envelope mr-1"></i> {{ Auth::user()->email }}
                                </p>
                            </form>

                            @if(Auth::user()->profile_pic)
                                <form action="{{ route('profile.clear-photo') }}" method="POST"
                                    onsubmit="event.preventDefault(); showConfirm('Remove Photo', 'Are you sure you want to remove your profile picture and revert to initials?', 'bg-red-600 hover:bg-red-700', () => this.submit(), 'Remove')">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-600 transition-colors uppercase tracking-widest">
                                        <i class="fas fa-trash-alt mr-1"></i> Remove Photo
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- RIGHT: Details Form -->
                        <div class="flex-1">
                            <form action="{{ route('profile.update-account') }}" method="POST" class="space-y-6">
                                @csrf

                                <div>
                                    <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Display Name</label>
                                    <input type="text" name="name" value="{{ Auth::user()->name }}" required
                                        class="w-full px-5 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-medium text-gray-800 dark:text-white">
                                </div>

                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <h4
                                        class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                                        <i class="fas fa-lock text-gray-400"></i> Security
                                    </h4>

                                    <div class="grid grid-cols-1 gap-6">
                                        <div class="mb-2">
                                            <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Current Password
                                                <span class="text-xs text-amber-500 font-normal lowercase">(Required to change password)</span></label>
                                            <input type="password" name="current_password" placeholder="Enter current password"
                                                class="w-full px-5 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-800 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-500 uppercase mb-2">New Password
                                                <span class="text-xs text-gray-400 font-normal lowercase">(Optional)</span></label>
                                            <input type="password" name="password" placeholder="Leave blank to keep current"
                                                class="w-full px-5 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-800 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Confirm New
                                                Password</label>
                                            <input type="password" name="password_confirmation"
                                                placeholder="Confirm new password"
                                                class="w-full px-5 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-800 dark:text-white">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-6 mt-2">
                                    <button type="submit"
                                        class="bg-gradient-to-r from-primary to-blue-600 hover:from-blue-600 hover:to-primary text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5 w-full md:w-auto">
                                        <i class="fas fa-save mr-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection