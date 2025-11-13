<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Agency Inquiry</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: 20px auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #4F46E5; /* Indigo */
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .content-item {
            margin-bottom: 20px;
        }
        .content-item strong {
            display: block;
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .content-item span {
            font-size: 16px;
            color: #111;
        }
        .content-item .message {
            white-space: pre-wrap;
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
        }
        .footer {
            background-color: #f9f9f9;
            color: #888;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New VitaLink Agency Inquiry</h1>
        </div>
        <div class="content">
            <p style="font-size: 16px; margin-bottom: 25px;">You have received a new sales inquiry from the VitaLink website.</p>

            <div class="content-item">
                <strong>Agency Name:</strong>
                <span>{{ $inquiryData['agency_name'] }}</span>
            </div>

            <div class="content-item">
                <strong>Contact Name:</strong>
                <span>{{ $inquiryData['contact_name'] }}</span>
            </div>

            <div class="content-item">
                <strong>Contact Email:</strong>
                <span><a href="mailto:{{ $inquiryData['contact_email'] }}">{{ $inquiryData['contact_email'] }}</a></span>
            </div>

            <div class="content-item">
                <strong>Agency Location:</strong>
                <span>{{ $inquiryData['location'] }}</span>
            </div>

            @if (!empty($inquiryData['message']))
                <div class="content-item">
                    <strong>Message / Additional Info:</strong>
                    <div class="message">
                        {{ $inquiryData['message'] }}
                    </div>
                </div>
            @else
                <div class="content-item">
                    <strong>Message / Additional Info:</strong>
                    <span>No message provided.</span>
                </div>
            @endif

        </div>
        <div class="footer">
            This is an automated notification. Please follow up with the contact email provided.
        </div>
    </div>
</body>
</html>