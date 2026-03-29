@extends('layouts.app')

@section('title', 'Calendar')
@section('page_title', 'Pastoral Calendar')
@section('page_subtitle', 'Schedule of Masses and Events')
@section('role_label', 'Priest')



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
    </style>

    <div x-data="calendarApp()" x-cloak>
        <div class="animate-fade-in-up">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Sidebar / Legend -->
                <div class="lg:col-span-1 space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
                        <h3 class="font-bold text-gray-800 dark:text-white mb-4 text-base uppercase tracking-wide">Filters
                        </h3>
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Service Type</label>
                            <select x-model="filterType" @change="refreshCalendar" 
                                class="w-full rounded-xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-primary focus:border-primary transition-all shadow-sm">
                                <option value="All">All Services</option>
                                <option value="Baptism">Baptism</option>
                                <option value="Wedding">Wedding</option>
                                <option value="Confirmation">Confirmation</option>
                                <option value="Burial">Funeral / Burial</option>
                                <option value="Wake">Wake</option>
                                <option value="Mass">Mass</option>
                                <option value="Blessing">Blessing</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <h3 class="font-bold text-gray-800 dark:text-white mb-4 text-base uppercase tracking-wide">Legend
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 group cursor-pointer">
                                <div
                                    class="w-4 h-4 rounded-md bg-violet-500 shadow-sm ring-2 ring-white dark:ring-gray-800">
                                </div>
                                <span
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Mass</span>
                            </div>
                            <div class="flex items-center gap-3 group cursor-pointer">
                                <div class="w-4 h-4 rounded-md bg-blue-500 shadow-sm ring-2 ring-white dark:ring-gray-800">
                                </div>
                                <span
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Baptism</span>
                            </div>
                            <div class="flex items-center gap-3 group cursor-pointer">
                                <div class="w-4 h-4 rounded-md bg-pink-500 shadow-sm ring-2 ring-white dark:ring-gray-800">
                                </div>
                                <span
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Wedding</span>
                            </div>
                            <div class="flex items-center gap-3 group cursor-pointer">
                                <div class="w-4 h-4 rounded-md bg-gray-500 shadow-sm ring-2 ring-white dark:ring-gray-800">
                                </div>
                                <span
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Burial</span>
                            </div>
                            <div class="flex items-center gap-3 group cursor-pointer">
                                <div class="w-4 h-4 rounded-md bg-amber-500 shadow-sm ring-2 ring-white dark:ring-gray-800">
                                </div>
                                <span
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Blessing</span>
                            </div>
                            <div class="flex items-center gap-3 group cursor-pointer">
                                <div class="w-4 h-4 rounded-md bg-[#3788d8] shadow-sm ring-2 ring-white dark:ring-gray-800">
                                </div>
                                <span
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Other</span>
                            </div>
                        </div>
                    </div>

                    <!-- Mini Calendar -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-800 dark:text-white text-lg">{{ date('F Y') }}</h3>
                            <div class="flex gap-2">
                                <button class="text-gray-400 hover:text-gray-600"><i
                                        class="fas fa-chevron-left"></i></button>
                                <button class="text-gray-400 hover:text-gray-600"><i
                                        class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-sm font-medium text-gray-400 mb-2">
                            <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-sm text-gray-600 dark:text-gray-300">
                            @for($i = 1; $i <= 30; $i++)
                                <span
                                    class="p-1.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer {{ $i == date('d') ? 'bg-primary text-white font-bold' : '' }}">{{ $i }}</span>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Calendar Widget -->
                <div
                    class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-6 h-[800px] overflow-hidden">
                    <div id="calendar" class="h-full"></div>
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
                                                'bg-blue-100 text-blue-600': selectedEvent.extendedProps?.type === 'Baptism',
                                                'bg-pink-100 text-pink-600': selectedEvent.extendedProps?.type === 'Wedding',
                                                'bg-gray-100 text-gray-600': selectedEvent.extendedProps?.type === 'Burial',
                                                'bg-amber-100 text-amber-600': selectedEvent.extendedProps?.type === 'Blessing',
                                                 'bg-sky-100 text-sky-600': (!selectedEvent.extendedProps?.type || selectedEvent.extendedProps?.type === 'Other')
                                             }">

                        <div class="absolute top-4 right-4 flex gap-2">
                            <button type="button" @click="isViewModalOpen = false"
                                class="w-8 h-8 rounded-full bg-white/50 hover:bg-white text-current flex items-center justify-center transition-colors">
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

                        <!-- Edit Button -->
                        <div class="pt-4 border-t dark:border-gray-700 flex justify-end">
                            <template x-if="selectedEvent.extendedProps?.source === 'request'">
                                <a :href="'{{ url('service-requests') }}/' + selectedEvent.id.replace('req_', '') + '/edit'" 
                                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-bold rounded-xl transition-all shadow-md transform hover:-translate-y-0.5">
                                    <i class="fas fa-edit"></i>
                                    Edit Request Details
                                </a>
                            </template>
                            <template x-if="selectedEvent.extendedProps?.source === 'manual'">
                                <a :href="'{{ url('schedules') }}?edit=' + selectedEvent.id.replace('manual_', '')" 
                                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-bold rounded-xl transition-all shadow-md transform hover:-translate-y-0.5">
                                    <i class="fas fa-edit"></i>
                                    Edit Event Schedule
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function calendarApp() {
                return {
                    isViewModalOpen: false,
                    selectedEvent: {},
                    calendar: null,
                    filterType: 'All',

                    init() {
                        const calendarEl = document.getElementById('calendar');
                        const _this = this; // self reference for FullCalendar
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
                            events: function(info, successCallback, failureCallback) {
                                fetch('{{ route('schedules.events') }}?start=' + info.startStr + '&end=' + info.endStr + '&type=' + _this.filterType)
                                    .then(response => response.json())
                                    .then(data => successCallback(data))
                                    .catch(error => failureCallback(error));
                            },
                            editable: false,
                            selectable: false,
                            dayMaxEvents: true,
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
                    },

                    formatDateRange(start, end) {
                        if (!start) return '';
                        const options = { weekday: 'long', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit' };
                        let str = start.toLocaleDateString('en-US', options);
                        if (end) {
                            str += ' - ' + end.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                        }
                        return str;
                    },

                    refreshCalendar() {
                        if (this.calendar) {
                            this.calendar.refetchEvents();
                        }
                    }
                }
            }
        </script>
@endsection