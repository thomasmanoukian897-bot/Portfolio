<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Password Change Verification</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #1e293b;">
    <h1 style="font-size: 1.25rem; margin-bottom: 1rem;">Verify your password change</h1>

    <p>Hi {{ $user->name }},</p>

    <p>We received a request to change the password for your Digital Builder account. Use the verification code below to confirm this change:</p>

    <p style="font-size: 1.5rem; font-weight: bold; letter-spacing: 0.25em; margin: 1.5rem 0;">{{ $code }}</p>

    <p>This code expires in 15 minutes. If you did not request a password change, you can safely ignore this email.</p>
</body>
</html>
