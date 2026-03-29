@extends('layouts.app')

@section('title', 'Collections')
@section('role_label', 'Treasurer')



@section('page_title', 'Collections Entry')
@section('page_subtitle', 'Record daily collections and mass offerings')

@section('content')
    <!-- ACTION BAR -->
    <div class="flex justify-end shrink-0 mb-6">
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="flex items-center gap-2 bg-gradient-to-r from-primary to-blue-600 hover:from-blue-700 hover:to-primary text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5 shrink-0 ml-auto lg:ml-0">
            <i class="fas fa-plus"></i> Record Collection
        </button>
    </div>

    <!-- TABLE -->
    <div
        class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden search-results-container relative" x-data="tableSearch()" @click="handlePagination">
        <!-- Removed blur loading state for instant feel -->
        
        <!-- Filters -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <form action="{{ route('collections') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full search-form" @submit.prevent="submitSearch">
                <div class="flex items-center gap-2 mr-2">
                    <label for="per_page_collections" class="text-xs font-bold text-gray-400 uppercase tracking-wider">Show</label>
                    <select name="per_page" id="per_page_collections" @change="submitSearch"
                        class="dropdown-btn w-20 px-3 py-1.5 h-10">
                        @foreach([5, 10, 15, 20, 50] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 15) == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Input -->
                <div class="relative max-w-xs w-full lg:w-auto">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search source/remarks..."
                        @input.debounce.300ms="submitSearch"
                        class="w-full lg:w-56 pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
                </div>

                <div class="relative max-w-[200px] w-full lg:w-auto">
                    <select name="collection_type" @change="submitSearch"
                        class="dropdown-btn w-full lg:w-auto">
                        <option value="">All Collection Types</option>
                        <option value="Collection" {{ request('collection_type') == 'Collection' ? 'selected' : '' }}>Collection</option>
                        <option value="Mass Offering" {{ request('collection_type') == 'Mass Offering' ? 'selected' : '' }}>Mass Offering</option>
                        <option value="Special Collection" {{ request('collection_type') == 'Special Collection' ? 'selected' : '' }}>Special Collection</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    @if(request()->anyFilled(['search', 'collection_type']))
                        <a href="{{ route('collections') }}"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-medium transition-all px-2">
                            <i class="fas fa-times-circle mr-1"></i>Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead
                    class="bg-gray-50/50 dark:bg-gray-700/30 text-sm font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Source/Remarks</th>
                        <th class="px-6 py-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse ($collections as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-200">
                                {{ date('F d, Y', strtotime($row->date_received)) }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800">
                                    <i class="fas fa-hand-holding-usd text-xs"></i>
                                    {{ $row->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 italic">
                                {{ $row->remarks }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-800 dark:text-white">₱
                                {{ number_format($row->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">No collection records
                                found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    <div class="mt-4 px-4 pb-4">
        {{ $collections->links() }}
    </div>
    </div>

    <!-- Modal -->
    <div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm p-4"
        style="background-color: rgba(0,0,0,0.5);">
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md shadow-2xl p-8 border border-gray-100 dark:border-gray-700 relative animate-fade-in-up">
            <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                <i class="fas fa-times"></i>
            </button>

            <div class="mb-6">
                <h3 class="font-bold text-xl text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-plus-circle text-primary"></i> Record Collection
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter the details of the collection.</p>
            </div>

            <form action="{{ route('collections.store') }}" method="POST"
                onsubmit="return confirmcollectionSubmit(event, this)">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Type</label>
                        <div class="relative group">
                            <select name="type"
                                class="dropdown-btn w-full">
                                <option value="Collection">Sunday Collection</option>
                                <option value="Mass Offering">Mass Offering</option>
                                <option value="Special Collection">Special Collection</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Amount
                            (₱)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-gray-400">₱</span>
                            <input type="number" step="0.01" name="amount"
                                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-8 pr-4 py-3 text-sm font-bold text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-primary transition-all"
                                required placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Date</label>
                        <input type="date" name="date_received"
                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-primary transition-all"
                            required value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Remarks
                            / Source</label>
                        <textarea name="remarks"
                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-primary transition-all resize-none"
                            rows="3" placeholder="e.g. 1st Mass, 6:00 AM"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-primary to-blue-600 hover:from-blue-700 hover:to-primary text-white font-bold py-3.5 rounded-xl mt-2 shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                        Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmcollectionSubmit(event, form) {
            event.preventDefault();
            showConfirm(
                'Save Collection?',
                'Please verify that the amount and details are correct.',
                'bg-blue-600 hover:bg-blue-700',
                () => { form.submit(); },
                'Save'
            );
            return false;
        }
    </script>
@endsection