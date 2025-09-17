<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $template->name ?? 'Email' }}</title>
    <style>
        /* Reset styles */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }

        /* Main styles */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f4f4f7;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
        }

        table {
            border-collapse: collapse !important;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f4f4f7;
            padding: 40px 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
        }

        .email-body {
            padding: 40px 30px;
        }

        .email-body h1 {
            color: #333333;
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 20px 0;
        }

        .email-body h2 {
            color: #333333;
            font-size: 20px;
            font-weight: 600;
            margin: 20px 0 15px 0;
        }

        .email-body h3 {
            color: #333333;
            font-size: 18px;
            font-weight: 600;
            margin: 15px 0 10px 0;
        }

        .email-body p {
            color: #555555;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 15px 0;
        }

        .email-body ul, .email-body ol {
            margin: 0 0 15px 20px;
            padding: 0;
            color: #555555;
        }

        .email-body li {
            margin-bottom: 8px;
        }

        .email-body a {
            color: #667eea;
            text-decoration: none;
        }

        .email-body a:hover {
            text-decoration: underline;
        }

        .email-body .button {
            display: inline-block;
            padding: 12px 30px;
            margin: 20px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 16px;
        }

        .email-body .button:hover {
            opacity: 0.9;
            text-decoration: none;
        }

        .email-body strong {
            color: #333333;
            font-weight: 600;
        }

        .email-body code {
            background-color: #f4f4f7;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }

        .email-body blockquote {
            border-left: 4px solid #667eea;
            padding-left: 15px;
            margin: 20px 0;
            color: #666666;
        }

        .email-body table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .email-body th {
            background-color: #f4f4f7;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #e0e0e0;
        }

        .email-body td {
            padding: 10px;
            border: 1px solid #e0e0e0;
        }

        .email-footer {
            background-color: #f4f4f7;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }

        .email-footer p {
            color: #999999;
            font-size: 14px;
            margin: 0 0 10px 0;
        }

        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }

        .email-footer a:hover {
            text-decoration: underline;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
        }

        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 30px 0;
        }

        /* Responsive styles */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }

            .email-body {
                padding: 30px 20px !important;
            }

            .email-header {
                padding: 20px !important;
            }

            .email-header h1 {
                font-size: 24px !important;
            }

            .email-body h1 {
                font-size: 20px !important;
            }

            .email-body h2 {
                font-size: 18px !important;
            }

            .email-body p, .email-body li {
                font-size: 14px !important;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1a1a1a !important;
            }

            .email-wrapper {
                background-color: #1a1a1a !important;
            }

            .email-container {
                background-color: #2a2a2a !important;
            }

            .email-body h1, .email-body h2, .email-body h3, .email-body strong {
                color: #f0f0f0 !important;
            }

            .email-body p, .email-body li {
                color: #d0d0d0 !important;
            }

            .email-footer {
                background-color: #1a1a1a !important;
                border-top-color: #3a3a3a !important;
            }

            .email-body th {
                background-color: #3a3a3a !important;
                border-color: #4a4a4a !important;
            }

            .email-body td {
                border-color: #4a4a4a !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Email Header -->
            <div class="email-header">
                <h1>{{ config('app.name') }}</h1>
            </div>

            <!-- Email Body -->
            <div class="email-body">
                {!! $content !!}
            </div>

            <!-- Email Footer -->
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p>
                    @if(config('app.url'))
                        <a href="{{ config('app.url') }}">Visit our website</a>
                    @endif
                    @if(config('mail.from.address'))
                        @if(config('app.url')) | @endif
                        <a href="mailto:{{ config('mail.from.address') }}">Contact Support</a>
                    @endif
                </p>

                <div class="divider"></div>

                <p style="font-size: 12px; color: #999999;">
                    This email was sent to {{ $client->email ?? 'you' }} because you are a registered member of {{ config('app.name') }}.
                    <br>
                    If you believe this email was sent in error, please contact our support team.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
