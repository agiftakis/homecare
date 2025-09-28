<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices for the agency.
     */
    public function index()
    {
        $agency = Auth::user()->agency;

        $invoices = Invoice::where('agency_id', $agency->id)
            ->with(['client', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate statistics for the dashboard
        $statistics = [
            'paid_count' => Invoice::where('agency_id', $agency->id)->where('status', 'paid')->count(),
            'pending_count' => Invoice::where('agency_id', $agency->id)->whereIn('status', ['draft', 'sent'])->count(),
            'overdue_count' => Invoice::where('agency_id', $agency->id)
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->count(),
            'total_revenue' => Invoice::where('agency_id', $agency->id)
                ->where('status', 'paid')
                ->sum('total_amount'),
        ];

        return view('invoices.index', compact('invoices', 'statistics'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        $agency = Auth::user()->agency;

        // Get clients with completed visits that haven't been invoiced
        $clients = Client::where('agency_id', $agency->id)
            ->whereHas('shifts.visit', function ($query) {
                $query->whereNotNull('clock_in_time')
                    ->whereNotNull('clock_out_time')
                    ->whereDoesntHave('invoiceItems');
            })
            ->get();

        return view('invoices.create', compact('clients'));
    }

    /**
     * Generate invoice from selected visits.
     */
    public function generate(Request $request)
    {
        // ✅ UPDATED: Added validation for hourly_rate
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'hourly_rate' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'visit_ids' => 'required|array|min:1',
            'visit_ids.*' => 'exists:visits,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $agency = Auth::user()->agency;
        $client = Client::findOrFail($request->client_id);

        // Verify client belongs to agency
        if ($client->agency_id !== $agency->id) {
            abort(403, 'Unauthorized access to client');
        }

        DB::beginTransaction();

        try {
            // Verify all selected visits belong to this client and are unbilled
            $visits = Visit::whereIn('id', $request->visit_ids)
                ->whereHas('shift', function ($query) use ($client) {
                    $query->where('client_id', $client->id);
                })
                ->whereNotNull('clock_in_time')
                ->whereNotNull('clock_out_time')
                ->whereDoesntHave('invoiceItems')
                ->get();

            if ($visits->count() !== count($request->visit_ids)) {
                throw new \Exception('Some selected visits are invalid or already billed.');
            }

            // Create the invoice
            $invoice = Invoice::create([
                'agency_id' => $agency->id,
                'client_id' => $client->id,
                'invoice_number' => Invoice::generateInvoiceNumber($agency->id),
                'invoice_date' => $request->invoice_date,
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'due_date' => $request->due_date,
                'subtotal' => 0,
                'tax_rate' => ($request->tax_rate ?: 0) / 100, // Convert percentage to decimal
                'tax_amount' => 0,
                'total_amount' => 0,
                'status' => 'draft',
                'client_name' => $client->first_name . ' ' . $client->last_name,
                'client_email' => $client->email,
                'client_address' => $client->address,
                'notes' => $request->notes,
            ]);

            // Create invoice items from selected visits
            foreach ($visits as $visit) {
                // ✅ UPDATED: Pass the custom hourly rate to the model method
                InvoiceItem::createFromVisit($visit, $invoice, $request->hourly_rate);
            }

            // Calculate totals
            $invoice->calculateTotals();

            DB::commit();

            // ✅ CORRECT FIX: Tell the success banner to "redirect" to the page we are already on.
            // This satisfies the banner's logic without navigating the user away from the new invoice.
            $destinationUrl = route('invoices.show', $invoice);

            return redirect($destinationUrl)
                ->with('success_message', 'Invoice Generated Successfully')
                ->with('redirect_to', $destinationUrl);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Failed to generate invoice: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'due_days' => 'required|integer|min:1|max:90',
            'tax_rate' => 'nullable|numeric|min:0|max:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $agency = Auth::user()->agency;
        $client = Client::findOrFail($request->client_id);

        // Verify client belongs to agency
        if ($client->agency_id !== $agency->id) {
            abort(403, 'Unauthorized access to client');
        }

        DB::beginTransaction();

        try {
            // Create the invoice
            $invoice = Invoice::create([
                'agency_id' => $agency->id,
                'client_id' => $client->id,
                'invoice_number' => Invoice::generateInvoiceNumber($agency->id),
                'invoice_date' => now()->toDateString(),
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'due_date' => now()->addDays($request->due_days)->toDateString(),
                'subtotal' => 0,
                'tax_rate' => $request->tax_rate ?: 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'status' => 'draft',
                'client_name' => $client->first_name . ' ' . $client->last_name,
                'client_email' => $client->email,
                'client_address' => $client->address,
                'notes' => $request->notes,
            ]);

            // Find completed visits for this client in the specified period
            $visits = Visit::whereHas('shift', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
                ->whereNotNull('clock_in_time')
                ->whereNotNull('clock_out_time')
                ->whereBetween(DB::raw('DATE(clock_in_time)'), [
                    $request->period_start,
                    $request->period_end
                ])
                ->whereDoesntHave('invoiceItems')
                ->get();

            if ($visits->isEmpty()) {
                throw new \Exception('No completed visits found for the selected period.');
            }

            // Create invoice items from visits
            foreach ($visits as $visit) {
                InvoiceItem::createFromVisit($visit, $invoice, $visit->shift->hourly_rate);
            }

            // Calculate totals
            $invoice->calculateTotals();

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} created successfully with {$visits->count()} visits.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        // Verify invoice belongs to user's agency
        if ($invoice->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        $invoice->load(['client', 'items.visit', 'agency']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * ✅ NEW: Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        // Verify invoice belongs to user's agency
        if ($invoice->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        // Only allow editing of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        return view('invoices.edit', compact('invoice'));
    }

    /**
     * ✅ UPDATED: Update the specified invoice in storage, now with hourly rate.
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Verify invoice belongs to user's agency
        if ($invoice->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        // Only allow editing of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'This invoice cannot be edited as it is no longer a draft.');
        }

        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'hourly_rate' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        DB::transaction(function () use ($invoice, $validated) {
            // Update the main invoice details
            $invoice->update([
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'tax_rate' => ($validated['tax_rate'] ?: 0) / 100,
                'notes' => $validated['notes'],
            ]);

            // Loop through each item, update the rate, and recalculate the line total
            foreach ($invoice->items as $item) {
                $item->hourly_rate = $validated['hourly_rate'];
                $item->line_total = $item->hours_worked * $validated['hourly_rate'];
                $item->save();
            }

            // Recalculate the invoice's grand totals based on the updated items
            $invoice->calculateTotals();
        });
        
        // ✅ FINAL REDIRECT FIX: Add the 'redirect_to' session flash
        $destinationUrl = route('invoices.show', $invoice);

        return redirect($destinationUrl)
            ->with('success_message', 'Invoice updated successfully.')
            ->with('redirect_to', $destinationUrl);
    }

    /**
     * Download invoice as PDF (placeholder implementation).
     */
    public function downloadPdf(Invoice $invoice)
    {
        // Verify invoice belongs to user's agency
        if ($invoice->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        // todo:Implement PDF generation
        // For now, return a message indicating the feature is coming soon
        return redirect()->route('invoices.show', $invoice)
            ->with('info', 'PDF generation feature is coming soon. For now, you can print this page or save as PDF using your browser.');
    }

    /**
     * Mark the specified invoice as sent.
     */
    public function markAsSent(Invoice $invoice)
    {
        // Verify invoice belongs to user's agency
        if ($invoice->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        if ($invoice->status === 'draft') {
            $invoice->status = 'sent';
            $invoice->sent_at = now();
            $invoice->save();
            return redirect()->route('invoices.show', $invoice)->with('success_message', 'Invoice marked as sent.');
        }

        return redirect()->route('invoices.show', $invoice)->with('error', 'Only draft invoices can be marked as sent.');
    }

    /**
     * Mark the specified invoice as paid.
     */
    public function markAsPaid(Invoice $invoice)
    {
        // Verify invoice belongs to user's agency
        if ($invoice->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        if (in_array($invoice->status, ['draft', 'sent'])) {
            $invoice->status = 'paid';
            $invoice->paid_at = now();
            // If it was a draft, mark it as sent too
            if (is_null($invoice->sent_at)) {
                $invoice->sent_at = now();
            }
            $invoice->save();
            return redirect()->route('invoices.show', $invoice)->with('success_message', 'Invoice marked as paid.');
        }

        return redirect()->route('invoices.show', $invoice)->with('error', 'This invoice cannot be marked as paid.');
    }

    /**
     * Email the invoice to the client (placeholder).
     */
    public function sendInvoice(Invoice $invoice)
    {
        // Verify invoice belongs to user's agency
        if ($invoice->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }
        
        // TODO: Implement actual email sending logic here
        
        return redirect()->route('invoices.show', $invoice)
            ->with('info', 'Emailing invoices is a feature coming soon.');
    }

    /**
     * Get unbilled visits for a client within a date range (AJAX endpoint).
     */
    public function getUnbilledVisits(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $agency = Auth::user()->agency;
        $client = Client::findOrFail($request->client_id);

        if ($client->agency_id !== $agency->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $visits = Visit::whereHas('shift', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        })
            ->whereNotNull('clock_in_time')
            ->whereNotNull('clock_out_time')
            ->whereBetween(DB::raw('DATE(clock_in_time)'), [
                $request->period_start,
                $request->period_end
            ])
            ->whereDoesntHave('invoiceItems')
            ->with(['shift.caregiver'])
            ->get()
            ->map(function ($visit) {
                $shift = $visit->shift;
                $billingDetails = $shift->getBillingDetails();

                return [
                    'id' => $visit->id,
                    'date' => $visit->clock_in_time->format('M d, Y'),
                    'time_range' => $visit->clock_in_time->format('H:i') . ' - ' . $visit->clock_out_time->format('H:i'),
                    'actual_hours' => abs($billingDetails['actual_hours']),
                    'hours' => $billingDetails['billable_hours'], // This will be minimum 1.0
                    'is_minimum_billing' => $billingDetails['is_minimum_billing'],
                    'hourly_rate' => number_format($shift->hourly_rate, 2),
                    'total' => number_format($billingDetails['amount'], 2),
                    'service_type' => $shift->service_type,
                    'caregiver_name' => $shift->caregiver ?
                        $shift->caregiver->first_name . ' ' . $shift->caregiver->last_name :
                        InvoiceItem::extractCaregiverName($visit->signature_path),
                ];
            });

        return response()->json([
            'visits' => $visits,
            'total_hours' => $visits->sum('hours'), // This will reflect billable hours (minimum 1.0 each)
            'total_amount' => $visits->sum(function ($visit) {
                return floatval(str_replace(',', '', $visit['total']));
            }),
        ]);
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // Verify invoice belongs to user's agency
        if ($invoice->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        // Can only delete draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.index')
                ->with('error', 'Only draft invoices can be deleted.');
        }

        try {
            DB::beginTransaction();

            // Delete invoice items first
            $invoice->items()->delete();

            // Delete the invoice
            $invoice->delete();

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('invoices.index')
                ->with('error', 'Failed to delete invoice.');
        }
    }
}