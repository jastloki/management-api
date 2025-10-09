<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Client;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class HtmlMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The client instance.
     */
    public Client $client;

    /**
     * The email template instance.
     */
    public EmailTemplate $template;

    /**
     * The parsed email content.
     */
    protected array $parsedContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Client $client, EmailTemplate $template)
    {
        $this->client = $client;
        $this->template = $template;
        $this->parseTemplate();
    }

    /**
     * Parse the email template with client data.
     */
    protected function parseTemplate(): void
    {
        // Prepare data for template parsing
        $data = [
            "client_name" => $this->client->name,
            "client_email" => $this->client->email,
            "client_phone" => $this->client->phone ?? "",
            "client_address" => $this->client->address ?? "",
            "client_status" => $this->client->status->name ?? "N/A",
            "app_name" => config("app.name"),
            "current_date" => now()->format("F j, Y"),
            "current_time" => now()->format("g:i A"),
            "current_year" => now()->year,
            "company_name" => env("COMPANY_NAME", ""),
            "company_website" => config("app.url"),
            "support_email" => env("MAIL_FROM_ADDRESS", ""),
            "support_phone" => env("SUPPORT_PHONE", ""),

            // Add any additional client-specific data
            "registration_date" => $this->client->created_at->format("F j, Y"),
            "last_updated" => $this->client->updated_at->format("F j, Y"),
        ];

        // Parse the template with the data
        $this->parsedContent = $this->template->parse($data);

        if (isset($this->parsedContent["content"])) {
            // Decode HTML entities like &nbsp; into regular characters (like a space).
            $decodedContent = html_entity_decode(
                $this->parsedContent["content"],
                ENT_QUOTES,
                "UTF-8",
            );

            // Replace the original parsed content with the cleaned version.
            $this->parsedContent["content"] = $decodedContent;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->parsedContent["subject"] ??
                "Email from " . config("app.name"),
            from: new Address(
                config("mail.from.address"),
                config("mail.from.name"),
            ),

            replyTo: [
                new Address(
                    config(
                        "mail.reply_to.address",
                        config("mail.from.address"),
                    ),
                    config("mail.reply_to.name", config("mail.from.name")),
                ),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            // view: "emails.template",
            // with: [
            //     "content" => $this->parsedContent["content"] ?? "",
            //     "client" => $this->client,
            //     "template" => $this->template,
            // ],
            htmlString: $this->parsedContent["content"],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * The job failed to process.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure
        \Log::error("Failed to send email to client", [
            "client_id" => $this->client->id,
            "client_email" => $this->client->email,
            "template_id" => $this->template->id,
            "template_name" => $this->template->name,
            "error" => $exception->getMessage(),
        ]);
    }
}
