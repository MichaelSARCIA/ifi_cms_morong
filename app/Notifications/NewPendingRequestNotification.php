<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\ServiceRequest;

class NewPendingRequestNotification extends Notification
{
    use Queueable;

    protected $serviceRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Pending Request: ' . $this->serviceRequest->service_type)
                    ->greeting('Hello,')
                    ->line("A new service request for **" . $this->serviceRequest->service_type . "** from **" . $this->serviceRequest->applicant_name . "** has been submitted.")
                    ->line("Please check your dashboard to review the schedule and requirements.")
                    ->action('View Dashboard', url('/dashboard'))
                    ->line('Thank you for using our parish system.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_pending_request',
            'title' => 'New Pending Request submitted',
            'message' => "A new pending request for {$this->serviceRequest->service_type} has been submitted.",
            'service_request_id' => $this->serviceRequest->id,
            'icon' => 'fa-clipboard-list',
            'color' => 'bg-yellow-500'
        ];
    }
}
