<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Schedule;
use App\Models\ServiceRequest;

class PriestScheduleController extends Controller
{
    /**
     * Get the schedule configuration and booked slots for a specific priest.
     */
    public function getPriestSchedule(Request $request, $priestId)
    {
        $priest = User::where('role', 'Priest')->findOrFail($priestId);

        // Fetch user config
        $workingDays = $priest->working_days ?? [];
        $workingHours = $priest->working_hours ?? ['start' => '08:00', 'end' => '17:00'];
        $maxServices = $priest->max_services_per_day ?? 5;

        // Optionally, check specific date if requested (for time pickers)
        $date = $request->input('date');
        $bookedTimes = [];
        $fullyBookedDays = [];

        // To make the calendar truly dynamic, we could fetch ALL booked dates for this priest.
        // For simplicity, we just return the raw schedules & active requests so the frontend can disabled dates.
        
        $activeRequests = ServiceRequest::where('priest_id', $priestId)
            ->whereNotIn('status', ['Cancelled', 'Declined', 'Completed']) // completed shouldn't block future, but they are in the past anyway
            ->get(['scheduled_date', 'scheduled_time']);
            
        $activeSchedules = Schedule::where('priest_id', $priestId)
            ->whereNotIn('status', ['Cancelled'])
            ->get(['start_datetime', 'end_datetime']);

        return response()->json([
            'working_days' => $workingDays,
            'working_hours' => $workingHours,
            'max_services' => $maxServices,
            'active_requests' => $activeRequests,
            'active_schedules' => $activeSchedules,
        ]);
    }

    /**
     * Update the schedule configuration for a specific priest (Admin/Priest only)
     */
    public function updatePriestSchedule(Request $request, $priestId)
    {
        $priest = User::where('role', 'Priest')->findOrFail($priestId);

        $validated = $request->validate([
            'working_days' => 'nullable|array',
            'working_hours' => 'nullable|array',
            'working_hours.start' => 'nullable|date_format:H:i',
            'working_hours.end' => 'nullable|date_format:H:i',
            'max_services_per_day' => 'nullable|integer|min:1',
        ]);

        $priest->working_days = $validated['working_days'] ?? [];
        $priest->working_hours = $validated['working_hours'] ?? ['start' => '08:00', 'end' => '17:00'];
        $priest->max_services_per_day = $validated['max_services_per_day'] ?? 5;
        $priest->save();

        \App\Helpers\AuditLogger::log('Update Priest Schedule', "Updated schedule settings for {$priest->name}");

        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully!'
        ]);
    }
}
