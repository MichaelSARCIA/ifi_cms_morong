<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RemarkAddedNotification extends Notification
{
    use Queueable;

    protected $serviceRequest;
    protected $authorName;
    protected $remark;

    /**
     * Create a new notification instance.
     */
    public function __construct($serviceRequest, $authorName, $remark)
    {
        $this->serviceRequest = $serviceRequest;
        $this->authorName = $authorName;
        $this->remark = $remark;
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
                    ->subject('New Remark: ' . $this->serviceRequest->service_type)
                    ->greeting('Hello,')
                    ->line("A new remark has been added to the application for **" . $this->serviceRequest->service_type . "** from **" . $this->serviceRequest->applicant_name . "**.")
                    ->line("**" . $this->authorName . "** wrote: \"" . $this->remark . "\"")
                    ->action('View Remarks', url('/dashboard'))
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
            'type' => 'new_remark',
            'title' => 'New Remark on Application',
            'message' => "{$this->authorName} left a new remark: \"{$this->remark}\"",
            'service_request_id' => $this->serviceRequest->id,
            'icon' => 'fa-comment-dots',
            'color' => 'bg-yellow-500'
        ];
    }
}
