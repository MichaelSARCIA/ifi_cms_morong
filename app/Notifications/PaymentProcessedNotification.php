<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\ServiceRequest;

class PaymentProcessedNotification extends Notification
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
                    ->subject('Payment Processed: ' . $this->serviceRequest->service_type)
                    ->greeting('Hello,')
                    ->line("Payment has been successfully processed for **" . $this->serviceRequest->applicant_name . "'s** application for **" . $this->serviceRequest->service_type . "**.")
                    ->line("The application status has been updated to **Approved**.")
                    ->action('View Details', url('/dashboard'))
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
            'type' => 'payment_processed',
            'title' => 'Payment Processed',
            'message' => "Payment has been processed for {$this->serviceRequest->applicant_name}'s {$this->serviceRequest->service_type} request.",
            'service_request_id' => $this->serviceRequest->id,
            'icon' => 'fa-check-circle',
            'color' => 'bg-green-600'
        ];
    }
}
