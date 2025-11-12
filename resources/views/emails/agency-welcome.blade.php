<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-g">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to VitalLink</title>
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
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #052a4d; /* VitalLink Dark Blue */
        }
        .content {
            padding: 30px;
        }
        .content p {
            margin-bottom: 20px;
        }
        .credentials {
            background-color: #fdfdfd;
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 5px;
        }
        .credentials strong {
            display: inline-block;
            width: 100px;
        }
        .button {
            display: inline-block;
            background-color: #1e40af; /* VitalLink Blue */
            color: #ffffff;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>VitalLink</h1>
        </div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            
            <p>Welcome to VitalLink! Your agency, <strong>{{ $agency->name }}</strong>, has been successfully created by our administrative team.</p>
            
            <p>You can now log in to the platform to set up your agency, add caregivers, and start managing your clients.</p>

            <div class="credentials">
                <p><strong>Login URL:</strong> <a href="{{ config('app.url') }}/login">{{ config('app.url') }}/login</a></p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Password:</strong> {{ $password }}</sppn>
            </div>

            <p style="margin-top: 20px;">We highly recommend changing this temporary password immediately after your first login via your profile settings.</p>

            <a href="{{ config('app.url') }}/login" class="button">Log In Now</a>

            <p style="margin-top: 30px;">Thank you,
            <br>The VitalLink Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} VitalLink. All rights reserved.</p>
        </div>
    </div>
</body>
</html>