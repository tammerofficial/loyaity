<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Customer;

class WalletDesignUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $customer;
    protected $designChanges;

    /**
     * Create a new notification instance.
     */
    public function __construct(Customer $customer, array $designChanges = [])
    {
        $this->customer = $customer;
        $this->designChanges = $designChanges;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('🎨 تم تحديث تصميم بطاقة الولاء!')
            ->greeting('مرحباً ' . $this->customer->name)
            ->line('تم تحديث تصميم بطاقة الولاء الخاصة بك!')
            ->line('رقم العضوية: ' . $this->customer->membership_number);

        if (!empty($this->designChanges)) {
            $message->line('التحديثات الجديدة:');
            foreach ($this->designChanges as $change) {
                $message->line('• ' . $change);
            }
        }

        $message->line('ستظهر التحديثات تلقائياً في Apple Wallet الخاص بك.')
            ->line('شكراً لك على استخدام بطاقة الولاء!')
            ->salutation('مع تحيات فريق Tammer');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'wallet_design_updated',
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'membership_number' => $this->customer->membership_number,
            'design_changes' => $this->designChanges,
            'message' => 'تم تحديث تصميم بطاقة الولاء بنجاح!',
            'created_at' => now(),
        ];
    }
}
