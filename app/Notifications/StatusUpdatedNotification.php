<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusUpdatedNotification extends Notification
{
    use Queueable;

    protected $serviceRequest;
    protected $oldStatus;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct($serviceRequest, $oldStatus, $newStatus)
    {
        $this->serviceRequest = $serviceRequest;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
                    ->subject('Application Status Update: ' . $this->serviceRequest->service_type)
                    ->greeting('Hello,')
                    ->line("The application for **" . $this->serviceRequest->service_type . "** from **" . $this->serviceRequest->applicant_name . "** has been updated.")
                    ->line("Status changed from **" . $this->oldStatus . "** to **" . $this->newStatus . "**.")
                    ->action('View Request', url('/dashboard'))
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
            'type' => 'status_update',
            'title' => 'Application Status Updated',
            'message' => "Your request for {$this->serviceRequest->service_type} has been updated from '{$this->oldStatus}' to '{$this->newStatus}'.",
            'service_request_id' => $this->serviceRequest->id,
            'icon' => 'fa-tasks',
            'color' => 'bg-blue-500'
        ];
    }
}
