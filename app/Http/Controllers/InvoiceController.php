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
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
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
                InvoiceItem::createFromVisit($visit, $invoice);
            }

            // Calculate totals
            $invoice->calculateTotals();

            DB::commit();

            // Flash success message and redirect
            session()->flash('success_message', 'Invoice Generated Successfully');
            session()->flash('redirect_to', route('invoices.show', $invoice));
            return redirect()->back();

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
                InvoiceItem::createFromVisit($visit, $invoice);
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
     * Update the specified invoice status.
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status' => 'required|in:draft,sent,paid,cancelled'
        ]);

        // Verify invoice belongs to user's agency
        if ($invoice->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        $oldStatus = $invoice->status;
        $newStatus = $request->status;

        // Handle status-specific logic
        if ($newStatus === 'sent' && $oldStatus !== 'sent') {
            $invoice->markAsSent();
        } elseif ($newStatus === 'paid' && $oldStatus !== 'paid') {
            $invoice->markAsPaid();
        } else {
            $invoice->update(['status' => $newStatus]);
        }

        $statusDisplay = ucfirst($newStatus);
        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice status updated to {$statusDisplay}.");
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
                    'actual_hours' => $billingDetails['actual_hours'],
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
            'total_amount' => $visits->sum(function($visit) { 
                return floatval(str_replace(',', '', $visit['total'])); 
            }),
        ]);
    }
}