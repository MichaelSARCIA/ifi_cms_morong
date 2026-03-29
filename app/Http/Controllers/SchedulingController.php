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
        $type = $request->query('type');

        // Parse dates to Y-m-d to ensure database compatibility and performance
        $startDate = $start ? substr($start, 0, 10) : null;
        $endDate = $end ? substr($end, 0, 10) : null;

        // 1. Fetch Manual Schedules
        $scheduleQuery = Schedule::query();
        if ($startDate && $endDate) {
            $scheduleQuery->whereBetween('start_datetime', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        if ($type && $type !== 'All') {
            $scheduleQuery->where('type', $type);
        }

        $scheduleQuery->whereIn('status', ['Scheduled', 'Approved']);

        $manualEvents = $scheduleQuery->select(['id', 'title', 'start_datetime', 'end_datetime', 'type', 'description', 'status'])
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
                        'source' => 'manual'
                    ]
                ];
            });

        // 2. Fetch Service Requests (Approved, Completed, OR Paid)
        // Select only necessary columns to improve performance
        $requestQuery = \App\Models\ServiceRequest::query()
            ->select(['id', 'service_type', 'first_name', 'middle_name', 'last_name', 'fathers_name', 'mothers_name', 'contact_number', 'email', 'scheduled_date', 'scheduled_time', 'status', 'payment_status', 'details', 'requirements', 'priest_id']);

        if ($startDate && $endDate) {
            $requestQuery->whereBetween('scheduled_date', [$startDate, $endDate]);
        }

        if ($type && $type !== 'All') {
            $requestQuery->where('service_type', $type);
        }

        // Include Paid requests regardless of main status (except cancelled), OR Approved
        $requestQuery->where(function ($q) {
            $q->whereIn('status', ['Approved'])
                ->orWhere('payment_status', 'Paid');
        });

        // ROLE FILTERING AND PRIEST PARAM FILTERING
        $user = auth()->user();
        if ($user && $user->role === 'Priest') {
            $requestQuery->where('priest_id', $user->id);
            $scheduleQuery->where('priest_id', $user->id);
        } else if ($request->filled('priest_id')) {
            $requestQuery->where('priest_id', $request->priest_id);
            $scheduleQuery->where('priest_id', $request->priest_id);
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

            $description = "<strong>Services:</strong> " . e($req->service_type) . "\n";
            $description .= "<strong>Applicant:</strong> " . e($req->first_name) . " " . ($req->middle_name ? e($req->middle_name) . " " : "") . e($req->last_name) . "\n";
            if ($req->fathers_name)
                $description .= "<strong>Father:</strong> " . e($req->fathers_name) . "\n";
            if ($req->mothers_name)
                $description .= "<strong>Mother:</strong> " . e($req->mothers_name) . "\n";
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

        // Merge collections
        // Use toBase() to convert Eloquent Collection to Support Collection because map() apparently kept it as Eloquent collection in this context
        // or simply use concat which is more forgiving, or convert explicitly.
        $allEvents = $manualEvents->toBase()->merge($serviceEvents);

        return response()->json($allEvents);
    }

    private function getColorByType($type)
    {
        switch ($type) {
            case 'Mass':
                return '#8b5cf6'; // violet
            case 'Special Mass':
                return '#a855f7'; // purple
            case 'Parish Meeting':
                return '#3b82f6'; // blue
            case 'Novena':
                return '#6366f1'; // indigo
            case 'Youth Activity':
                return '#0ea5e9'; // sky
            case 'Community Service':
                return '#22c55e'; // green
            case 'Other':
                return '#f59e0b'; // amber
            
            // Sacrament services
            case 'Baptism':
                return '#3b82f6';
            case 'Wedding':
                return '#ec4899';
            case 'Burial':
                return '#6b7280';
            case 'Blessing':
                return '#f59e0b';
                
            default:
                return '#3788d8';
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
