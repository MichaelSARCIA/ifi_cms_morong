<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ApplicationForwarded;
use Spatie\GoogleCalendar\Event;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceRequest::with('priest')->whereNotIn('status', ['Completed', 'Approved'])->latest();

        if ($request->has('type') && $request->input('type') !== '') {
            $query->where('service_type', trim($request->input('type')));
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('date_filter')) {
            $query->whereDate('scheduled_date', request('date_filter'));
        }

        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "{$search}%"); // Only match starting substring to prevent catching '.com' domains
            });
        }

        // Priest Scoping: Only show requests assigned to the logged-in priest
        if (auth()->user()->role === 'Priest') {
            $query->where(function($q) {
                $q->where('priest_id', auth()->id())
                  ->orWhereNull('priest_id');
            });
        }

        $perPage = 10;
        $requests = $query->paginate($perPage)->withQueryString();
        $service_types = \App\Models\ServiceType::all();

        // Fetch active priests for the dropdown
        $active_priests = User::where('role', 'Priest')->get();

        // Get selected service details (requirements, fee)
        $selected_service = null;
        if ($request->has('type')) {
            $selected_service = \App\Models\ServiceType::where('name', $request->input('type'))->first();
        }

        // Check if there's a request_id to auto-open
        $autoOpenRequest = null;
        if (request()->filled('request_id')) {
            $autoOpenQuery = ServiceRequest::with('priest')->where('id', request('request_id'));
            if (auth()->user()->role === 'Priest') {
                $autoOpenQuery->where(function($q) {
                    $q->where('priest_id', auth()->id())
                      ->orWhereNull('priest_id');
                });
            }
            $autoOpenRequest = $autoOpenQuery->first();
        }

        return view('modules.service_requests.index', compact('requests', 'service_types', 'active_priests', 'selected_service', 'autoOpenRequest'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_type' => 'required|string',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable', // Allow AM/PM format
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'fathers_name' => 'nullable|string|max:255',
            'mothers_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string',
            'email' => 'nullable|email',
            'priest_id' => 'nullable|exists:users,id',
            'details' => 'nullable|string',
            'requirements' => 'nullable|array',
            'status' => 'nullable|in:Pending,For Priest Review,For Payment,Approved,Completed,Cancelled,Declined',
            'payment_status' => 'nullable|in:Pending,Paid,Waived',
            'custom_data' => 'nullable|array', // Validate as array
        ]);

        // No need to manually json_encode if using 'array' cast in model, but for create() it might be needed depending on Laravel version.
        // However, since we added 'custom_data' => 'array' to casts, Laravel handles it.

        // Map custom_data to root columns if not provided at root (since UI fields were removed)
        if (!empty($validated['custom_data'])) {
            $cd = $validated['custom_data'];
            if (empty($validated['first_name'])) {
                $validated['first_name'] = $cd['first_name'] ?? ($cd['name'] ?? ($cd['applicant_name'] ?? null));
            }
            if (empty($validated['middle_name'])) {
                $validated['middle_name'] = $cd['middle_name'] ?? ($cd['middle_initial'] ?? null);
            }
            if (empty($validated['last_name'])) {
                $validated['last_name'] = $cd['last_name'] ?? ($cd['surname'] ?? ($cd['applicant_last_name'] ?? null));
            }
            if (empty($validated['suffix'])) {
                $validated['suffix'] = $cd['suffix'] ?? null;
            }
            if (empty($validated['contact_number'])) {
                $validated['contact_number'] = $cd['contact_number'] ?? ($cd['contact_no'] ?? ($cd['phone'] ?? ($cd['mobile'] ?? null)));
            }
            if (empty($validated['email'])) {
                $validated['email'] = $cd['email'] ?? ($cd['email_address'] ?? null);
            }
        }

        $validated['scheduled_time'] = $this->parseScheduledTime($validated['scheduled_time']);
        if ($validated['scheduled_time'] === false) {
            return back()->withErrors(['scheduled_time' => 'Invalid time format. Please use a valid time (e.g. 10:00 AM).'])->withInput();
        }
        // Validate Priest Availability if a priest is assigned
        if (!empty($validated['priest_id'])) {
            $availabilityError = $this->validatePriestAvailability(
                $validated['priest_id'], 
                $validated['scheduled_date'], 
                $validated['scheduled_time'] ?? null
            );

            if ($availabilityError) {
                return back()->withErrors(['priest_id' => $availabilityError])->withInput();
            }
        }

        // Default to For Priest Review for new applications since the dropdown was removed
        $validated['status'] = 'For Priest Review';
        $validated['payment_status'] = 'Pending';

        $newRequest = ServiceRequest::create($validated);

        $clientName = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));
        if (empty($clientName)) $clientName = "Client #" . $newRequest->id;

        \App\Helpers\AuditLogger::log('Create Request', "Created service request for " . $clientName);

        // Notify Priest(s)
        $priestsToNotify = collect();
        if (!empty($validated['priest_id'])) {
            $priest = User::find($validated['priest_id']);
            if ($priest) {
                $priestsToNotify->push($priest);
            }
        }

        foreach ($priestsToNotify as $p) {
            $p->notify(new \App\Notifications\NewPendingRequestNotification($newRequest));
            if ($p->email) {
                try {
                    Mail::to($p->email)->send(new \App\Mail\NewPendingRequestMail($newRequest));
                } catch (\Exception $e) {
                    Log::error("Failed to send New Pending Request email: " . $e->getMessage());
                }
            }
        }

        // Notify Admins and Secretaries
        $staffToNotify = User::whereIn('role', ['Admin', 'Secretary'])->get();
        \Illuminate\Support\Facades\Notification::send($staffToNotify, new \App\Notifications\NewPendingRequestNotification($newRequest));

        return redirect()->back()->with('success', 'Services Request created successfully.');
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'service_type' => 'required|string',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable', // Allow AM/PM format
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'fathers_name' => 'nullable|string|max:255',
            'mothers_name' => 'nullable|string|max:255',
            'status' => 'required|in:Pending,For Priest Review,For Payment,Approved,Completed,Cancelled,Declined',
            'payment_status' => 'required|in:Pending,Paid,Waived',
            'contact_number' => 'nullable|string',
            'email' => 'nullable|email',
            'priest_id' => 'nullable|exists:users,id',
            'details' => 'nullable|string',
            'requirements' => 'nullable|array',
            'custom_data' => 'nullable|array',
        ]);

        $validated['scheduled_time'] = $this->parseScheduledTime($validated['scheduled_time'] ?? null);
        if ($validated['scheduled_time'] === false) {
            return back()->withErrors(['scheduled_time' => 'Invalid time format. Please use a valid time (e.g. 10:00 AM).'])->withInput();
        }

        // Auto-sync payment status for manual approvals/completions from the Edit Modal
        if (in_array($validated['status'], ['Approved', 'Completed']) && $validated['payment_status'] === 'Pending') {
            $validated['payment_status'] = 'Paid';
        }
        // Validate Priest Availability if a priest is assigned
        if (!empty($validated['priest_id'])) {
            $availabilityError = $this->validatePriestAvailability(
                $validated['priest_id'], 
                $validated['scheduled_date'], 
                $validated['scheduled_time'] ?? null,
                $serviceRequest->id // EXCLUDE CURRENT REQUEST ID
            );

            if ($availabilityError) {
                return back()->withErrors(['priest_id' => $availabilityError])->withInput();
            }
        }

        $oldStatus = $serviceRequest->status;
        
        // Ensure custom_data is merged if provided
        if ($request->has('custom_data')) {
            $existingData = $serviceRequest->custom_data ?? [];
            $newData = $validated['custom_data'];
            $mergedData = array_merge($existingData, $newData);
            $validated['custom_data'] = $mergedData;

            // Map custom_data to root columns if not provided at root
            $cd = $validated['custom_data'];
            if (empty($validated['contact_number'])) {
                $validated['contact_number'] = $cd['contact_number'] ?? ($cd['contact_no'] ?? ($cd['phone'] ?? ($cd['mobile'] ?? null)));
            }
            if (empty($validated['email'])) {
                $validated['email'] = $cd['email'] ?? ($cd['email_address'] ?? null);
            }
        }

        $serviceRequest->update($validated);

        // Check if the application was just forwarded to the Priest
        if ($oldStatus !== 'For Priest Review' && $validated['status'] === 'For Priest Review') {
            \App\Helpers\AuditLogger::log('Application Forwarded', "Forwarded service request #" . $serviceRequest->id . " to Priest for schedule checking.");

            // Send Notification
            if (!empty($validated['priest_id'])) {
                $priest = User::find($validated['priest_id']);
                if ($priest) {
                    $priest->notify(new \App\Notifications\StatusUpdatedNotification($serviceRequest, $oldStatus, $validated['status']));
                    
                    if ($priest->email) {
                        try {
                            Mail::to($priest->email)->send(new ApplicationForwarded($serviceRequest));
                        } catch (\Exception $e) {
                            \Log::error("Failed to send Application Forwarded email: " . $e->getMessage());
                        }
                    }
                }
            }
        } else {
            \App\Helpers\AuditLogger::log('Update Request', "Updated service request #" . $serviceRequest->id);
            
            // Notify Admins and Secretaries if a status changes
            if (auth()->check() && auth()->user()->role === 'Priest' && $oldStatus !== $validated['status']) {
                $staff = \App\Models\User::whereIn('role', ['Admin', 'Secretary'])->get();
                \Illuminate\Support\Facades\Notification::send($staff, new \App\Notifications\StatusUpdatedNotification($serviceRequest, $oldStatus, $validated['status']));
            }

            // Notify Treasurers if a status changes to For Payment
            if ($oldStatus !== 'For Payment' && $validated['status'] === 'For Payment') {
                $treasurers = \App\Models\User::where('role', 'Treasurer')->get();
                if ($treasurers->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send($treasurers, new \App\Notifications\NewPaymentRequestNotification($serviceRequest));
                }


            }
        }

        // Handle Remarks
        if ($request->filled('remarks')) {
            $remarkText = $request->input('remarks');
            // custom_data is cast as 'array' in the model, access it directly
            $customData = is_array($serviceRequest->custom_data) ? $serviceRequest->custom_data : [];
            $remarksHistory = $customData['remarks_history'] ?? [];
            
            $remarksHistory[] = [
                'author' => auth()->user()->name ?? 'System',
                'role' => auth()->user()->role ?? 'System',
                'remark' => $remarkText,
                'created_at' => now()->toIso8601String()
            ];
            
            $customData['remarks_history'] = $remarksHistory;
            $serviceRequest->custom_data = $customData; // Model cast handles JSON serialization
            $serviceRequest->save();

            // Notify specific roles depending on who added the remark
            if (auth()->check()) {
                $authorRole = auth()->user()->role;
                if ($authorRole === 'Priest' || $authorRole === 'Treasurer') {
                    // Notify Admins
                    $admins = User::where('role', 'Admin')->get();
                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\RemarkAddedNotification($serviceRequest, auth()->user()->name, $remarkText));
                    
                    // Cross-notify (Priest <-> Treasurer)
                    if ($authorRole === 'Priest') {
                        $treasurers = User::where('role', 'Treasurer')->get();
                        \Illuminate\Support\Facades\Notification::send($treasurers, new \App\Notifications\RemarkAddedNotification($serviceRequest, auth()->user()->name, $remarkText));
                    } else {
                        if ($serviceRequest->priest_id) {
                            $priest = User::find($serviceRequest->priest_id);
                            if ($priest) $priest->notify(new \App\Notifications\RemarkAddedNotification($serviceRequest, auth()->user()->name, $remarkText));
                        } else {
                            $priests = User::where('role', 'Priest')->get();
                            \Illuminate\Support\Facades\Notification::send($priests, new \App\Notifications\RemarkAddedNotification($serviceRequest, auth()->user()->name, $remarkText));
                        }
                    }
                } elseif ($authorRole === 'Admin') {
                    // Notify the Priest assigned
                    if ($serviceRequest->priest_id) {
                        $priest = User::find($serviceRequest->priest_id);
                        if ($priest) {
                            $priest->notify(new \App\Notifications\RemarkAddedNotification($serviceRequest, auth()->user()->name, $remarkText));
                        }
                    } else {
                        $priests = User::where('role', 'Priest')->get();
                        \Illuminate\Support\Facades\Notification::send($priests, new \App\Notifications\RemarkAddedNotification($serviceRequest, auth()->user()->name, $remarkText));
                    }
                    
                    // Also notify Treasurers
                    $treasurers = User::where('role', 'Treasurer')->get();
                    \Illuminate\Support\Facades\Notification::send($treasurers, new \App\Notifications\RemarkAddedNotification($serviceRequest, auth()->user()->name, $remarkText));
                }
            }
        }

        if ($validated['status'] === 'For Payment' && $oldStatus !== 'For Payment') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Request confirmed. Status updated to For Payment.', 'status' => 'success']);
            }
            return redirect()->back()->with('success', 'Request confirmed. Status updated to For Payment.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Services Request updated successfully.', 'status' => 'success']);
        }
        return redirect()->back()->with('success', 'Services Request updated successfully.');
    }

    public function destroy(ServiceRequest $serviceRequest)
    {
        $serviceRequest->delete();

        \App\Helpers\AuditLogger::log('Delete Request', "Deleted service request #" . $serviceRequest->id);

        return redirect()->back()->with('success', 'Services Request deleted successfully.');
    }

    /**
     * Validates if the assigned priest is available on the given date and time.
     * Returns an error message string if unavailable, or null if available.
     */
    private function validatePriestAvailability($priestId, $date, $time = null, $excludeRequestId = null)
    {
        if (!$priestId) return null;

        $priest = User::find($priestId);
        if (!$priest) return null;

        $carbonDate = \Carbon\Carbon::parse($date);
        $dayName = $carbonDate->format('l'); // 'Monday', 'Tuesday', etc.

        // 1. Check Working Days
        $workingDays = $priest->working_days ?? [];
        if (!empty($workingDays)) {
            // Ensure we are comparing correctly (Trim and same case)
            $workingDays = array_map(fn($d) => trim(ucfirst(strtolower($d))), $workingDays);
            $currentDay = trim($dayName);
            
            if (!in_array($currentDay, $workingDays)) {
                \Log::warning("Priest Availability Denied: Priest #{$priestId} does not work on {$currentDay}. Working days: " . implode(', ', $workingDays));
                return "The selected priest is not available on {$currentDay}s.";
            }
        }

        // 2. Check Working Hours
        if ($time) {
            $workingHours = $priest->working_hours ?? [];
            if (!empty($workingHours['start']) && !empty($workingHours['end'])) {
                $checkTime = \Carbon\Carbon::parse($time)->format('H:i');
                $startTime = \Carbon\Carbon::parse($workingHours['start'])->format('H:i');
                $endTime = \Carbon\Carbon::parse($workingHours['end'])->format('H:i');

                if ($checkTime < $startTime || $checkTime > $endTime) {
                    return "The selected time ({$checkTime}) is outside the priest's working hours ({$startTime} - {$endTime}).";
                }
            }
        }

        // 3. Check Max Capacity
        $maxServices = $priest->max_services_per_day;
        if ($maxServices && $maxServices > 0) {
            // Count existing Service Requests for this priest on this date (not cancelled)
            $requestCountQuery = ServiceRequest::where('priest_id', $priestId)
                ->whereDate('scheduled_date', $carbonDate->toDateString())
                ->whereNotIn('status', ['Cancelled', 'Declined']);
            
            if ($excludeRequestId) {
                $requestCountQuery->where('id', '!=', $excludeRequestId);
            }

            // Also check Schedules table for manually added schedules
            $scheduleCountQuery = \App\Models\Schedule::where('priest_id', $priestId)
                ->whereDate('start_datetime', $carbonDate->toDateString())
                ->whereNotIn('status', ['Cancelled']);

            $totalBooked = $requestCountQuery->count() + $scheduleCountQuery->count();

            if ($totalBooked >= $maxServices) {
                return "The selected priest has reached their maximum capacity of {$maxServices} services for this date.";
            }
        }
        return null; // Available
    }

    public function checkAvailability(Request $request)
    {
        $priestId = $request->query('priest_id');
        $date = $request->query('date');
        $time = $request->query('time');
        $excludeId = $request->query('exclude_id');

        if (!$priestId || !$date) {
            return response()->json(['available' => true]);
        }

        $parsedTime = $this->parseScheduledTime($time);
        
        $error = $this->validatePriestAvailability($priestId, $date, $parsedTime ?: null, $excludeId);

        if ($error) {
            return response()->json([
                'available' => false,
                'message' => $error
            ]);
        }

        return response()->json(['available' => true]);
    }

    /**
     * Helper to parse the scheduled time and return false on failure or null if absent.
     */
    private function parseScheduledTime($timeInput)
    {
        if (empty($timeInput)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($timeInput)->format('H:i:s');
        } catch (\Exception $e) {
            \Log::warning('Could not parse scheduled_time: ' . $timeInput);
            return false; // Indicator for failure
        }
    }
}
