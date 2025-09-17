<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{app_name}}!',
                'content' => '<h2>Welcome {{client_name}}!</h2>
<p>Thank you for joining <strong>{{app_name}}</strong>. We\'re excited to have you on board.</p>
<p>Your account has been successfully created with the following details:</p>
<ul>
    <li><strong>Name:</strong> {{client_name}}</li>
    <li><strong>Email:</strong> {{client_email}}</li>
    <li><strong>Registration Date:</strong> {{current_date}}</li>
</ul>
<p>If you have any questions, feel free to reach out to our support team at {{support_email}}.</p>
<p>Best regards,<br>The {{app_name}} Team</p>',
                'variables' => ['client_name', 'client_email', 'app_name', 'current_date', 'support_email'],
                'description' => 'Sent to new clients upon registration',
                'is_active' => true,
            ],
            [
                'name' => 'Status Update Notification',
                'subject' => 'Your Status Has Been Updated - {{app_name}}',
                'content' => '<h2>Status Update Notification</h2>
<p>Dear {{client_name}},</p>
<p>We wanted to inform you that your status has been updated in our system.</p>
<div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <p><strong>New Status:</strong> {{new_status}}</p>
    <p><strong>Previous Status:</strong> {{old_status}}</p>
    <p><strong>Updated on:</strong> {{current_date}} at {{current_time}}</p>
</div>
<p>If you have any questions about this change, please don\'t hesitate to contact us at {{support_email}}.</p>
<p>Thank you for your continued trust in {{app_name}}.</p>
<p>Best regards,<br>The {{app_name}} Team</p>',
                'variables' => ['client_name', 'new_status', 'old_status', 'current_date', 'current_time', 'app_name', 'support_email'],
                'description' => 'Sent when a client\'s status is updated',
                'is_active' => true,
            ],
            [
                'name' => 'Password Reset Request',
                'subject' => 'Password Reset Request - {{app_name}}',
                'content' => '<h2>Password Reset Request</h2>
<p>Hello {{user_name}},</p>
<p>We received a request to reset the password for your account associated with {{user_email}}.</p>
<p>To reset your password, please click the button below:</p>
<div style="text-align: center; margin: 30px 0;">
    <a href="{{reset_link}}" style="background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a>
</div>
<p>Or copy and paste this link into your browser:</p>
<p style="word-break: break-all; color: #007bff;">{{reset_link}}</p>
<p><strong>Important:</strong> This link will expire in {{expiry_time}} hours for security reasons.</p>
<p>If you didn\'t request a password reset, please ignore this email or contact our support team at {{support_email}} if you have concerns.</p>
<p>Best regards,<br>The {{app_name}} Security Team</p>',
                'variables' => ['user_name', 'user_email', 'reset_link', 'expiry_time', 'app_name', 'support_email'],
                'description' => 'Sent when a password reset is requested',
                'is_active' => true,
            ],
            [
                'name' => 'Account Activation',
                'subject' => 'Activate Your {{app_name}} Account',
                'content' => '<h2>Account Activation Required</h2>
<p>Hi {{client_name}},</p>
<p>Thank you for registering with {{app_name}}! To complete your registration and activate your account, please click the button below:</p>
<div style="text-align: center; margin: 30px 0;">
    <a href="{{activation_link}}" style="background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Activate Account</a>
</div>
<p>Or copy and paste this link into your browser:</p>
<p style="word-break: break-all; color: #007bff;">{{activation_link}}</p>
<p>This activation link will expire in {{expiry_time}} hours.</p>
<p>If you have any issues activating your account, please contact us at {{support_email}}.</p>
<p>Welcome aboard!<br>The {{app_name}} Team</p>',
                'variables' => ['client_name', 'app_name', 'activation_link', 'expiry_time', 'support_email'],
                'description' => 'Sent to new users for account activation',
                'is_active' => true,
            ],
            [
                'name' => 'Monthly Newsletter',
                'subject' => '{{app_name}} Monthly Update - {{month_name}}',
                'content' => '<h2>{{app_name}} Monthly Newsletter</h2>
<p>Dear {{client_name}},</p>
<p>Welcome to our {{month_name}} newsletter! We\'re excited to share the latest updates and news with you.</p>
<h3>This Month\'s Highlights:</h3>
<ul>
    <li>New features and improvements</li>
    <li>Upcoming events and webinars</li>
    <li>Success stories from our community</li>
    <li>Tips and best practices</li>
</ul>
<p>We value your continued support and partnership with {{app_name}}.</p>
<div style="background-color: #f0f8ff; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;">
    <p><strong>Did you know?</strong> You can update your email preferences at any time by visiting your account settings.</p>
</div>
<p>Stay connected with us:</p>
<ul>
    <li>Website: {{company_website}}</li>
    <li>Support: {{support_email}}</li>
</ul>
<p>Thank you for being part of the {{app_name}} community!</p>
<p>Best regards,<br>The {{app_name}} Team</p>',
                'variables' => ['client_name', 'app_name', 'month_name', 'company_website', 'support_email'],
                'description' => 'Monthly newsletter template',
                'is_active' => false,
            ],
            [
                'name' => 'Invoice Notification',
                'subject' => 'Invoice #{{invoice_number}} from {{app_name}}',
                'content' => '<h2>Invoice Notification</h2>
<p>Dear {{client_name}},</p>
<p>We\'re writing to inform you that a new invoice has been generated for your account.</p>
<div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3 style="margin-top: 0;">Invoice Details:</h3>
    <table style="width: 100%;">
        <tr>
            <td><strong>Invoice Number:</strong></td>
            <td>#{{invoice_number}}</td>
        </tr>
        <tr>
            <td><strong>Invoice Date:</strong></td>
            <td>{{invoice_date}}</td>
        </tr>
        <tr>
            <td><strong>Due Date:</strong></td>
            <td>{{due_date}}</td>
        </tr>
        <tr>
            <td><strong>Total Amount:</strong></td>
            <td>{{total_amount}}</td>
        </tr>
    </table>
</div>
<p>You can view and download your invoice by logging into your account.</p>
<p>If you have any questions about this invoice, please contact our billing department at {{support_email}}.</p>
<p>Thank you for your business!</p>
<p>Best regards,<br>The {{app_name}} Billing Team</p>',
                'variables' => ['client_name', 'app_name', 'invoice_number', 'invoice_date', 'due_date', 'total_amount', 'support_email'],
                'description' => 'Sent when a new invoice is generated',
                'is_active' => false,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}
