<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to VitaLink</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eeeeee;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            padding: 20px 0;
        }
        .content h1 {
            font-size: 24px;
            color: #333;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
        }
        .button-container {
            text-align: center;
            padding: 20px 0;
        }
        .button {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 15px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            {{-- You can replace this with your actual logo URL --}}
            <img src="{{ asset('images/vitalink-logo.png') }}" alt="VitaLink Logo">
        </div>
        <div class="content">
            <h1>Welcome to VitaLink, {{ $user->name }}!</h1>
            <p>Your account has been created successfully. To get started, you need to set up your password.</p>
            <p>Please click the button below to securely set up your password and log in to your account. This link is valid for 24 hours.</p>
            <div class="button-container">
                <a href="{{ route('password.setup.show', ['token' => $token]) }}" class="button">Set Up Your Password</a>
            </div>
            <p>If you did not expect to receive this email, you can safely ignore it.</p>
            <p>Thanks,<br>The VitaLink Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} VitaLink. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
