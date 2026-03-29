<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\ServiceRequest;

class NewPaymentRequestNotification extends Notification
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_payment_request',
            'title' => 'For Payment: ' . $this->serviceRequest->service_type,
            'message' => "An application from {$this->serviceRequest->applicant_name} is now ready for payment processing.",
            'service_request_id' => $this->serviceRequest->id,
            'icon' => 'fa-money-bill-wave',
            'color' => 'bg-green-500'
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/dashboard');

        return (new MailMessage)
                    ->subject('New Payment Request: ' . $this->serviceRequest->service_type)
                    ->greeting('Hello,')
                    ->line("An application for **" . $this->serviceRequest->service_type . "** from **" . $this->serviceRequest->applicant_name . "** has been approved by the priest.")
                    ->line("It is now strictly awaiting payment processing under the Treasurer dashboard.")
                    ->action('Process Payment', $url)
                    ->line('Thank you for using our parish system.');
    }
}
