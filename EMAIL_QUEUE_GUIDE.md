# Email Queue Management System

This Laravel application includes a comprehensive email queue management system that allows you to monitor, queue, and track email sending status for all clients.

## Features

- **Email Status Tracking**: Track email status (pending, queued, sending, sent, failed)
- **Bulk Email Operations**: Queue emails for multiple clients at once
- **Email Analytics**: View detailed statistics and reports
- **Auto-retry Logic**: Failed emails are automatically retried with exponential backoff
- **Real-time Monitoring**: Monitor email queue status in real-time
- **Filtering & Search**: Filter clients by email status, email validity, and search by name/email/company

## Email Status States

- **pending**: Email has not been queued yet (default for new clients)
- **queued**: Email has been added to the queue and is waiting to be processed
- **sending**: Email is currently being sent
- **sent**: Email was successfully sent
- **failed**: Email failed to send after all retry attempts

## Getting Started

### 1. Database Setup

Make sure you have run the migrations to create the necessary database tables:

```bash
php artisan migrate
```

This will add the `email_status` and `email_sent_at` columns to the clients table and create the jobs table for the queue system.

### 2. Queue Configuration

The system uses Laravel's queue system. Make sure your queue is configured properly in `.env`:

```env
QUEUE_CONNECTION=database
```

### 3. Start the Queue Worker

To process queued emails, you need to run the queue worker:

```bash
php artisan queue:work --timeout=60
```

For production, consider using a process manager like Supervisor to keep the queue worker running.

### 4. Mail Configuration

Configure your mail settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Using the Email Queue System

### Accessing the Email Queue

Navigate to `/admin/emails` in your admin panel to access the email queue management interface.

### Queue Management Features

#### Statistics Dashboard
- View real-time statistics of email statuses
- Monitor total clients, pending, queued, sending, sent, and failed emails
- Track valid vs invalid email addresses
- View queue-eligible clients (valid emails with pending/failed status)
- Visual indicators with color-coded badges
- Interactive statistics cards for quick filtering

#### Filtering and Search
- Filter clients by email status (pending, queued, sending, sent, failed)
- Filter clients by email validity (valid emails, invalid emails)
- Filter for queue-eligible clients (valid emails with pending/failed status)
- Search clients by name, email, or company
- Clear filters to view all clients
- Click on statistics cards for quick filtering

#### Individual Actions
- **Queue Email**: Queue an email for a single client (available for pending/failed clients with valid emails)
- **Reset Status**: Reset email status back to pending (available for sent/failed clients)
- **View Client**: View detailed client information
- Note: Emails can only be queued for clients with valid email addresses

#### Bulk Actions
- **Queue Selected**: Queue emails for selected clients (only those with valid emails)
- **Queue All Pending**: Queue emails for all clients with pending status and valid emails
- **Queue All Failed**: Queue emails for all clients with failed status and valid emails
- **Queue All Eligible**: Queue emails for all clients with pending or failed status and valid emails
- **Reset Selected**: Reset email status for selected clients
- Note: All queue operations automatically filter for clients with valid email addresses

### Email Analytics

Access analytics at `/admin/emails/analytics` to view:

- **Daily Email Statistics**: Chart showing emails sent over the last 30 days
- **Status Distribution**: Pie chart showing distribution of email statuses
- **Summary Statistics**: Total sent, average per day, peak day, success rate
- **Daily Breakdown**: Detailed table of daily email sending activity

## Command Line Interface

### Test Email Queue

Test the email queue system with sample data:

```bash
# Queue emails for 5 pending clients
php artisan email:test-queue --count=5
```

### Queue Worker Commands

```bash
# Start queue worker
php artisan queue:work

# Start queue worker with timeout
php artisan queue:work --timeout=60

# Process only email queue
php artisan queue:work --queue=default

# Monitor queue status
php artisan queue:monitor

# Restart all queue workers
php artisan queue:restart
```

## Email Templates

The system uses a responsive email template located at `resources/views/emails/client-welcome.blade.php`. The template includes:

- Professional design with company branding
- Client information display
- Responsive layout for mobile devices
- Call-to-action buttons
- Social media links
- Unsubscribe options

### Customizing Email Content

To customize the email content, edit the `ClientWelcome` mailable class:

```php
// app/Mail/ClientWelcome.php
public function content(): Content
{
    return new Content(
        view: 'emails.client-welcome',
        with: [
            'client' => $this->client,
            // Add more data here
        ],
    );
}
```

## API Integration

### Queue Single Email

```php
use App\Jobs\SendClientEmail;
use App\Models\Client;

$client = Client::find(1);
$client->update(['email_status' => 'queued']);
SendClientEmail::dispatch($client);
```

### Queue Multiple Emails

```php
$clients = Client::where('email_status', 'pending')->get();

foreach ($clients as $client) {
    $client->update(['email_status' => 'queued']);
    SendClientEmail::dispatch($client);
}
```

## Error Handling

### Failed Jobs

Failed jobs are automatically stored in the `failed_jobs` table. You can:

```bash
# View failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry {id}

# Retry all failed jobs
php artisan queue:retry all

# Delete failed job
php artisan queue:forget {id}

# Clear all failed jobs
php artisan queue:flush
```

### Logging

The system logs all email activities:

- Successful sends: `INFO` level
- Retry attempts: `WARNING` level
- Permanent failures: `ERROR` level

Logs include client information and error details for debugging.

## Production Deployment

### Supervisor Configuration

Create a supervisor configuration file for the queue worker:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

### Monitoring

Consider implementing monitoring for:

- Queue size and processing rate
- Failed job counts
- Email delivery rates
- System performance metrics

## Security Considerations

- Ensure proper authentication for admin routes
- Validate email addresses before queuing
- Implement rate limiting for bulk operations
- Monitor for spam/abuse patterns
- Keep email templates secure from XSS

## Troubleshooting

### Common Issues

1. **Emails not sending**: Check queue worker is running and mail configuration
2. **Jobs stuck in queue**: Restart queue worker with `php artisan queue:restart`
3. **High failure rate**: Check mail server settings and network connectivity
4. **Memory issues**: Adjust PHP memory limits and queue worker timeout

### Debug Mode

Enable debug logging by setting `APP_DEBUG=true` in `.env` for detailed error information.

## Support

For additional support or customization:

1. Check Laravel queue documentation
2. Review application logs in `storage/logs/`
3. Monitor database for queue job status
4. Use `php artisan tinker` for testing individual components