<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sacrament;
use App\Helpers\AuditLogger;
use Illuminate\Support\Facades\DB;

class SacramentController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\ServiceRequest::query()
            ->whereIn('status', ['Approved', 'Completed'])
            ->with(['priest']);

        // Apply Search Filter (Applicant Name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
            });
        }

        // Apply Service Type Filter
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        // Apply Status Filter
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'Pending Completion' || $status === 'Pending') {
                $query->where('status', 'Approved');
            } else {
                $query->where('status', $status);
            }
        }

        $perPage = 10;
        $sacraments = $query->orderBy('updated_at', 'desc')
                            ->paginate($perPage)
                            ->withQueryString();
        
        // Fetch all service types for the filter dropdown
        $serviceTypes = \App\Models\ServiceType::pluck('name')->toArray();

        return view('modules.sacraments.index', compact('sacraments', 'serviceTypes'));
    }

    public function markComplete(Request $request, $id)
    {
        $service = \App\Models\ServiceRequest::findOrFail($id);

        if ($service->status !== 'Approved') {
            return back()->with('error', 'Only approved services can be marked as completed.');
        }

        try {
            $service->status = 'Completed';
            $service->save();
            AuditLogger::log('Complete Service', "Marked service request #{$service->id} ({$service->service_type}) as Completed");
            return back()->with('success', 'Service marked as completed successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to mark service complete: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while updating the service status. Please try again.');
        }
    }

    public function printCertificate($id)
    {
        $service = \App\Models\ServiceRequest::with(['priest'])->findOrFail($id);

        if ($service->status !== 'Completed') {
            return back()->with('error', 'Certificate can only be generated for completed services.');
        }

        try {
            // Create certificate record if it doesn't exist
            $certificate = $service->certificate;
            if (!$certificate) {
                $certificate = \App\Models\Certificate::create([
                    'service_request_id' => $service->id,
                    'certificate_number' => \App\Models\Certificate::generateCertificateNumber(),
                    'issued_at'          => now(),
                    'issued_by'          => auth()->id(),
                ]);
            }

            $serviceName = $service->serviceType ? $service->serviceType->name : 'Sacrament';
            $fullName = strtoupper($service->first_name . ' ' . $service->middle_name . ' ' . $service->last_name);

            // Generate PDF using Barryvdh/DomPDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.certificate', compact('service', 'certificate'))
                ->setPaper('a4', 'portrait');

            $filenameSafeLabel = preg_replace('/[^a-zA-Z0-9]+/', '_', $serviceName);
            $filename = $filenameSafeLabel . '_Certificate_' . preg_replace('/[^a-zA-Z0-9]+/', '_', $service->last_name) . '.pdf';

            AuditLogger::log('Print Certificate', 'Generated/Printed ' . $serviceName . ' Certificate for ' . trim($fullName));

            return $pdf->stream($filename);

        } catch (\Exception $e) {
            \Log::error('PDF Certificate generation failed: ' . $e->getMessage());
            return back()->with('error', 'Certificate generation failed: ' . $e->getMessage());
        }
    }
}
