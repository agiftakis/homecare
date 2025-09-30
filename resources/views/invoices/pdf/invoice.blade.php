<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    {{-- PHP block to securely embed the logo --}}
    @php
        $logoPath = public_path('images/vitalink-logo.png');
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        } else {
            $logoSrc = ''; // Fallback if logo is not found
        }
    @endphp
    <style>
        /* A professional blue color scheme */
        :root {
            --primary-color: #3490dc;
            --secondary-color: #f3f4f6;
            --text-color: #374151;
            --heading-color: #111827;
            --border-color: #e5e7eb;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #374151; /* --text-color */
        }
        .container {
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 30px;
        }
        .header-table .logo-container {
            width: 50%;
            vertical-align: top;
        }
        .header-table .logo {
            max-width: 150px;
            height: auto;
        }
        .header-table .invoice-title-container {
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .header-table h1 {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
            color: #111827; /* --heading-color */
        }
        .invoice-details p {
            margin: 2px 0;
            font-size: 12px;
        }
        .addresses-table {
            margin-bottom: 30px;
        }
        .addresses-table td {
            width: 50%;
            vertical-align: top;
        }
        .addresses-table h2 {
            font-size: 13px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #3490dc; /* --primary-color */
        }
        .items-table th {
            background-color: #3490dc; /* --primary-color */
            color: #ffffff;
            font-weight: bold;
            padding: 10px 8px;
            text-align: left;
            font-size: 12px;
        }
        .items-table td {
            border-bottom: 1px solid #e5e7eb; /* --border-color */
            padding: 10px 8px;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #f3f4f6; /* --secondary-color */
        }
        .items-table .text-right {
            text-align: right;
        }
        
        /* START: BULLETPROOF TOTALS FIX */
        .totals-section {
            page-break-inside: avoid;
            margin-top: 20px;
        }
        .totals-container {
            width: 45%;
            float: right;
        }
        .totals-row {
            /* This "clearfix" is for the floated spans inside */
            overflow: auto; 
            padding: 6px 8px;
        }
        .totals-row .label {
            float: left;
            text-align: right;
            font-weight: bold;
            color: #374151;
            width: 65%;
        }
        .totals-row .amount {
            float: right;
            text-align: right;
            width: 35%;
        }
        .totals-row.total-row {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            border-top: 2px solid #111827;
            margin-top: 5px;
            padding-top: 10px;
        }
        /* END: BULLETPROOF TOTALS FIX */

        .notes-section {
            /* clear:both is needed to position this below the floated totals */
            clear: both;
            page-break-inside: avoid;
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb; /* --border-color */
        }
        .notes-section h3 {
            font-size: 13px;
            font-weight: bold;
            color: #3490dc; /* --primary-color */
            margin-bottom: 5px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb; /* --border-color */
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <table class="header-table">
            <tr>
                <td class="logo-container">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Agency Logo" class="logo">
                    @else
                        <h1>{{ $invoice->agency->name }}</h1>
                    @endif
                </td>
                <td class="invoice-title-container">
                    <h1>INVOICE</h1>
                    <div class="invoice-details">
                        <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                        <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                        <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
                    </div>
                </td>
            </tr>
        </table>

        <table class="addresses-table">
            <tr>
                <td>
                    <h2>From:</h2>
                    <strong>{{ $invoice->agency->name }}</strong><br>
                    @if ($invoice->agency->address)
                        {{ $invoice->agency->address }}<br>
                    @endif
                    {{ $invoice->agency->contact_email }}
                </td>
                <td>
                    <h2>Bill To:</h2>
                    <strong>{{ $invoice->client_name }}</strong><br>
                    @if($invoice->client_address)
                        {{ $invoice->client_address }}<br>
                    @endif
                    {{ $invoice->client_email }}
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Service Date</th>
                    <th>Description</th>
                    <th class="text-right">Hours</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ $item->service_date->format('M d, Y') }}</td>
                        <td>{{ $item->service_description }}</td>
                        <td class="text-right">{{ number_format($item->hours_worked, 2) }}</td>
                        <td class="text-right">${{ number_format($item->hourly_rate, 2) }}</td>
                        <td class="text-right">${{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- START: BULLETPROOF TOTALS FIX --}}
        <div class="totals-section">
            <div class="totals-container">
                <div class="totals-row">
                    <span class="label">Subtotal:</span>
                    <span class="amount">${{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                <div class="totals-row">
                    <span class="label">Tax ({{ number_format($invoice->tax_rate * 100, 2) }}%):</span>
                    <span class="amount">${{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="totals-row total-row">
                    <span class="label">Total:</span>
                    <span class="amount">${{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
        {{-- END: BULLETPROOF TOTALS FIX --}}

        @if ($invoice->notes)
            <div class="notes-section">
                <h3>Notes:</h3>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

        <div class="footer">
            Thank you for your business! | {{ $invoice->agency->name }} | {{ $invoice->agency->phone ?? '' }}
        </div>
    </div>
</body>
</html>