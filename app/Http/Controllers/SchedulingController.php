<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Validator;
use App\Helpers\AuditLogger;
use Illuminate\Support\Facades\DB;

class SchedulingController extends Controller
{
    // View Methods
    public function index()
    {
        // For list view
        $events = DB::table('schedules')
            ->orderBy('start_datetime', 'asc')
            ->paginate(10);


        $service_types = ServiceType::all();
        $active_priests = \App\Models\User::where('role', 'Priest')->get();

        return view('modules.scheduling.index', compact('events', 'service_types', 'active_priests'));
    }

    public function calendar()
    {
        // For calendar view
        return view('modules.scheduling.calendar');
    }

    // API / CRUD Methods (Migrated from ScheduleController)

    public function getEvents(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $eventType = $request->query('event_type');
        $serviceType = $request->query('service_type');

        // Parse dates to Y-m-d to ensure database compatibility and performance
        $startDate = $start ? substr($start, 0, 10) : null;
        $endDate = $end ? substr($end, 0, 10) : null;

        $user = auth()->user();

        // Strict Filtering Logic: Hide the other category if one is specifically selected
        $fetchManual = true;
        $fetchServices = true;

        if ($eventType && $eventType !== 'All' && (!$serviceType || $serviceType === 'All')) {
            $fetchServices = false;
        }

        if ($serviceType && $serviceType !== 'All' && (!$eventType || $eventType === 'All')) {
            $fetchManual = false;
        }

        // 1. Fetch Manual Schedules
        $manualEvents = collect();
        if ($fetchManual) {
            $scheduleQuery = Schedule::query()->with(['priest' => function($q) {
                $q->select('id', 'name');
            }]);
            
            if ($startDate && $endDate) {
                $scheduleQuery->whereBetween('start_datetime', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }

            if ($eventType && $eventType !== 'All') {
                $scheduleQuery->where('type', $eventType);
            }

            $scheduleQuery->whereIn('status', ['Scheduled', 'Approved']);

            // ROLE FILTERING AND PRIEST PARAM FILTERING
            if ($user && $user->role === 'Priest') {
                $scheduleQuery->where('priest_id', $user->id);
            } else if ($request->filled('priest_id')) {
                $scheduleQuery->where('priest_id', $request->priest_id);
            }

            $manualEvents = $scheduleQuery->select(['id', 'title', 'start_datetime', 'end_datetime', 'type', 'description', 'status', 'priest_id'])
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => 'manual_' . $event->id,
                        'title' => $event->title,
                        'start' => $event->start_datetime->toIso8601String(),
                        'end' => $event->end_datetime->toIso8601String(),
                        'backgroundColor' => $this->getColorByType($event->type),
                        'borderColor' => $this->getColorByType($event->type),
                        'extendedProps' => [
                            'type' => $event->type,
                            'description' => e($event->description),
                            'source' => 'manual',
                            'priest_id' => $event->priest_id,
                            'priest_name' => $event->priest ? $event->priest->name : null
                        ]
                    ];
                });
        }

        // 2. Fetch Service Requests (Approved, Completed, OR Paid)
        $serviceEvents = collect();
        if ($fetchServices) {
            $requestQuery = \App\Models\ServiceRequest::query()
                ->select(['id', 'service_type', 'first_name', 'middle_name', 'last_name', 'suffix', 'fathers_name', 'mothers_name', 'contact_number', 'email', 'scheduled_date', 'scheduled_time', 'status', 'payment_status', 'details', 'requirements', 'priest_id', 'custom_data']);

            if ($startDate && $endDate) {
                $requestQuery->whereBetween('scheduled_date', [$startDate, $endDate]);
            }

            if ($serviceType && $serviceType !== 'All') {
                $requestQuery->where('service_type', $serviceType);
            }

        // Only show on calendar: Approved status OR Paid payment_status (except Cancelled/Completed)
        $requestQuery->where(function ($q) {
            $q->where('status', 'Approved')
              ->orWhere(function($sub) {
                  $sub->where('payment_status', 'Paid')
                      ->whereNotIn('status', ['Cancelled', 'Completed']);
              });
        });

        if ($user && $user->role === 'Priest') {
            $requestQuery->where('priest_id', $user->id);
        } else if ($request->filled('priest_id')) {
            $requestQuery->where('priest_id', $request->priest_id);
        }

        $serviceEvents = $requestQuery->get()->map(function ($req) {
            // Optimization: Parse manually to avoid overhead if possible, but keep robust parsing
            $startDt = $req->scheduled_date->copy();

            if ($req->scheduled_time) {
                // Combine date and time
                try {
                    // We can use the string directly since we selected the columns
                    $dtStr = $req->scheduled_date->format('Y-m-d') . ' ' . $req->scheduled_time;
                    $startDt = \Carbon\Carbon::parse($dtStr);
                } catch (\Exception $e) {
                    // Fallback to start of day
                }
            }

            // Parse custom_data for richer field values
            $cd = [];
            if (!empty($req->custom_data)) {
                $cd = is_string($req->custom_data) ? json_decode($req->custom_data, true) : (array) $req->custom_data;
                $cd = $cd ?? [];
            }

            // Helper: get value from custom_data by common key variants, skip N/A
            $cdGet = function (array $keys) use ($cd) {
                foreach ($keys as $key) {
                    foreach ($cd as $k => $v) {
                        if (strcasecmp(trim($k), $key) === 0 && !empty($v) && strtolower(trim($v)) !== 'n/a') {
                            return $v;
                        }
                    }
                }
                return null;
            };

            // Full applicant name (include suffix if present)
            $suffix = $req->suffix && strtolower(trim($req->suffix)) !== 'n/a' ? ' ' . trim($req->suffix) : '';
            $applicantName = trim(
                e($req->first_name) . ' ' .
                ($req->middle_name ? e($req->middle_name) . ' ' : '') .
                e($req->last_name) . $suffix
            );

            // Father: prefer custom_data, fallback to column
            $fatherName = $cdGet(["Father's Name", "Father's First Name", "father_first_name", "father_name"])
                ?? ($req->fathers_name && strtolower(trim($req->fathers_name)) !== 'n/a' ? $req->fathers_name : null);

            // Mother: prefer custom_data, fallback to column
            $motherName = $cdGet(["Mother's Name", "Mother's First Name", "mother_first_name", "mother_name"])
                ?? ($req->mothers_name && strtolower(trim($req->mothers_name)) !== 'n/a' ? $req->mothers_name : null);

            $description = "<strong>Services:</strong> " . e($req->service_type) . "\n";
            $description .= "<strong>Applicant:</strong> " . $applicantName . "\n";
            if ($fatherName)
                $description .= "<strong>Father:</strong> " . e($fatherName) . "\n";
            if ($motherName)
                $description .= "<strong>Mother:</strong> " . e($motherName) . "\n";
            $description .= "<strong>Contact:</strong> " . e($req->contact_number) . "\n";
            $description .= "<strong>Email:</strong> " . e($req->email) . "\n";
            if (!empty($req->details)) {
                $description .= "<strong>Notes:</strong> " . e($req->details) . "\n";
            }

            $reqs = is_string($req->requirements) ? json_decode($req->requirements, true) : $req->requirements;
            if (!empty($reqs) && is_array($reqs)) {
                $description .= "\n<strong>Requirements Submitted:</strong>\n- " . implode("\n- ", array_map('e', $reqs));
            }

            return [
                'id' => 'req_' . $req->id,
                'title' => $req->service_type . ' - ' . $req->last_name,
                'start' => $startDt->toIso8601String(),
                'end' => $startDt->copy()->addHour()->toIso8601String(),
                'backgroundColor' => $this->getColorByType($req->service_type),
                'borderColor' => $this->getColorByType($req->service_type),
                'extendedProps' => [
                    'type' => $req->service_type,
                    'description' => $description,
                    'source' => 'request'
                ]
            ];
        });
        }

        // Merge collections
        // Use toBase() to convert Eloquent Collection to Support Collection because map() apparently kept it as Eloquent collection in this context
        // or simply use concat which is more forgiving, or convert explicitly.
        $allEvents = $manualEvents->toBase()->merge($serviceEvents);

        return response()->json($allEvents);
    }

    private function getColorByType($type)
    {
        // Cache the service type colors for the duration of the request
        static $serviceColors = null;
        if ($serviceColors === null) {
            $serviceColors = \App\Models\ServiceType::pluck('color', 'name')->toArray();
        }

        // 1. Check if it's a dynamic service from the settings
        if (isset($serviceColors[$type])) {
            return $serviceColors[$type];
        }

        // 2. Fallback to hardcoded activity categories
        switch ($type) {
            case 'Mass':
                return '#dc2626'; // red
            case 'Special Mass':
                return '#f59e0b'; // amber
            case 'Parish Meeting':
                return '#0891b2'; // cyan
            case 'Novena':
                return '#84cc16'; // lime
            case 'Youth Activity':
                return '#f97316'; // orange
            case 'Community Service':
                return '#10b981'; // emerald
            
            case 'Baptism':
                return '#3b82f6'; // blue
            case 'Confirmation':
                return '#a855f7'; // purple
            case 'Wedding':
                return '#ec4899'; // pink
            case 'Funeral Mass':
            case 'Burial':
                return '#f43f5e'; // red-pink
            case 'Wake':
                return '#6366f1'; // indigo
            case 'Blessing':
                return '#eab308'; // yellow
                
            case 'Other':
            default:
                return '#64748b'; // slate
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:Mass,Special Mass,Parish Meeting,Novena,Youth Activity,Community Service,Other',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'description' => 'nullable|string',
            'priest_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!empty($request->priest_id)) {
            $availabilityError = $this->validatePriestAvailability(
                $request->priest_id,
                $request->start_datetime,
                null
            );

            if ($availabilityError) {
                return response()->json(['errors' => ['priest_id' => [$availabilityError]]], 422);
            }
        }

        $schedule = Schedule::create([
            'title' => $request->title,
            'type' => $request->type,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'description' => $request->description,
            'priest_id' => $request->priest_id,
            'status' => 'Scheduled'
        ]);

        AuditLogger::log('Create Schedule', 'Created event: ' . $schedule->title);

        return response()->json(['message' => 'Event created successfully!', 'event' => $schedule]);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:Mass,Special Mass,Parish Meeting,Novena,Youth Activity,Community Service,Other',
            'start_datetime' => 'sometimes|required|date',
            'end_datetime' => 'sometimes|required|date|after_or_equal:start_datetime',
            'description' => 'nullable|string',
            'priest_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!empty($request->priest_id)) {
            $availabilityError = $this->validatePriestAvailability(
                $request->priest_id,
                $request->start_datetime ?? $schedule->start_datetime,
                $id
            );

            if ($availabilityError) {
                return response()->json(['errors' => ['priest_id' => [$availabilityError]]], 422);
            }
        }

        $schedule->update($request->all());

        AuditLogger::log('Update Schedule', 'Updated event: ' . $schedule->title);

        return response()->json(['message' => 'Event updated successfully!', 'event' => $schedule]);
    }

    public function destroy($id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $schedule->delete();

        AuditLogger::log('Delete Schedule', 'Deleted event: ' . $schedule->title);

        return response()->json(['message' => 'Event deleted successfully!']);
    }

    /**
     * Validates if the assigned priest is available on the given date and time.
     */
    private function validatePriestAvailability($priestId, $startDatetime, $excludeScheduleId = null)
    {
        if (!$priestId) return null;

        $priest = \App\Models\User::find($priestId);
        if (!$priest) return null;

        $carbonDate = \Carbon\Carbon::parse($startDatetime);
        $dayName = $carbonDate->format('l');

        // 1. Check Working Days
        $workingDays = $priest->working_days ?? [];
        if (!empty($workingDays) && !in_array($dayName, $workingDays)) {
            return "The selected priest is not available on {$dayName}s.";
        }

        // 2. Check Working Hours
        $workingHours = $priest->working_hours ?? [];
        if (!empty($workingHours['start']) && !empty($workingHours['end'])) {
            $checkTime = $carbonDate->format('H:i');
            $startTime = \Carbon\Carbon::parse($workingHours['start'])->format('H:i');
            $endTime = \Carbon\Carbon::parse($workingHours['end'])->format('H:i');

            if ($checkTime < $startTime || $checkTime > $endTime) {
                return "The selected time ({$checkTime}) is outside the priest's working hours ({$startTime} - {$endTime}).";
            }
        }

        // 3. Check Max Capacity
        $maxServices = $priest->max_services_per_day;
        if ($maxServices && $maxServices > 0) {
            $requestCountQuery = \App\Models\ServiceRequest::where('priest_id', $priestId)
                ->whereDate('scheduled_date', $carbonDate->toDateString())
                ->whereNotIn('status', ['Cancelled', 'Declined']);

            $scheduleCountQuery = Schedule::where('priest_id', $priestId)
                ->whereDate('start_datetime', $carbonDate->toDateString())
                ->whereNotIn('status', ['Cancelled']);
            
            if ($excludeScheduleId) {
                $scheduleCountQuery->where('id', '!=', $excludeScheduleId);
            }

            $totalBooked = $requestCountQuery->count() + $scheduleCountQuery->count();

            if ($totalBooked >= $maxServices) {
                return "The selected priest has reached their maximum capacity of {$maxServices} services for this date.";
            }
        }

        return null;
    }
}
