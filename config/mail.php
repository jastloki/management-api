<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    "default" => env("MAIL_MAILER", "log"),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    "mailers" => [
        "smtp" => [
            "transport" => "smtp",
            "scheme" => env("MAIL_SCHEME"),
            "url" => env("MAIL_URL"),
            "host" => env("MAIL_HOST", "127.0.0.1"),
            "port" => env("MAIL_PORT", 2525),
            "username" => env("MAIL_USERNAME"),
            "password" => env("MAIL_PASSWORD"),
            "timeout" => null,
            "local_domain" => env(
                "MAIL_EHLO_DOMAIN",
                parse_url(
                    (string) env("APP_URL", "http://localhost"),
                    PHP_URL_HOST,
                ),
            ),
        ],

        "ses" => [
            "transport" => "ses",
        ],

        "postmark" => [
            "transport" => "postmark",
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        "resend" => [
            "transport" => "resend",
        ],

        "sendmail" => [
            "transport" => "sendmail",
            "path" => env("MAIL_SENDMAIL_PATH", "/usr/sbin/sendmail -bs -i"),
        ],

        "log" => [
            "transport" => "log",
            "channel" => env("MAIL_LOG_CHANNEL"),
        ],

        "array" => [
            "transport" => "array",
        ],

        "failover" => [
            "transport" => "failover",
            "mailers" => ["smtp", "log"],
            "retry_after" => 60,
        ],

        "roundrobin" => [
            "transport" => "roundrobin",
            "mailers" => ["ses", "postmark"],
            "retry_after" => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    "from" => [
        "address" => env("MAIL_FROM_ADDRESS", "hello@example.com"),
        "name" => env("MAIL_FROM_NAME", "Example"),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mail Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure different email service providers that can be
    | used for sending emails. Each provider has its own configuration
    | settings and API requirements. You can switch between providers
    | on a per-email basis for better deliverability and redundancy.
    |
    */

    "providers" => [
        "smtp" => [
            "host" => env("MAIL_HOST", "127.0.0.1"),
            "port" => env("MAIL_PORT", 587),
            "username" => env("MAIL_USERNAME"),
            "password" => env("MAIL_PASSWORD"),
            "encryption" => env("MAIL_ENCRYPTION", "tls"),
            "timeout" => 60,
            "local_domain" => env("MAIL_EHLO_DOMAIN"),
            "from_email" => env("MAIL_FROM_ADDRESS"),
            "from_name" => env("MAIL_FROM_NAME"),
        ],

        "sendgrid" => [
            "api_key" => env("SENDGRID_API_KEY"),
            "from_email" => env("MAIL_FROM_ADDRESS"),
            "from_name" => env("MAIL_FROM_NAME"),
            "tracking_settings" => [
                "click_tracking" => [
                    "enable" => env("SENDGRID_CLICK_TRACKING", true),
                ],
                "open_tracking" => [
                    "enable" => env("SENDGRID_OPEN_TRACKING", true),
                ],
            ],
            "custom_headers" => [
                "X-Mailer" => config("app.name", "Laravel"),
            ],
        ],

        "mailgun" => [
            "api_key" => env("MAILGUN_SECRET"),
            "domain" => env("MAILGUN_DOMAIN"),
            "region" => env("MAILGUN_REGION", "us"),
            "from_email" => env("MAIL_FROM_ADDRESS"),
            "from_name" => env("MAIL_FROM_NAME"),
            "track_clicks" => env("MAILGUN_TRACK_CLICKS", true),
            "track_opens" => env("MAILGUN_TRACK_OPENS", true),
            "tags" => ["laravel-sender"],
            "custom_headers" => [
                "X-Mailer" => config("app.name", "Laravel"),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Mail Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default mail provider that is used when no
    | specific provider is selected for sending an email. This should be
    | one of the providers configured in the "providers" array above.
    |
    */

    "default_provider" => env("MAIL_DEFAULT_PROVIDER", "smtp"),

    /*
    |--------------------------------------------------------------------------
    | Provider Priority Order
    |--------------------------------------------------------------------------
    |
    | This array defines the priority order for selecting mail providers
    | when automatic failover is enabled. The system will try providers
    | in this order until one succeeds or all fail.
    |
    */

    "provider_priority" => ["sendgrid", "mailgun", "smtp"],

    /*
    |--------------------------------------------------------------------------
    | SMTP Email Verification
    |--------------------------------------------------------------------------
    |
    | These options control SMTP-based email verification used during email
    | validation. SMTP verification connects to the recipient's mail server
    | to check if an email address actually exists before sending emails.
    |
    */

    "smtp_verification_enabled" => env("MAIL_SMTP_VERIFICATION_ENABLED", true),
    "smtp_timeout" => env("MAIL_SMTP_TIMEOUT", 10),
    "smtp_max_mx_hosts" => env("MAIL_SMTP_MAX_MX_HOSTS", 3),
];
