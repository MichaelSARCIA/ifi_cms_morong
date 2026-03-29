@extends('layouts.app')

@section('title', 'Scheduling')
@section('page_title', 'Scheduling')
@section('page_subtitle', 'Manage church events and calendars')
@section('role_label', 'Admin')

@section('content')
    <style>
        /* Custom FullCalendar Styling for "Google Calendar" feel */
        .fc {
            font-family: 'Poppins', sans-serif;
            --fc-border-color: #f3f4f6;
            --fc-button-text-color: #374151;
            --fc-button-bg-color: #fff;
            --fc-button-border-color: #e5e7eb;
            --fc-button-hover-bg-color: #f9fafb;
            --fc-button-hover-border-color: #d1d5db;
            --fc-button-active-bg-color: #e5e7eb;
            --fc-button-active-border-color: #d1d5db;
            --fc-today-bg-color: #eff6ff;
            --fc-now-indicator-color: #3b82f6;
        }

        .fc .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        .fc .fc-button {
            border-radius: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background-color: #eff6ff;
            border-color: #3b82f6;
            color: #3b82f6;
            box-shadow: none;
        }

        .fc .fc-col-header-cell-cushion {
            padding: 1rem 0;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: #f3f4f6;
        }

        .fc-daygrid-day-number {
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            padding: 0.5rem;
        }

        .fc-daygrid-event {
            border-radius: 0.5rem;
            padding: 2px 4px;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        /* Modal Transitions */
        [x-cloak] {
            display: none !important;
        }

        /* Show and fix datetime picker icons for dark mode */
        .dark input[type="datetime-local"]::-webkit-calendar-picker-indicator,
        .dark input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(1.5) contrast(1.2);
            cursor: pointer;
        }

        input[type="datetime-local"]::-webkit-calendar-picker-indicator,
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
        }
    </style>

    <div x-data="schedulingApp()" x-cloak>

        <!-- Notification Modal -->
        <div x-show="notifOpen" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display:none;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center animate-fade-in-up">
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
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center animate-fade-in-up">
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
        <div class="animate-fade-in-up">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Sidebar / Legend -->
                <div class="lg:col-span-1 flex flex-col justify-center">
                    <div class="space-y-8 animate-fade-in-up">
                    <!-- Create Event Button -->
                    @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'Priest')
                        <div class="flex justify-center">
                            <button @click="openAddModal()"
                                class="w-full max-w-[280px] py-4 bg-white hover:bg-gray-50 text-gray-800 dark:text-white dark:bg-gray-800 dark:hover:bg-gray-700 font-bold rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-center gap-4 transition-all transform hover:-translate-y-1 group">
                                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-plus text-lg"></i>
                                </div>
                                <span class="text-base">Add Schedule</span>
                            </button>
                        </div>
                    @endif

                    <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-800 p-6 w-full max-w-[280px] mx-auto overflow-hidden relative">
                        <!-- Decorative Header -->
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 opacity-20"></div>
                        
                        <h3 class="font-black text-gray-900 dark:text-white mb-5 text-sm uppercase tracking-[0.15em] text-center">
                            Activity Types
                        </h3>
                        <div class="flex flex-col gap-1">
                            @php
                                $activityTypes = [
                                    ['name' => 'Mass',             'icon' => 'fa-church',      'color' => 'violet'],
                                    ['name' => 'Special Mass',     'icon' => 'fa-star',        'color' => 'purple'],
                                    ['name' => 'Parish Meeting',   'icon' => 'fa-users',       'color' => 'blue'],
                                    ['name' => 'Novena',           'icon' => 'fa-pray',        'color' => 'indigo'],
                                    ['name' => 'Youth Activity',   'icon' => 'fa-child',       'color' => 'sky'],
                                    ['name' => 'Community Service','icon' => 'fa-hands-helping','color' => 'green'],
                                    ['name' => 'Other',            'icon' => 'fa-calendar-alt','color' => 'amber'],
                                ];
                            @endphp
                            @foreach($activityTypes as $act)
                                <div class="flex items-center gap-4 py-2 px-3 rounded-xl transition-all duration-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 group cursor-default">
                                    <div class="w-9 h-9 shrink-0 flex items-center justify-center rounded-xl bg-{{ $act['color'] }}-100 text-{{ $act['color'] }}-600 transition-all duration-300 group-hover:scale-110 shadow-[0_2px_4px_rgba(0,0,0,0.05)]">
                                        <i class="fas {{ $act['icon'] }} text-base"></i>
                                    </div>
                                    <span class="text-[17px] font-extrabold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $act['name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

                <!-- Calendar Widget -->
                <div
                    class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-6 h-[calc(100vh-140px)]">
                    <div id="calendar" class="h-full"></div>
                </div>
            </div>
        </div>

        <!-- View Event Modal -->
        <div x-show="isViewModalOpen"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">

            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-md p-0 shadow-2xl transform transition-all overflow-hidden"
                @click.away="isViewModalOpen = false">

                <!-- Header with Color strip -->
                <div class="h-24 relative p-6 flex flex-col justify-end" :class="{
                    'bg-violet-100 text-violet-600': selectedEvent.extendedProps?.type === 'Mass',
                    'bg-purple-100 text-purple-600': selectedEvent.extendedProps?.type === 'Special Mass',
                    'bg-blue-100 text-blue-600': ['Parish Meeting', 'Baptism'].includes(selectedEvent.extendedProps?.type),
                    'bg-indigo-100 text-indigo-600': selectedEvent.extendedProps?.type === 'Novena',
                    'bg-sky-100 text-sky-600': selectedEvent.extendedProps?.type === 'Youth Activity',
                    'bg-green-100 text-green-600': selectedEvent.extendedProps?.type === 'Community Service',
                    'bg-pink-100 text-pink-600': selectedEvent.extendedProps?.type === 'Wedding',
                    'bg-gray-100 text-gray-600': selectedEvent.extendedProps?.type === 'Burial',
                    'bg-amber-100 text-amber-600': ['Other', 'Blessing'].includes(selectedEvent.extendedProps?.type) || !selectedEvent.extendedProps?.type
                }">

                    <div class="absolute top-4 right-4 flex gap-2">
                        @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'Staff' || auth()->user()->role === 'Secretary')
                            <button type="button" @click="editEvent" x-show="selectedEvent.extendedProps?.source === 'manual'"
                                class="w-8 h-8 rounded-full bg-blue-100 hover:bg-blue-500 text-blue-600 hover:text-white flex items-center justify-center transition-colors shadow-sm"
                                title="Edit Event">
                                <i class="fas fa-edit text-sm"></i>
                            </button>
                            <button type="button" @click="deleteEvent" x-show="selectedEvent.extendedProps?.source === 'manual'"
                                class="w-8 h-8 rounded-full bg-red-100 hover:bg-red-500 text-red-600 hover:text-white flex items-center justify-center transition-colors shadow-sm"
                                title="Delete Event">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        @endif
                        <button type="button" @click="isViewModalOpen = false"
                            class="w-8 h-8 rounded-full bg-white/50 hover:bg-white text-current flex items-center justify-center transition-colors shadow-sm">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>

                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/70 backdrop-blur-sm text-sm font-bold uppercase tracking-wider w-fit mb-2 shadow-sm">
                        <i class="fas fa-circle text-xs"></i>
                        <span x-text="selectedEvent.extendedProps?.type"></span>
                    </span>
                    <h3 class="text-2xl font-bold leading-tight" x-text="selectedEvent.title"></h3>
                </div>

                <div class="p-6 space-y-6">
                    <div class="flex gap-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center shrink-0 text-gray-400">
                            <i class="far fa-clock text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 dark:text-white">Date and Time</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400"
                                x-text="formatDateRange(selectedEvent.start, selectedEvent.end)"></p>
                        </div>
                    </div>

                    <div class="flex gap-4" x-show="selectedEvent.extendedProps?.description">
                        <div
                            class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center shrink-0 text-gray-400">
                            <i class="fas fa-align-left text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 dark:text-white">Description</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line leading-relaxed"
                                x-html="selectedEvent.extendedProps?.description"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Event Modal -->
        <div x-show="isAddModalOpen" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="isAddModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 relative animate-fade-in-up">
                    <div class="flex justify-between items-center mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                        <h3 class="font-bold text-xl text-gray-800 dark:text-white" x-text="isEditMode ? 'Edit Schedule' : 'Add Schedule'"></h3>
                        <button @click="isAddModalOpen = false" class="text-gray-400 hover:text-gray-600"><i
                                class="fas fa-times"></i></button>
                    </div>

                    <form @submit.prevent="submitEvent" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Event Title</label>
                            <input type="text" x-model="newEvent.title" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-gray-800 dark:text-white"
                                placeholder="e.g., Weekly Sunday Mass">
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Activity
                                Type</label>
                            <select x-model="newEvent.type" required
                                class="dropdown-btn w-full">
                                <option value="Mass">Mass</option>
                                <option value="Special Mass">Special Mass</option>
                                <option value="Parish Meeting">Parish Meeting</option>
                                <option value="Novena">Novena</option>
                                <option value="Youth Activity">Youth Activity</option>
                                <option value="Community Service">Community Service</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="relative">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Assign Priest (Optional)</label>
                            <select x-model="newEvent.priest_id"
                                class="dropdown-btn w-full">
                                <option value="">Unassigned</option>
                                @foreach($active_priests as $priest)
                                    <option value="{{ $priest->id }}">{{ $priest->title ? $priest->title . ' ' : '' }}{{ $priest->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Start Date &
                                    Time</label>
                                <input type="datetime-local" x-model="newEvent.start_datetime" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-gray-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">End Date &
                                    Time</label>
                                <input type="datetime-local" x-model="newEvent.end_datetime" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-gray-800 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Description
                                (Optional)</label>
                            <textarea x-model="newEvent.description" rows="3"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-gray-800 dark:text-white"
                                placeholder="Any additional details..."></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="isAddModalOpen = false"
                                class="px-5 py-2.5 rounded-xl text-gray-500 hover:bg-gray-100 font-bold transition-colors text-sm">Cancel</button>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2 text-sm"
                                :disabled="isSubmitting">
                                <span x-show="isSubmitting"><i class="fas fa-spinner fa-spin"></i></span>
                                <span x-text="isSubmitting ? 'Saving...' : (isEditMode ? 'Update Event' : 'Save Event')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function schedulingApp() {
                return {
                    isAddModalOpen: false,
                    isViewModalOpen: false,
                    isSubmitting: false,
                    isEditMode: false,
                    selectedEvent: {},
                    calendar: null,
                    notifOpen: false,
                    notifMsg: '',
                    notifType: 'success',
                    deleteConfirmOpen: false,
                    newEvent: {
                        title: '',
                        type: 'Mass',
                        start_datetime: '',
                        end_datetime: '',
                        description: ''
                    },

                    initWatchers() {
                        this.$watch('newEvent.start_datetime', (value) => {
                            if (value && !this.isEditMode) {
                                // Default end time to 1 hour after start time
                                let startDate = new Date(value);
                                let endDate = new Date(startDate.getTime() + (60 * 60 * 1000));
                                
                                // Format to ISO local string for datetime-local input
                                const offset = endDate.getTimezoneOffset() * 60000;
                                this.newEvent.end_datetime = new Date(endDate - offset).toISOString().slice(0, 16);
                            }
                        });
                        
                        // Watch for end date to ensure it's not before start date
                        this.$watch('newEvent.end_datetime', (value) => {
                            if (value && this.newEvent.start_datetime) {
                                const start = new Date(this.newEvent.start_datetime);
                                const end = new Date(value);
                                if (end < start) {
                                    // Optionally could auto-adjust or just let validation handle it
                                    // For now, we'll let the user know via validation on submit
                                }
                            }
                        });
                    },

                    showNotif(msg, type) {
                        this.notifMsg = msg;
                        this.notifType = type || 'success';
                        this.notifOpen = true;
                    },

                    init() {
                        const calendarEl = document.getElementById('calendar');
                        this.calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth',
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                today: 'Today',
                                month: 'Month',
                                week: 'Week',
                                day: 'Day'
                            },
                            events: '{{ route('schedules.events') }}',
                            height: '100%',
                            stickyHeaderDates: true,
                            editable: false,
                            selectable: false,
                            dayMaxEvents: false,
                            eventTimeFormat: {
                                hour: 'numeric',
                                minute: '2-digit',
                                meridiem: 'short'
                            },
                            eventClick: (info) => {
                                this.selectedEvent = info.event;
                                this.isViewModalOpen = true;
                            }
                        });
                        this.calendar.render();
                        this.initWatchers();
                    },

                    deleteEvent() {
                        if (!this.selectedEvent || !this.selectedEvent.id) return;
                        this.deleteConfirmOpen = true;
                    },

                    async confirmDeleteEvent() {
                        this.deleteConfirmOpen = false;
                        const id = this.selectedEvent.id.replace('manual_', '');
                        this.isSubmitting = true;
                        try {
                            const response = await fetch(`{{ url('/api/schedules') }}/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            const data = await response.json();

                            if (response.ok) {
                                this.isViewModalOpen = false;
                                this.calendar.refetchEvents();
                                this.showNotif('Event successfully deleted!', 'success');
                            } else {
                                this.showNotif(data.message || 'Failed to delete event.', 'error');
                            }
                        } catch (error) {
                            this.showNotif('An error occurred. Please try again.', 'error');
                            console.error(error);
                        } finally {
                            this.isSubmitting = false;
                        }
                    },

                    editEvent() {
                        if (!this.selectedEvent) return;
                        this.isEditMode = true;
                        this.isViewModalOpen = false;
                        
                        // Populate newEvent with selectedEvent data
                        const start = this.selectedEvent.start;
                        const end = this.selectedEvent.end;
                        
                        // Format to ISO local string for datetime-local input
                        const formatLocal = (date) => {
                            if (!date) return '';
                            const d = new Date(date);
                            d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
                            return d.toISOString().slice(0, 16);
                        };

                        this.newEvent = {
                            id: this.selectedEvent.id.replace('manual_', ''),
                            title: this.selectedEvent.title,
                            type: this.selectedEvent.extendedProps?.type || 'Mass',
                            start_datetime: formatLocal(start),
                            end_datetime: formatLocal(end),
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
                        // Client-side validation
                        if (!this.newEvent.start_datetime || !this.newEvent.end_datetime) {
                            this.showNotif('Please select both start and end date/time.', 'error');
                            return;
                        }

                        const startTime = new Date(this.newEvent.start_datetime).getTime();
                        const endTime = new Date(this.newEvent.end_datetime).getTime();

                        if (endTime <= startTime) {
                            this.showNotif('End date & time must be after the start date & time.', 'error');
                            return;
                        }

                        this.isSubmitting = true;
                        try {
                            const url = this.isEditMode 
                                ? `{{ url('/api/schedules') }}/${this.newEvent.id}`
                                : "{{ route('schedules.store') }}";
                            
                            const method = this.isEditMode ? 'PUT' : 'POST';

                            const response = await fetch(url, {
                                method: method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(this.newEvent)
                            });

                            const data = await response.json();

                            if (response.ok) {
                                this.isAddModalOpen = false;
                                this.calendar.refetchEvents();
                                this.showNotif('Event successfully created!', 'success');
                            } else {
                                this.showNotif(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Failed to create event.'), 'error');
                            }
                        } catch (error) {
                            this.showNotif('An error occurred. Please try again.', 'error');
                            console.error(error);
                        } finally {
                            this.isSubmitting = false;
                        }
                    },

                    formatDateRange(start, end) {
                        if (!start) return '';
                        const options = { weekday: 'long', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit' };
                        let str = start.toLocaleDateString('en-US', options);
                        if (end) {
                            str += ' - ' + end.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                        }
                        return str;
                    }
                }
            }
        </script>
    @endpush
@endsection