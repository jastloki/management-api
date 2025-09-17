<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome {{ $client->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 400;
        }
        .content p {
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.8;
        }
        .highlight {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .client-info {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 6px;
            margin: 25px 0;
        }
        .client-info h3 {
            margin: 0 0 15px 0;
            color: #667eea;
            font-size: 18px;
        }
        .client-info p {
            margin: 8px 0;
            font-size: 14px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            margin: 20px 0;
            transition: transform 0.2s ease;
        }
        .button:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 0;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            .content h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Our Platform!</h1>
        </div>

        <div class="content">
            <h2>Hello {{ $client->name }}!</h2>

            <p>We're thrilled to welcome you to our platform. Thank you for joining our community of valued clients.</p>

            <div class="client-info">
                <h3>Your Information</h3>
                <p><strong>Name:</strong> {{ $client->name }}</p>
                <p><strong>Email:</strong> {{ $client->email }}</p>
                @if($client->phone)
                    <p><strong>Phone:</strong> {{ $client->phone }}</p>
                @endif
                @if($client->company)
                    <p><strong>Company:</strong> {{ $client->company }}</p>
                @endif
                @if($client->address)
                    <p><strong>Address:</strong> {{ $client->address }}</p>
                @endif
            </div>

            <div class="highlight">
                <p><strong>What's Next?</strong></p>
                <p>Our team will be in touch with you shortly to discuss how we can best serve your needs. In the meantime, feel free to explore our services and don't hesitate to reach out if you have any questions.</p>
            </div>

            <p>We're committed to providing you with exceptional service and look forward to building a successful partnership with you.</p>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">Visit Our Website</a>
            </div>

            <p>Best regards,<br>
            <strong>{{ config('app.name') }} Team</strong></p>
        </div>

        <div class="footer">
            <p>This email was sent to {{ $client->email }} as part of our client onboarding process.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            @if(config('mail.from.address'))
                <p>If you have any questions, please contact us at {{ config('mail.from.address') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
