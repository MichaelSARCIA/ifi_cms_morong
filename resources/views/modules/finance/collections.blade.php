@extends('layouts.app')

@section('title', 'Collections')
@section('role_label', 'Treasurer')



@section('page_title', 'Collections Entry')
@section('page_subtitle', 'Record daily collections and mass offerings')

@section('content')
    <!-- ACTION BAR -->
    <div class="flex justify-end shrink-0 mb-6">
        <button onclick="openCollectionModal()"
            class="flex items-center gap-2 bg-gradient-to-r from-primary to-blue-600 hover:from-blue-700 hover:to-primary text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5 shrink-0 ml-auto lg:ml-0">
            <i class="fas fa-plus"></i> Record Collection
        </button>
    </div>

    <!-- TABLE -->
    <div x-data="tableSearch()">
        <!-- Filters -->
        <div class="p-6 bg-white dark:bg-gray-800 rounded-t-3xl border border-gray-100 dark:border-gray-700 border-b-0 shadow-sm relative z-20">
            <form action="{{ route('collections') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full search-form" @submit.prevent="submitSearch">


                <!-- Search Input -->
                <div class="relative max-w-xs w-full lg:w-auto">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search source/remarks..."
                        @input.debounce.50ms="submitSearch"
                        class="w-full lg:w-56 pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
                </div>

                <div class="relative max-w-[200px] w-full lg:w-auto">
                    <select name="collection_type" @change="submitSearch"
                        class="dropdown-btn w-full lg:w-auto">
                        <option value="">All Collection Types</option>
                        <option value="Sunday Collection" {{ request('collection_type') == 'Sunday Collection' ? 'selected' : '' }}>Sunday Collection</option>
                        <option value="Mass Offering" {{ request('collection_type') == 'Mass Offering' ? 'selected' : '' }}>Mass Offering</option>
                        <option value="Special Collection" {{ request('collection_type') == 'Special Collection' ? 'selected' : '' }}>Special Collection</option>
                        <option value="Other" {{ request('collection_type') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    @if(request()->anyFilled(['collection_type']))
                        <button type="button" @click="clearFilters()"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-bold transition-all px-2 flex items-center gap-1">
                            <i class="fas fa-times-circle"></i>Clear
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-b-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden search-results-container relative" @click="handlePagination">
            <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-320px)] custom-scrollbar">
            <table class="w-full text-left border-collapse relative">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 text-sm font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Source & Notes</th>
                        <th class="px-6 py-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse ($collections as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-6 py-4 text-base font-bold text-gray-900 dark:text-white">
                                {{ date('F d, Y', strtotime($row->date_received)) }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $typeConfig = match($row->type) {
                                        'Sunday Collection', 'Collection' => ['label' => 'Sunday Collection', 'icon' => 'fa-church',         'class' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800'],
                                        'Mass Offering'                   => ['label' => 'Mass Offering',     'icon' => 'fa-hands-praying',   'class' => 'bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 border border-violet-200 dark:border-violet-800'],
                                        'Special Collection'              => ['label' => 'Special Collection', 'icon' => 'fa-star',            'class' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800'],
                                        'Other'                           => ['label' => 'Other',              'icon' => 'fa-th-list',         'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600'],
                                        default                           => ['label' => $row->type,           'icon' => 'fa-hand-holding-usd','class' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $typeConfig['class'] }}">
                                    <i class="fas {{ $typeConfig['icon'] }} text-xs"></i>
                                    {{ $typeConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $row->remarks }}</div>
                                @if(isset($row->notes) && $row->notes)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 italic mt-0.5">{{ $row->notes }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-lg text-gray-900 dark:text-white">₱
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
                <h3 class="font-bold text-xl text-gray-900 dark:text-white flex items-center gap-2">
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
                            class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Type <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <select name="type" class="dropdown-btn w-full">
                                <option value="Sunday Collection">Sunday Collection</option>
                                <option value="Mass Offering">Mass Offering</option>
                                <option value="Special Collection">Special Collection</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Amount 
                            (₱) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-bold">₱</span>
                            </div>
                            <input type="number" step="0.01" name="amount"
                                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-4 py-3 text-sm font-bold text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-primary transition-all"
                                required placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Date <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="date_received" id="collectionDateInput"
                                placeholder="MM/DD/YYYY"
                                readonly required
                                class="datepicker-input w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-900 dark:text-white placeholder-gray-400">
                            <i class="fas fa-calendar-alt absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Source <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <select name="remarks" required class="dropdown-btn w-full">
                                <option value="">Select source...</option>
                                <option value="1st Mass (6:00 AM)">1st Mass (6:00 AM)</option>
                                <option value="2nd Mass (8:00 AM)">2nd Mass (8:00 AM)</option>
                                <option value="3rd Mass (10:00 AM)">3rd Mass (10:00 AM)</option>
                                <option value="Walk-in / Drop box">Walk-in / Drop box</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Notes (Optional)</label>
                        <textarea name="notes"
                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-primary transition-all resize-none"
                            rows="2" placeholder="Additional notes or details..."></textarea>
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                            class="w-1/2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-3.5 rounded-xl transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="w-1/2 bg-gradient-to-r from-primary to-blue-600 hover:from-blue-700 hover:to-primary text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                            Save Record
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let collectionDatepicker = null;

        function createCollectionDatepicker(el) {
            return new AirDatepicker(el, {
                locale: {
                    days: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
                    daysShort: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
                    daysMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
                    months: ['January','February','March','April','May','June','July','August','September','October','November','December'],
                    monthsShort: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                    today: 'Today', clear: 'Clear',
                    dateFormat: 'MM/dd/yyyy', timeFormat: 'hh:ii aa', firstDay: 0
                },
                dateFormat: 'MM/dd/yyyy',
                autoClose: true,
                buttons: ['today'],
                position: 'bottom left',
                maxDate: new Date(),
                onSelect: ({ date }) => {
                    if (date) {
                        const tzOffset = date.getTimezoneOffset() * 60000;
                        el.value = (new Date(date - tzOffset)).toISOString().slice(0, 10);
                    } else {
                        el.value = '';
                    }
                }
            });
        }

        function openCollectionModal() {
            document.getElementById('addModal').classList.remove('hidden');
            const el = document.getElementById('collectionDateInput');
            if (el && !collectionDatepicker) {
                collectionDatepicker = createCollectionDatepicker(el);
                // Reposition calendar on modal scroll so it follows the input
                document.getElementById('addModal').addEventListener('scroll', () => {
                    if (collectionDatepicker && collectionDatepicker.visible) {
                        collectionDatepicker.show();
                    }
                }, { passive: true });
            }
        }

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