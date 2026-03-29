<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceType;
use App\Models\PaymentMethod;
use App\Helpers\AuditLogger;

class FinanceController extends Controller
{
    // --- COLLECTIONS ---

    public function collections(Request $request)
    {
        $query = DB::table('donations')->orderBy('created_at', 'desc');
        
        // Base collections type scope
        $query->whereIn('type', ['Collection', 'Mass Offering', 'Special Collection']);

        // Search scope
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('remarks', 'like', "%{$search}%");
            });
        }

        // Collection Type scope
        if ($request->filled('collection_type')) {
            $query->where('type', $request->collection_type);
        }

        $perPage = $request->input('per_page', 15);
        $collections = $query->paginate($perPage)->withQueryString();
        $service_types = ServiceType::all();
        return view('modules.finance.collections', compact('collections', 'service_types'));
    }

    public function storeCollection(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required',
            'amount' => 'required|numeric',
            'date_received' => 'required|date|before_or_equal:today',
            'remarks' => 'nullable'
        ]);

        DB::table('donations')->insert([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'donor_name' => 'Anonymous',
            'date_received' => $validated['date_received'],
            'remarks' => $validated['remarks'] ?? '',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        AuditLogger::log('Add Collection', "Recorded collection amount: ₱" . number_format($validated['amount'], 2));

        return redirect()->route('collections')->with('success', 'Collection recorded successfully');
    }

    // --- DONATIONS ---

    public function donations(Request $request)
    {
        $type = $request->query('type');
        $isFeePage = $type === 'fee';

        if ($isFeePage) {
            $query = \App\Models\ServiceRequest::with(['priest', 'payment'])
                ->where(function ($q) {
                    $q->where('status', 'For Payment')
                        ->orWhere('payment_status', 'Paid');
                })
                ->where('status', '!=', 'Cancelled');

            // Filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            }

            if ($request->filled('service_type')) {
                $query->where('service_type', $request->service_type);
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $perPage = $request->input('per_page', 10);
            $donations = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        } else {
            // Donations: Original logic
            $query = DB::table('donations')->orderBy('created_at', 'desc');
            $query->whereIn('type', ['Donation', 'Tithes', 'Love Offering']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('donor_name', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            }

            if ($request->filled('donation_type')) {
                $query->where('type', $request->donation_type);
            }

            $perPage = $request->input('per_page', 10);
            $donations = $query->paginate($perPage)->withQueryString();
        }

        $services = ServiceType::all();
        $payment_methods = PaymentMethod::active()->orderBy('sort_order')->get();

        return view('modules.finance.donations', compact('donations', 'services', 'isFeePage', 'payment_methods'));
    }

    public function storeDonation(Request $request)
    {
        $validated = $request->validate([
            'donor_name' => 'required',
            'type' => 'required',
            'amount' => 'required|numeric',
            'date_received' => 'required|date|before_or_equal:today',
            'remarks' => 'nullable|string'
        ]);

        DB::table('donations')->insert([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'donor_name' => $validated['donor_name'],
            'date_received' => $validated['date_received'],
            'remarks' => $validated['remarks'] ?? '', // Save remarks (Service Name)
            'created_at' => now(),
            'updated_at' => now()
        ]);

        AuditLogger::log('Add Donation', "Recorded donation from " . $validated['donor_name'] . " amount: ₱" . number_format($validated['amount'], 2));

        return redirect()->route('donations')->with('success', 'Donation recorded successfully');
    }

    // --- PAYMENT PROCESSING ---

    public function processPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_method'   => 'required|string',
            'reference_number' => 'nullable|string',
            'amount'           => 'required|numeric|min:0',
            'amount_tendered'  => 'nullable|numeric|min:0',
        ]);

        $serviceRequest = \App\Models\ServiceRequest::findOrFail($id);

        // Guard: already paid AND has a payment record
        if ($serviceRequest->payment_status === 'Paid' && $serviceRequest->payment()->exists()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'This service request has already been paid and has a recorded receipt.'], 422);
            }
            return back()->with('error', 'This service request has already been paid and has a recorded receipt.');
        }

        try {
            $payment = DB::transaction(function () use ($validated, $serviceRequest) {
                // Create payment record
                $payment = \App\Models\Payment::create([
                    'service_request_id' => $serviceRequest->id,
                    'amount'             => $validated['amount'],
                    'amount_tendered'    => $validated['amount_tendered'] ?? null,
                    'payment_method'     => $validated['payment_method'],
                    'reference_number'   => $validated['reference_number'] ?? null,
                    'receipt_number'     => \App\Models\Payment::generateReceiptNumber(),
                    'paid_at'            => now(),
                    'processed_by'       => auth()->id(),
                ]);

                // Update service request status atomically
                $serviceRequest->update([
                    'payment_status' => 'Paid',
                    'status'         => 'Approved',
                ]);

                return $payment;
            });

            AuditLogger::log('Process Payment', "Processed payment for service request #{$serviceRequest->id} - Receipt: {$payment->receipt_number}");

            // Notify Admins and Secretaries
            $staff = \App\Models\User::whereIn('role', ['Admin', 'Secretary'])->get();
            \Illuminate\Support\Facades\Notification::send($staff, new \App\Notifications\PaymentProcessedNotification($serviceRequest));

            // Notify Priest (NEW: synchronized with staff/treasurers)
            if ($serviceRequest->priest_id) {
                $assignedPriest = \App\Models\User::find($serviceRequest->priest_id);
                if ($assignedPriest) {
                    $assignedPriest->notify(new \App\Notifications\PaymentProcessedNotification($serviceRequest));
                }
            } else {
                $allPriests = \App\Models\User::where('role', 'Priest')->get();
                \Illuminate\Support\Facades\Notification::send($allPriests, new \App\Notifications\PaymentProcessedNotification($serviceRequest));
            }

            // Notify Treasurers (NEW: synchronized)
            $treasurers = \App\Models\User::where('role', 'Treasurer')->get();
            \Illuminate\Support\Facades\Notification::send($treasurers, new \App\Notifications\PaymentProcessedNotification($serviceRequest));

            if ($request->wantsJson()) {
                return response()->json([
                    'success'            => true,
                    'message'            => 'Payment processed successfully!',
                    'payment_id'         => $payment->id,
                    'receipt_url'        => route('payments.receipt', $payment->id),
                    'new_status'         => 'Approved',
                    'new_payment_status' => 'Paid',
                ]);
            }

            return back()->with('success', 'Payment processed successfully! You can now print the receipt.');

        } catch (\Exception $e) {
            \Log::error('Payment processing failed for request #' . $id . ': ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Payment processing failed. Please try again.'], 500);
            }
            return back()->with('error', 'Payment processing failed. Please try again or contact support.');
        }
    }

    public function downloadReceipt($id)
    {
        $payment = \App\Models\Payment::with(['serviceRequest', 'processor'])->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt', compact('payment'));

        AuditLogger::log('Print Receipt', "Generated/Printed receipt " . $payment->receipt_number . " for Service Request #" . $payment->service_request_id);

        return $pdf->stream('receipt-' . $payment->receipt_number . '.pdf');
    }
}
