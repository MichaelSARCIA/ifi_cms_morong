@extends('layouts.app')

@section('title', 'Parish Registry')
@section('page_title', 'Parish Registry')
@section('page_subtitle', 'Master list of parishioners')
@section('role_label', 'Admin')



@section('content')
    <div x-data="{ 
                            modalOpen: {{ $errors->any() ? 'true' : 'false' }}, 
                            viewModalOpen: false,
                            editMode: false,
                            member: {
                                id: '',
                                surname: '',
                                first_name: '',
                                middle_initial: '',
                                birthdate: '',
                                sex: '',
                                contact_info: '',
                                address: '',
                                marital_status: ''
                            },
                            viewMember: {},
                        }" <!-- Hidden Bulk Action Form -->


        <!-- ACTION BAR -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <!-- Spacing/Placeholder if needed or just empty -->
            </div>

            <div class="flex gap-3 ml-auto">
                <button @click="modalOpen = true; editMode = false; member = { marital_status: '' }"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-blue-500/30 flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i> <span>Add Parishioner</span>
                </button>
            </div>
        </div>

        <!-- CONTENT -->
        <div x-data="tableSearch()">
            <!-- Search & Filters -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-t-3xl border border-gray-100 dark:border-gray-800 border-b-0 shadow-sm relative z-30">
                <form action="{{ route('members') }}" method="GET" class="flex flex-col md:flex-row md:items-center justify-between gap-4 w-full search-form" @submit.prevent="submitSearch">
                <div class="relative max-w-sm w-full group">
                    <span
                        class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                        <i class="fas fa-search" :class="{'fa-spin fa-spinner': isLoading, 'fa-search': !isLoading}"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" @input.debounce.50ms="submitSearch"
                        class="w-full bg-gray-50 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-xl pl-11 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all placeholder-gray-400 shadow-inner"
                        placeholder="Search parishioners...">
                </div>

                <div class="flex flex-wrap gap-2 items-center">


                    <!-- Filters -->
                    <div class="relative group">
                        <select name="sex" @change="submitSearch"
                            class="dropdown-btn w-full lg:w-32">
                            <option value="">All Sex</option>
                            <option value="Male" {{ request('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ request('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(request()->anyFilled(['sex']))
                            <button type="button" @click="clearFilters()"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-bold transition-all px-2 flex items-center gap-1">
                                <i class="fas fa-times-circle"></i>Clear
                            </button>
                        @endif
                    </div>

                </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-b-3xl border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col overflow-visible search-results-container relative" @click="handlePagination">
                <!-- Table -->
                <div class="flex-1 overflow-x-auto relative rounded-b-3xl">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-20 bg-white dark:bg-gray-800 shadow-sm">
                        <tr
                            class="bg-gray-50/90 dark:bg-gray-700/90 backdrop-blur text-sm font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700 tracking-wider">

                            <th class="px-6 py-5">No.</th>
                            <th class="px-6 py-5">Name</th>
                            <th class="px-4 py-5 text-center">Sex</th>
                            <th class="px-6 py-5">Address</th>
                            <th class="px-6 py-5">Contact</th>
                            <th class="px-6 py-5">Birthdate</th>
                            <th class="px-6 py-5 text-center">Marital Status</th>
                            <th class="px-8 py-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="members-table-body" class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @include('admin.partials.members-table')
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $members->links() }}
            </div>
            </div>
        </div>
    </div>

        <!-- ADD/EDIT MODAL -->
        <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="modalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 relative animate-fade-in-up">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-xl text-gray-800 dark:text-white"
                            x-text="editMode ? 'Edit Parishioner' : 'Add New Parishioner'"></h3>
                        <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                    </div>

                <form method="POST"
                    :action="editMode ? '{{ url('members') }}/' + member.id : '{{ route('members.store') }}'"
                    class="space-y-4" @submit.prevent="
                                                            const form = $el;
                                                            const actionType = editMode ? 'Update' : 'Add';
                                                            showConfirm(
                                                                actionType + ' Member', 
                                                                'Are you sure you want to ' + actionType.toLowerCase() + ' this parishioner?', 
                                                                'bg-blue-600 hover:bg-blue-700', 
                                                                () => form.submit()
                                                            )
                                                        ">
                    @csrf

                    @if($errors->any())
                        <div
                            class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800 flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-800 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                                <i class="fas fa-exclamation"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-red-800 dark:text-red-200 text-sm uppercase">Please fix the
                                    following errors:</h4>
                                <ul class="text-sm text-red-600 dark:text-red-300 list-disc pl-4 mt-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    <!-- Method spoofing for PUT if editing -->
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Surname</label>
                            <input type="text" name="surname" x-model="member.surname"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                                required placeholder="Dela Cruz">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                            <input type="text" name="first_name" x-model="member.first_name"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                                required placeholder="Juan">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">M.I.</label>
                            <input type="text" name="middle_initial" x-model="member.middle_initial"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                                placeholder="A.">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Birthdate</label>
                            <input type="date" name="birthdate" x-model="member.birthdate"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Sex</label>
                            <div class="relative group">
                                <select name="sex" x-model="member.sex"
                                    class="dropdown-btn w-full"
                                    required>
                                    <option value="" disabled>Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Contact Info</label>
                        <input type="text" name="contact_info" x-model="member.contact_info"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                            placeholder="Mobile No.">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Address</label>
                        <textarea name="address" x-model="member.address" rows="2"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                            required></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Marital Status</label>
                        <div class="relative group">
                            <select name="marital_status" x-model="member.marital_status"
                                class="dropdown-btn w-full"
                                required>
                                <option value="" disabled>Select Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="modalOpen = false"
                            class="px-5 py-2.5 rounded-xl text-gray-500 hover:bg-gray-100 font-bold transition-colors text-sm">Cancel</button>
                        <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-500/30 transition-all text-sm">
                            <span x-text="editMode ? 'Update Member' : 'Add Member'"></span>
                        </button>
                    </div>
                </form>
                </div>
            </div>
        </div>

        <!-- VIEW DETAILS MODAL -->
        <div x-show="viewModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="viewModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 relative animate-fade-in-up">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-xl text-gray-800 dark:text-white">Parishioner Details</h3>
                        <button @click="viewModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xl font-bold uppercase">
                            <span x-text="viewMember.full_name ? viewMember.full_name.substring(0,2) : ''"></span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-1" x-text="viewMember.full_name"></h2>
                            <span class="px-2.5 py-1 rounded-md text-sm font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 uppercase tracking-wide border border-gray-200 dark:border-gray-600"
                                x-text="viewMember.marital_status"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                        <div>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1">Sex</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 flex items-center gap-2">
                                <i :class="viewMember.sex === 'Male' ? 'fas fa-mars text-blue-500' : 'fas fa-venus text-pink-500'"></i>
                                <span x-text="viewMember.sex"></span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1">Birthdate</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="viewMember.birthdate"></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1">Contact Info</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="viewMember.contact_info || 'N/A'"></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1">Address</p>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 leading-relaxed" x-text="viewMember.address"></p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700 mt-6">
                        <button @click="viewModalOpen = false"
                            class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-500/30 transition-all text-sm">Close Details</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection