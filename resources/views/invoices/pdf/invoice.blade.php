<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: right;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #000;
        }
        .header .invoice-details {
            margin-top: 10px;
        }
        .header .invoice-details p {
            margin: 2px 0;
        }
        .addresses {
            margin-bottom: 30px;
            overflow: hidden;
        }
        .addresses table {
            width: 100%;
        }
        .addresses .from, .addresses .to {
            width: 50%;
            vertical-align: top;
        }
        .addresses h2 {
            font-size: 14px;
            margin: 0 0 5px 0;
            color: #555;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .items-table .text-right {
            text-align: right;
        }
        .totals {
            width: 100%;
            margin-top: 20px;
        }
        .totals table {
            width: 40%;
            float: right;
        }
        .totals td {
            padding: 5px;
        }
        .totals .label {
            text-align: right;
            font-weight: bold;
            color: #555;
        }
        .totals .amount {
            text-align: right;
        }
        .notes {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .notes h3 {
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <h1>INVOICE</h1>
            <div class="invoice-details">
                <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="addresses">
            <table>
                <tr>
                    <td class="from">
                        <h2>From:</h2>
                        <strong>{{ $invoice->agency->name }}</strong><br>
                        @if ($invoice->agency->address)
                            {{ $invoice->agency->address }}<br>
                        @endif
                        {{ $invoice->agency->contact_email }}
                    </td>
                    <td class="to">
                        <h2>Bill To:</h2>
                        <strong>{{ $invoice->client_name }}</strong><br>
                        @if($invoice->client_address)
                            {{ $invoice->client_address }}<br>
                        @endif
                        {{ $invoice->client_email }}
                    </td>
                </tr>
            </table>
        </div>

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

        <div class="totals">
            <table>
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td class="label">Tax ({{ number_format($invoice->tax_rate * 100, 2) }}%):</td>
                    <td class="amount">${{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                {{-- ✅ FIX: Switched from a CSS class to a more reliable inline style for the top border --}}
                <tr>
                    <td class="label" style="border-top: 2px solid #333; padding-top: 10px; font-weight: bold; font-size: 14px; color: #000;">Total:</td>
                    <td class="amount" style="border-top: 2px solid #333; padding-top: 10px; font-weight: bold; font-size: 14px; color: #000;">${{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        @if ($invoice->notes)
            <div class="notes">
                <h3>Notes:</h3>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

    </div>
</body>
</html>