@extends('layouts.app')

@section('title', 'Scheduling')
@section('page_title', 'Scheduling')
@section('page_subtitle', 'Manage church events and calendars')
@section('role_label', 'Admin')

@section('content')
    <style>
        /* ---- Google Calendar–style FullCalendar overrides ---- */
        .fc {
            font-family: 'Inter', 'Poppins', sans-serif;
            --fc-border-color: #e5e7eb;
            --fc-today-bg-color: #eff6ff;
            --fc-now-indicator-color: #3b82f6;
            --fc-page-bg-color: #ffffff;
            --fc-neutral-bg-color: #f9fafb;
        }
        .dark .fc {
            --fc-border-color: #374151;
            --fc-today-bg-color: rgba(37, 99, 235, 0.12);
            --fc-page-bg-color: #1f2937;
            --fc-neutral-bg-color: #111827;
        }

        /* ---- Buttons (light) ---- */
        .fc .fc-button,
        .fc .fc-button-primary {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            color: #374151 !important;
            font-family: inherit;
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.4rem 0.75rem;
            border-radius: 9999px;
            transition: background 0.15s;
        }
        .fc .fc-button:hover,
        .fc .fc-button-primary:hover {
            background: #f3f4f6 !important;
        }
        /* ---- Buttons (dark) ---- */
        .dark .fc .fc-button,
        .dark .fc .fc-button-primary {
            color: #e5e7eb !important;
        }
        .dark .fc .fc-button:hover,
        .dark .fc .fc-button-primary:hover {
            background: #374151 !important;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: #e5e7eb !important;
            color: #1f2937 !important;
        }
        .dark .fc .fc-button-primary:not(:disabled).fc-button-active,
        .dark .fc .fc-button-primary:not(:disabled):active {
            background: #1e3a5f !important;
            color: #93c5fd !important;
        }
        .fc .fc-button-group { gap: 2px; }

        /* Today button – outlined pill */
        .fc .fc-today-button {
            border: 1px solid #d1d5db !important;
            border-radius: 9999px !important;
            font-weight: 600 !important;
            padding: 0.35rem 1rem !important;
        }
        .fc .fc-today-button:hover { background: #f9fafb !important; }
        .dark .fc .fc-today-button {
            border-color: #4b5563 !important;
        }
        .dark .fc .fc-today-button:hover { background: #374151 !important; }

        /* View switch buttons */
        .fc .fc-dayGridMonth-button,
        .fc .fc-timeGridWeek-button,
        .fc .fc-timeGridDay-button {
            border: 1px solid #d1d5db !important;
            border-radius: 0 !important;
        }
        .dark .fc .fc-dayGridMonth-button,
        .dark .fc .fc-timeGridWeek-button,
        .dark .fc .fc-timeGridDay-button {
            border-color: #4b5563 !important;
        }
        .fc .fc-dayGridMonth-button { border-radius: 9999px 0 0 9999px !important; }
        .fc .fc-timeGridDay-button  { border-radius: 0 9999px 9999px 0 !important; }
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background: #dbeafe !important;
            color: #1d4ed8 !important;
            border-color: #93c5fd !important;
        }
        .dark .fc .fc-button-primary:not(:disabled).fc-button-active {
            background: #1e3a5f !important;
            color: #93c5fd !important;
            border-color: #3b82f6 !important;
        }

        /* Toolbar title */
        .fc .fc-toolbar-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.01em;
        }
        .dark .fc .fc-toolbar-title { color: #f9fafb; }

        /* Navigation arrows */
        .fc .fc-prev-button,
        .fc .fc-next-button {
            border: 1px solid #d1d5db !important;
            border-radius: 9999px !important;
            width: 36px !important;
            height: 36px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
        }
        .dark .fc .fc-prev-button,
        .dark .fc .fc-next-button {
            border-color: #4b5563 !important;
        }
        .fc .fc-prev-button .fc-icon,
        .fc .fc-next-button .fc-icon { color: #374151; }
        .dark .fc .fc-prev-button .fc-icon,
        .dark .fc .fc-next-button .fc-icon { color: #d1d5db; }

        /* Column header (SUN, MON …) */
        .fc .fc-col-header-cell {
            border-bottom: 1px solid #e5e7eb !important;
            background: #ffffff;
        }
        .dark .fc .fc-col-header-cell {
            border-bottom: 1px solid #374151 !important;
            background: #1f2937;
        }
        .fc .fc-col-header-cell-cushion {
            padding: 0.65rem 0;
            font-size: 0.78rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-decoration: none !important;
        }
        .dark .fc .fc-col-header-cell-cushion { color: #9ca3af; }

        /* Day grid cells */
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: #f3f4f6;
        }
        .dark .fc-theme-standard td,
        .dark .fc-theme-standard th { border-color: #374151; }

        /* Day numbers */
        .fc-daygrid-day-number {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            padding: 6px 8px;
            text-decoration: none !important;
        }
        .dark .fc-daygrid-day-number { color: #9ca3af; }

        /* Today highlight */
        .fc-day-today .fc-daygrid-day-number {
            background: #2563eb;
            color: #fff !important;
            border-radius: 9999px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px;
            padding: 0;
        }
        .fc-day-today { background: #f8faff !important; }
        .dark .fc-day-today { background: rgba(37,99,235,0.12) !important; }

        /* Other month days */
        .fc-day-other .fc-daygrid-day-number { color: #d1d5db; }
        .dark .fc-day-other .fc-daygrid-day-number { color: #4b5563; }

        /* Events */
        .fc-daygrid-event {
            border-radius: 4px;
            padding: 2px 7px;
            font-size: 0.8rem;
            font-weight: 600;
            border: none !important;
            margin-bottom: 2px;
        }
        .fc-daygrid-event:hover { filter: brightness(0.9); }

        /* "more" link */
        .fc-daygrid-more-link { color: #3b82f6 !important; font-size: 0.75rem; font-weight: 600; }
        .dark .fc-daygrid-more-link { color: #60a5fa !important; }

        /* [x-cloak] */
        [x-cloak] { display: none !important; }

        /* datetime picker dark */
        .dark input[type="datetime-local"]::-webkit-calendar-picker-indicator,
        .dark input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(1.5) contrast(1.2); cursor: pointer;
        }
        input[type="datetime-local"]::-webkit-calendar-picker-indicator,
        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; }

        /* Calendar container */
        #fc-wrapper { overflow: hidden; }
    </style>


    <div x-data="schedulingApp()" x-cloak>

        <!-- Notification Modal -->
        <div x-show="notifOpen" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display:none;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4"
                    :class="notifType === 'success' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                    <i class="fas text-2xl" :class="notifType === 'success' ? 'fa-check' : 'fa-exclamation-triangle'"></i>
                </div>
                <h3 class="font-bold text-xl text-gray-800 dark:text-white mb-2" x-text="notifType === 'success' ? 'Success!' : 'Error'"></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5" x-text="notifMsg"></p>
                <button @click="notifOpen = false"
                    class="w-full py-3 rounded-xl font-bold text-white transition-all text-sm"
                    :class="notifType === 'success' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">OK</button>
            </div>
        </div>

        <!-- Delete Confirm Modal -->
        <div x-show="deleteConfirmOpen" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display:none;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash-alt text-2xl"></i>
                </div>
                <h3 class="font-bold text-xl text-gray-800 dark:text-white mb-2">Delete Event?</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">This cannot be undone. Are you sure?</p>
                <div class="flex gap-3 justify-center">
                    <button @click="deleteConfirmOpen = false"
                        class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold hover:bg-gray-200 transition-colors text-sm">Cancel</button>
                    <button @click="confirmDeleteEvent()"
                        class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold shadow-lg shadow-red-500/30 transition-colors text-sm">Yes, Delete</button>
                </div>
            </div>
        </div>

        <!-- Google Calendar–style layout -->
        <div class="flex gap-0 h-[calc(100vh-128px)] animate-fade-in-up">

            <!-- ===== LEFT SIDEBAR ===== -->
            <div class="w-64 shrink-0 flex flex-col gap-4 pr-4 overflow-y-auto custom-scrollbar pb-2">

                <!-- Create button -->
                @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'Priest')
                <button @click="openAddModal()"
                    class="flex items-center gap-3 px-5 py-3 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-md hover:shadow-lg transition-all font-semibold text-gray-700 dark:text-white text-sm group w-full shrink-0">
                    <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/30 group-hover:scale-110 transition-transform shrink-0">
                        <i class="fas fa-plus"></i>
                    </div>
                    Create
                </button>
                @endif


                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm space-y-8">
                    <!-- Activity Category Filter -->
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 block">Activity Categories</label>
                        <div class="relative group">
                            <select x-model="filter_event_type" @change="updateActivityFilter()" 
                                class="dropdown-btn w-full !py-3 !text-base !font-bold text-gray-700 dark:text-gray-200">
                                <option value="All">All Categories</option>
                                <template x-for="(color, name) in eventCategories" :key="name">
                                    <option :value="name" x-text="name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Services Filter -->
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 block">Services</label>
                        <div class="relative group">
                            <select x-model="filter_service_type" @change="updateServiceFilter()" 
                                class="dropdown-btn w-full !py-3 !text-base !font-bold text-gray-700 dark:text-gray-200">
                                <option value="All">All Services</option>
                                <template x-for="(color, name) in serviceTypes" :key="name">
                                    <option :value="name" x-text="name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Simple Legend -->
                    <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Color Reference</p>
                        <div class="flex flex-col gap-3">
                            <template x-for="(color, name) in {...eventCategories, ...serviceTypes}" :key="name">
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full shrink-0 border border-black/5" :style="'background:' + color"></span>
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200 truncate" x-text="name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== MAIN CALENDAR ===== -->
            <div class="flex-1 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
                <div id="fc-wrapper" class="flex-1 p-4">
                    <div id="calendar" style="height:100%;"></div>
                </div>
            </div>
        </div>


        <!-- ===== View Event Modal ===== -->
        <div x-show="isViewModalOpen"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            style="display: none;">

            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden"
                @click.away="isViewModalOpen = false">

                <!-- Colour strip header -->
                <div class="h-24 relative flex items-end p-6" :style="'background:' + getEventColor(selectedEvent)">
                    <div class="absolute top-3 right-3 flex gap-1.5">
                        @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'Staff' || auth()->user()->role === 'Secretary')
                        <button type="button" @click="editEvent" x-show="selectedEvent.extendedProps?.source === 'manual'"
                            class="w-8 h-8 rounded-full bg-white/30 hover:bg-white/60 text-white flex items-center justify-center transition" title="Edit">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button type="button" @click="deleteEvent" x-show="selectedEvent.extendedProps?.source === 'manual'"
                            class="w-8 h-8 rounded-full bg-white/30 hover:bg-red-500/80 text-white flex items-center justify-center transition" title="Delete">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                        @endif
                        <button type="button" @click="isViewModalOpen = false"
                            class="w-8 h-8 rounded-full bg-white/30 hover:bg-white/60 text-white flex items-center justify-center transition">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-white/25 text-white text-xs font-bold uppercase tracking-wide mb-1.5" x-text="selectedEvent.extendedProps?.type || 'Event'"></span>
                        <h3 class="text-white font-bold text-xl leading-tight" x-text="selectedEvent.title"></h3>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <div class="flex items-start gap-4">
                        <i class="far fa-clock mt-0.5 text-gray-400 w-5 text-center text-base"></i>
                        <div>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-0.5">Date & Time</p>
                            <p class="text-base text-gray-600 dark:text-gray-300" x-text="formatDateRange(selectedEvent.start, selectedEvent.end)"></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4" x-show="selectedEvent.extendedProps?.description">
                        <i class="fas fa-info-circle mt-0.5 text-gray-400 w-5 text-center text-base"></i>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-1">Details</p>
                            <div class="text-base text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line" x-html="selectedEvent.extendedProps?.description"></div>
                        </div>
                    </div>
                    <div class="flex items-start gap-4" x-show="selectedEvent.extendedProps?.priest_name">
                        <i class="fas fa-user-tie mt-0.5 text-gray-400 w-5 text-center text-base"></i>
                        <div>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-0.5">Assigned Priest</p>
                            <p class="text-base text-gray-600 dark:text-gray-300" x-text="selectedEvent.extendedProps?.priest_name"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Add / Edit Event Modal ===== -->
        <div x-show="isAddModalOpen" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="isAddModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 relative">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-lg text-gray-800 dark:text-white" x-text="isEditMode ? 'Edit Schedule' : 'New Schedule'"></h3>
                        <button @click="isAddModalOpen = false" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitEvent" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Event Title</label>
                            <input type="text" x-model="newEvent.title" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-gray-800 dark:text-white text-sm"
                                placeholder="e.g., Weekly Sunday Mass">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Activity Type</label>
                            <select x-model="newEvent.type" required class="dropdown-btn w-full">
                                <option value="Mass">Mass</option>
                                <option value="Special Mass">Special Mass</option>
                                <option value="Parish Meeting">Parish Meeting</option>
                                <option value="Novena">Novena</option>
                                <option value="Youth Activity">Youth Activity</option>
                                <option value="Community Service">Community Service</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Assign Priest (Optional)</label>
                            <select x-model="newEvent.priest_id" class="dropdown-btn w-full">
                                <option value="">Unassigned</option>
                                @foreach($active_priests as $priest)
                                    <option value="{{ $priest->id }}">{{ $priest->title ? $priest->title . ' ' : '' }}{{ $priest->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Start</label>
                                <input type="datetime-local" x-model="newEvent.start_datetime" required
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-gray-800 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">End</label>
                                <input type="datetime-local" x-model="newEvent.end_datetime" required
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-gray-800 dark:text-white text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Description (Optional)</label>
                            <textarea x-model="newEvent.description" rows="3"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-gray-800 dark:text-white text-sm resize-none"
                                placeholder="Any additional details..."></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="isAddModalOpen = false"
                                class="px-5 py-2 rounded-xl text-gray-500 hover:bg-gray-100 font-semibold transition text-sm">Cancel</button>
                            <button type="submit"
                                class="px-6 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-500/30 transition flex items-center gap-2 text-sm"
                                :disabled="isSubmitting">
                                <i class="fas fa-spinner fa-spin" x-show="isSubmitting"></i>
                                <span x-text="isSubmitting ? 'Saving...' : (isEditMode ? 'Update' : 'Save')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div><!-- end x-data -->

    @push('scripts')
    <script>
        function schedulingApp() {
            let calendarInstance = null;

            return {
                isAddModalOpen: false,
                isViewModalOpen: false,
                isSubmitting: false,
                isEditMode: false,
                selectedEvent: {},
                notifOpen: false,
                notifMsg: '',
                notifType: 'success',
                deleteConfirmOpen: false,

                /* Mini-calendar state */
                miniDate: new Date(),
                get miniMonthLabel() {
                    return this.miniDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                },
                get miniCalWeeks() {
                    const y = this.miniDate.getFullYear();
                    const m = this.miniDate.getMonth();
                    const today = new Date();
                    const firstDay = new Date(y, m, 1).getDay();
                    const daysInMonth = new Date(y, m + 1, 0).getDate();
                    const daysInPrev = new Date(y, m, 0).getDate();

                    let days = [];
                    for (let i = firstDay - 1; i >= 0; i--) {
                        const d = daysInPrev - i;
                        days.push({ day: d, date: new Date(y, m - 1, d), isOtherMonth: true, isToday: false, key: `p${d}` });
                    }
                    for (let d = 1; d <= daysInMonth; d++) {
                        const date = new Date(y, m, d);
                        const isToday = date.toDateString() === today.toDateString();
                        days.push({ day: d, date, isOtherMonth: false, isToday, key: `c${d}` });
                    }
                    let next = 1;
                    while (days.length % 7 !== 0) {
                        days.push({ day: next, date: new Date(y, m + 1, next), isOtherMonth: true, isToday: false, key: `n${next}` });
                        next++;
                    }
                    const weeks = [];
                    for (let i = 0; i < days.length; i += 7) weeks.push(days.slice(i, i + 7));
                    return weeks;
                },
                prevMiniMonth() { this.miniDate = new Date(this.miniDate.getFullYear(), this.miniDate.getMonth() - 1, 1); },
                nextMiniMonth() { this.miniDate = new Date(this.miniDate.getFullYear(), this.miniDate.getMonth() + 1, 1); },
                goToDate(date) {
                    if (calendarInstance) calendarInstance.gotoDate(date);
                    this.miniDate = new Date(date.getFullYear(), date.getMonth(), 1);
                },

                filter_event_type: 'All',
                filter_service_type: 'All',
                
                /* Separated categories for dropdowns */
                eventCategories: {
                    'Mass':              '#dc2626', // red       — distinct from Wake(indigo) & Baptism(blue)
                    'Special Mass':     '#f59e0b', // amber     — keep ✓
                    'Parish Meeting':   '#0891b2', // cyan      — distinct from Community Service(emerald)
                    'Novena':           '#84cc16', // lime      — distinct from Confirmation(purple)
                    'Youth Activity':   '#f97316', // orange    — keep ✓
                    'Community Service':'#10b981', // emerald   — keep ✓
                    'Other':            '#64748b', // slate     — keep ✓
                },
                serviceTypes: {{ Js::from($service_types->pluck('color', 'name')) }},

                updateActivityFilter() {
                    if (this.filter_event_type !== 'All') {
                        this.filter_service_type = 'All';
                    }
                    if (calendarInstance) calendarInstance.refetchEvents();
                },

                updateServiceFilter() {
                    if (this.filter_service_type !== 'All') {
                        this.filter_event_type = 'All';
                    }
                    if (calendarInstance) calendarInstance.refetchEvents();
                },

                getEventColor(event) {
                    const type = event?.extendedProps?.type || 'Other';
                    const fullMap = { ...this.eventCategories, ...this.serviceTypes };
                    return fullMap[type] || fullMap['Other'];
                },

                showNotif(msg, type) { this.notifMsg = msg; this.notifType = type || 'success'; this.notifOpen = true; },

                init() {
                    const calendarEl = document.getElementById('calendar');
                    calendarInstance = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        buttonText: { today: 'Today', month: 'Month', week: 'Week', day: 'Day' },
                        events: (info, successCallback, failureCallback) => {
                            const params = new URLSearchParams({
                                start: info.startStr,
                                end: info.endStr,
                                event_type: this.filter_event_type,
                                service_type: this.filter_service_type
                            });
                            fetch(`{{ route('schedules.events') }}?${params.toString()}`)
                                .then(r => r.json())
                                .then(d => successCallback(d))
                                .catch(e => failureCallback(e));
                        },
                        height: '100%',
                        editable: false,
                        selectable: false,
                        dayMaxEvents: 3,
                        eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
                        eventClick: (info) => {
                            this.selectedEvent = info.event;
                            this.isViewModalOpen = true;
                        },
                        datesSet: (info) => {
                            /* sync mini-cal month with main calendar */
                            const d = info.view.currentStart;
                            this.miniDate = new Date(d.getFullYear(), d.getMonth(), 1);
                        }
                    });
                    calendarInstance.render();

                    /* Auto end-time watcher */
                    this.$watch('newEvent.start_datetime', (v) => {
                        if (v && !this.isEditMode) {
                            const s = new Date(v);
                            const e = new Date(s.getTime() + 3600000);
                            const off = e.getTimezoneOffset() * 60000;
                            this.newEvent.end_datetime = new Date(e - off).toISOString().slice(0, 16);
                        }
                    });
                },

                deleteEvent() { if (!this.selectedEvent?.id) return; this.deleteConfirmOpen = true; },

                async confirmDeleteEvent() {
                    this.deleteConfirmOpen = false;
                    const id = this.selectedEvent.id.replace('manual_', '');
                    this.isSubmitting = true;
                    try {
                        const r = await fetch(`{{ url('/api/schedules') }}/${id}`, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const d = await r.json();
                        if (r.ok) { this.isViewModalOpen = false; if (calendarInstance) calendarInstance.refetchEvents(); this.showNotif('Event deleted!', 'success'); }
                        else this.showNotif(d.message || 'Failed to delete.', 'error');
                    } catch { this.showNotif('An error occurred.', 'error'); }
                    finally { this.isSubmitting = false; }
                },

                editEvent() {
                    if (!this.selectedEvent) return;
                    this.isEditMode = true;
                    this.isViewModalOpen = false;
                    const fmt = (d) => {
                        if (!d) return '';
                        const x = new Date(d);
                        x.setMinutes(x.getMinutes() - x.getTimezoneOffset());
                        return x.toISOString().slice(0, 16);
                    };
                    this.newEvent = {
                        id: this.selectedEvent.id.replace('manual_', ''),
                        title: this.selectedEvent.title,
                        type: this.selectedEvent.extendedProps?.type || 'Mass',
                        start_datetime: fmt(this.selectedEvent.start),
                        end_datetime: fmt(this.selectedEvent.end),
                        description: this.selectedEvent.extendedProps?.description || '',
                        priest_id: this.selectedEvent.extendedProps?.priest_id || ''
                    };
                    this.isAddModalOpen = true;
                },

                openAddModal() {
                    this.isEditMode = false;
                    this.newEvent = { title: '', type: 'Mass', start_datetime: '', end_datetime: '', description: '', priest_id: '' };
                    this.isAddModalOpen = true;
                },

                async submitEvent() {
                    if (!this.newEvent.start_datetime || !this.newEvent.end_datetime) {
                        this.showNotif('Please fill in both start and end time.', 'error'); return;
                    }
                    if (new Date(this.newEvent.end_datetime) <= new Date(this.newEvent.start_datetime)) {
                        this.showNotif('End time must be after start time.', 'error'); return;
                    }
                    this.isSubmitting = true;
                    try {
                        const url = this.isEditMode
                            ? `{{ url('/api/schedules') }}/${this.newEvent.id}`
                            : "{{ route('schedules.store') }}";
                        const r = await fetch(url, {
                            method: this.isEditMode ? 'PUT' : 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.newEvent)
                        });
                        const d = await r.json();
                        if (r.ok) {
                            this.isAddModalOpen = false;
                            if (calendarInstance) calendarInstance.refetchEvents();
                            this.showNotif(this.isEditMode ? 'Event updated!' : 'Event created!', 'success');
                        } else {
                            this.showNotif(d.errors ? Object.values(d.errors).flat().join(' ') : (d.message || 'Failed.'), 'error');
                        }
                    } catch { this.showNotif('An error occurred.', 'error'); }
                    finally { this.isSubmitting = false; }
                },

                formatDateRange(start, end) {
                    if (!start) return '';
                    const opts = { weekday: 'long', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit' };
                    let s = start.toLocaleDateString('en-US', opts);
                    if (end) s += ' – ' + end.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                    return s;
                }
            }
        }
    </script>
    @endpush
@endsection