<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 80%; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
        h1 { color: #0056b3; }
        p { margin-bottom: 10px; }
        .footer { margin-top: 20px; font-size: 0.8em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hello, {{ $data['name'] ?? 'Guest' }}!</h1>
        <p>This is an HTML email sent from your Laravel application.</p>
        <p><strong>Message:</strong></p>
        <p>{{ $data['message'] ?? 'No specific message was provided.' }}</p>
        <div class="footer">
            <p>Best regards,</p>
            <p>Your Application Team</p>
        </div>
    </div>
</body>
</html>
