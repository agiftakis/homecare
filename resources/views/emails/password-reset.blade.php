<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        /* Basic Styling */
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { text-align: center; margin-bottom: 20px; }
        .button { display: inline-block; padding: 12px 25px; margin: 20px 0; background-color: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px; }
        .footer { font-size: 0.9em; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Password Reset Request</h2>
        </div>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>We received a request to reset the password for your VitaLink account. You can reset your password by clicking the button below:</p>
        
        <a href="{{ $resetUrl }}" class="button">Reset Password</a>
        
        <p>This password reset link will expire in 60 minutes.</p>
        
        <p>If you did not request a password reset, no further action is required.</p>
        
        <hr>
        
        <p class="footer">If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
        <small>{{ $resetUrl }}</small>
        </p>
    </div>
</body>
</html>