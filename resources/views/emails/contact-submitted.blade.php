<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Contact Form Submission</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #1e293b;">
    <h1 style="font-size: 1.25rem; margin-bottom: 1rem;">New contact form submission</h1>

    <p><strong>Name:</strong> {{ $name }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Subject:</strong> {{ $contactSubject }}</p>

    <p><strong>Message:</strong></p>
    <p style="white-space: pre-wrap;">{{ $body }}</p>
</body>
</html>
