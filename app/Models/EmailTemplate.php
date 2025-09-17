<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "subject",
        "content",
        "variables",
        "description",
        "is_active",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "is_active" => "boolean",
        "variables" => "array",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    /**
     * Get the default email templates.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getDefaultTemplates(): array
    {
        return [
            "welcome_email" => [
                "name" => "Welcome Email",
                "subject" => "Welcome to {{app_name}}",
                "content" => '<h2>Welcome {{client_name}}!</h2>
<p>Thank you for joining {{app_name}}. We\'re excited to have you on board.</p>
<p>Your account has been successfully created with the following details:</p>
<ul>
    <li><strong>Name:</strong> {{client_name}}</li>
    <li><strong>Email:</strong> {{client_email}}</li>
    <li><strong>Registration Date:</strong> {{registration_date}}</li>
</ul>
<p>If you have any questions, feel free to reach out to our support team.</p>
<p>Best regards,<br>{{app_name}} Team</p>',
                "variables" => [
                    "client_name",
                    "client_email",
                    "app_name",
                    "registration_date",
                ],
                "description" => "Sent to new clients upon registration",
                "is_active" => true,
            ],
            "status_update" => [
                "name" => "Status Update Notification",
                "subject" => "Your Status Has Been Updated",
                "content" => '<h2>Status Update Notification</h2>
<p>Dear {{client_name}},</p>
<p>Your status has been updated to: <strong>{{new_status}}</strong></p>
<p>Previous status: {{old_status}}</p>
<p>Updated on: {{update_date}}</p>
<p>If you have any questions about this change, please contact us.</p>
<p>Thank you,<br>{{app_name}} Team</p>',
                "variables" => [
                    "client_name",
                    "new_status",
                    "old_status",
                    "update_date",
                    "app_name",
                ],
                "description" => 'Sent when a client\'s status is updated',
                "is_active" => true,
            ],
            "password_reset" => [
                "name" => "Password Reset",
                "subject" => "Password Reset Request",
                "content" => '<h2>Password Reset Request</h2>
<p>Hello {{user_name}},</p>
<p>We received a request to reset your password. Click the link below to reset it:</p>
<p><a href="{{reset_link}}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
<p>Or copy and paste this link: {{reset_link}}</p>
<p>This link will expire in {{expiry_time}} hours.</p>
<p>If you didn\'t request this, please ignore this email.</p>
<p>Best regards,<br>{{app_name}} Team</p>',
                "variables" => [
                    "user_name",
                    "reset_link",
                    "expiry_time",
                    "app_name",
                ],
                "description" => "Sent when a password reset is requested",
                "is_active" => true,
            ],
        ];
    }

    /**
     * Parse the template content with provided data.
     *
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function parse(array $data = []): array
    {
        $subject = $this->subject;
        $content = $this->content;

        // Add default app_name if not provided
        if (!isset($data["app_name"])) {
            $data["app_name"] = config("app.name");
        }

        // Replace variables in subject and content
        foreach ($data as $key => $value) {
            $placeholder = "{{" . $key . "}}";

            $subject = str_replace($placeholder, $value, $subject);
            $content = str_replace($placeholder, $value, $content);
        }

        return [
            "subject" => $subject,
            "content" => $content,
        ];
    }

    /**
     * Get available variables as formatted string.
     *
     * @return string
     */
    public function getVariablesFormatted(): string
    {
        if (empty($this->variables)) {
            return "No variables";
        }

        return implode(
            ", ",
            array_map(fn($var) => "{{" . $var . "}}", $this->variables),
        );
    }

    /**
     * Scope active templates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }

    /**
     * Extract variables from template content.
     *
     * @return array<int, string>
     */
    public function extractVariables(): array
    {
        $pattern = "/\{\{(\w+)\}\}/";
        $allContent = $this->subject . " " . $this->content;

        preg_match_all($pattern, $allContent, $matches);

        return array_unique($matches[1]);
    }

    /**
     * Update variables based on content.
     *
     * @return void
     */
    public function updateVariables(): void
    {
        $this->variables = $this->extractVariables();
        $this->save();
    }
}
