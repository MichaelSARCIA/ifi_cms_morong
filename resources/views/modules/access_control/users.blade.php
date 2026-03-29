@extends('layouts.app')

@section('title', 'User Accounts')
@section('page_title', 'User Accounts')
@section('page_subtitle', 'Manage all system user accounts')

@section('content')
    <div class="flex flex-col" x-data="{ 
                                                                                        userModalOpen: {{ $errors->any() ? 'true' : 'false' }}, 
                                                                                        editMode: false, 
                                                                                        user: { role: '', working_days: [], working_hours: { start: '08:00', end: '17:00' }, max_services_per_day: 5 },
                                                                                        roles: {{ json_encode($roles) }},
                                                                                        statuses: {},
                                                                                        getStatus(id, isOnlineDefault) {
                                                                                            if (this.statuses[id]) {
                                                                                                return this.statuses[id].is_online ? 'Active' : 'Inactive';
                                                                                            }
                                                                                            return isOnlineDefault ? 'Active' : 'Inactive';
                                                                                        },
                                                                                        async fetchStatuses() {
                                                                                            try {
                                                                                                const response = await fetch('{{ route('users.status') }}');
                                                                                                const data = await response.json();
                                                                                                this.statuses = data.reduce((acc, curr) => {
                                                                                                    acc[curr.id] = curr;
                                                                                                    return acc;
                                                                                                }, {});
                                                                                            } catch (e) {
                                                                                                console.error('Failed to fetch statuses', e);
                                                                                            }
                                                                                        },
                                                                                        init() {
                                                                                            this.fetchStatuses();
                                                                                            setInterval(() => this.fetchStatuses(), 2000); 
                                                                                        }
                                                                }">

        <div
            class="flex flex-col bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden search-results-container relative" x-data="tableSearch()" @click="handlePagination">
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4 bg-gray-50 dark:bg-gray-800 shrink-0">
                <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                    <div class="flex bg-gray-200 dark:bg-gray-700 p-1 rounded-xl">
                        <a href="{{ route('users', ['tab' => 'active']) }}"
                            class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $tab === 'active' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-800 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                            Active
                        </a>
                        <a href="{{ route('users', ['tab' => 'archived']) }}"
                            class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $tab === 'archived' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-800 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                            Archived
                        </a>
                    </div>

                    <!-- Search Input -->
                    <form action="{{ route('users') }}" method="GET" @submit.prevent="submitSearch" class="search-form relative w-full md:w-64">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user..."
                            @input.debounce.300ms="submitSearch"
                            class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
                    </form>
                </div>

                @if($tab === 'active')
                    <button @click="userModalOpen = true; editMode = false; user = { role: '', working_days: [], working_hours: { start: '08:00', end: '17:00' }, max_services_per_day: 5 }"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                        <i class="fas fa-user-plus"></i> <span>Create Account</span>
                    </button>
                @endif
            </div>

            <div class="overflow-y-auto custom-scrollbar">
                @if($users->isEmpty())
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-users-slash text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">No users found</h3>
                        <p class="text-sm text-gray-500">There are no {{ $tab }} users at the moment.</p>
                    </div>
                @else
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-white dark:bg-gray-800 z-10 text-sm font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">User</th>
                                <th class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">Role & Access</th>
                                <th class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">Status</th>
                                <th class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($users as $u)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="relative">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center font-bold">
                                                    {{ substr($u->name, 0, 1) }}
                                                </div>
                                                <!-- Online Indicator Dot -->
                                                <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white dark:border-gray-800 transition-colors duration-300"
                                                    :class="getStatus({{ $u->id }}, {{ $u->is_online ? 'true' : 'false' }}) === 'Active' ? 'bg-green-500' : 'bg-gray-300'">
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-800 dark:text-gray-200">{{ $u->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $u->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $roleColor = match ($u->role) {
                                                'Admin' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                                'Priest' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                                                'Treasurer' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                                'Secretary' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                                default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-bold {{ $roleColor }}">
                                            {{ $u->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($u->deleted_at)
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-100 text-orange-700">
                                                Archived
                                            </span>
                                        @else
                                            <!-- Real-time Status Badge -->
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider transition-colors duration-300"
                                                :class="getStatus({{ $u->id }}, {{ $u->is_online ? 'true' : 'false' }}) === 'Active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                                x-text="getStatus({{ $u->id }}, {{ $u->is_online ? 'true' : 'false' }})">
                                                {{ $u->is_online ? 'Active' : 'Inactive' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            @if($tab === 'active')
                                                <button
                                                    @click="
                                                                                                                                                                                                                                                                                                                                                                                userModalOpen = true; 
                                                                                                                                                                                                                                                                                                                                                                                editMode = true; 
                                                                                                                                                                                                                                                                                                                                                                                user = { 
                                                                                                                                                                                                                                                                                                                                                                                     id: '{{ $u->id }}', 
                                                                                                                                                                                                                                                                                                                                                                                     name: '{{ addslashes($u->name) }}', 
                                                                                                                                                                                                                                                                                                                                                                                     title: '{{ addslashes($u->title ?? "") }}',
                                                                                                                                                                                                                                                                                                                                                                                     email: '{{ addslashes($u->email) }}', 
                                                                                                                                                                                                                                                                                                                                                                                     role: '{{ $u->role }}',
                                                                                                                                                                                                                                                                                                                                                                                     working_days: {{ json_encode($u->working_days ?? []) }},
                                                                                                                                                                                                                                                                                                                                                                                     working_hours: {{ json_encode($u->working_hours ?? ['start' => '08:00', 'end' => '17:00']) }},
                                                                                                                                                                                                                                                                                                                                                                                     max_services_per_day: {{ $u->max_services_per_day ?? 5 }}
                                                                                                                                                                                                                                                                                                                                                                                 }"
                                                    class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 text-blue-600 hover:bg-blue-100 transition-colors flex items-center justify-center"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                @if($u->id !== Auth::id())
                                                    <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline"
                                                        onsubmit="event.preventDefault(); showConfirm('Archive User', 'Are you sure you want to archive this user?', 'bg-orange-600 hover:bg-orange-700', () => this.submit(), 'Archive')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 hover:bg-orange-100 transition-colors flex items-center justify-center"
                                                            title="Archive">
                                                            <i class="fas fa-archive"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <form action="{{ route('users.restore', $u->id) }}" method="POST" class="inline"
                                                    onsubmit="event.preventDefault(); showConfirm('Restore User', 'Are you sure you want to restore this user?', 'bg-green-600 hover:bg-green-700', () => this.submit(), 'Restore')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 hover:bg-green-100 transition-colors flex items-center justify-center"
                                                        title="Restore">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700 shrink-0">
                {{ $users->appends(['tab' => $tab])->links() }}
            </div>
        </div>

        <!-- USER ACCOUNT MODAL -->
        <div x-show="userModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="userModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 relative animate-fade-in-up">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-xl text-gray-800 dark:text-white"
                            x-text="editMode ? 'Edit Account' : 'Create New Account'"></h3>
                        <button @click="userModalOpen = false" class="text-gray-400 hover:text-gray-600"><i
                                class="fas fa-times"></i></button>
                    </div>

                    @if ($errors->any())
                        <div
                            class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/50">
                            <ul class="text-sm text-red-600 font-medium list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form :action="editMode ? '{{ url('users') }}/' + user.id : '{{ route('users.store') }}'" method="POST"
                        class="space-y-4">
                        @csrf
                        <input type="hidden" name="_method" :value="editMode ? 'PUT' : 'POST'">



                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                            <input type="text" name="name" x-model="user.name" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Email
                                Address</label>
                            <input type="email" name="email" x-model="user.email" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Role</label>
                            <select name="role" x-model="user.role" required
                                class="dropdown-btn w-full">
                                <option value="" disabled selected>Select Role</option>
                                <template x-for="r in roles">
                                    <option :value="r.name" x-text="r.name" :selected="user.role == r.name"></option>
                                </template>
                            </select>
                        </div>

                        <div x-show="user.role === 'Priest'" x-transition class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Title (Optional)</label>
                                <input type="text" name="title" x-model="user.title" placeholder="e.g. Rev. Fr."
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                            </div>

                            <!-- Schedule Settings -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700 space-y-4">
                                <h4 class="font-bold text-sm text-gray-800 dark:text-gray-200"><i class="fas fa-calendar-alt text-blue-500 mr-2"></i>Availability Settings</h4>
                                
                                <div>
                                     <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Working Days</label>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="day in ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']" :key="day">
                                            <label class="flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-blue-400 transition-colors">
                                                <input type="checkbox" name="working_days[]" :value="day" x-model="user.working_days" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="day.substring(0, 3)"></span>
                                            </label>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Select the days this priest is available for services.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                         <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Start Time</label>
                                        <input type="time" name="working_hours[start]" x-model="user.working_hours.start"
                                            class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                    </div>
                                    <div>
                                         <label class="block text-sm font-bold text-gray-500 uppercase mb-2">End Time</label>
                                        <input type="time" name="working_hours[end]" x-model="user.working_hours.end"
                                            class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                    </div>
                                </div>

                                <div>
                                     <label class="block text-sm font-bold text-gray-500 uppercase mb-2">Max Capacity (Services / Day)</label>
                                    <input type="number" name="max_services_per_day" x-model="user.max_services_per_day" min="1"
                                        class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                    <p class="text-xs text-gray-400 mt-1">The maximum number of services this priest can accommodate in one day.</p>
                                </div>
                            </div>
                        </div>

                        <div x-data="{ showPw: false }">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                <span x-text="editMode ? 'New Password (Optional)' : 'Password'"></span>
                            </label>
                            <div class="relative">
                                <input :type="showPw ? 'text' : 'password'" name="password" :required="!editMode"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                <button type="button" @click="showPw = !showPw" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas" :class="showPw ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="userModalOpen = false"
                                class="px-5 py-2.5 rounded-xl text-gray-500 hover:bg-gray-100 font-bold transition-colors">Cancel</button>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-500/30 transition-all">Save
                                Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection