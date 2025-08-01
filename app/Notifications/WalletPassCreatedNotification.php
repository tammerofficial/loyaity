<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Customer;

class WalletPassCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $customer;
    protected $passUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(Customer $customer, $passUrl = null)
    {
        $this->customer = $customer;
        $this->passUrl = $passUrl;
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
            ->subject('🎉 تم إنشاء بطاقة الولاء الجديدة!')
            ->greeting('مرحباً ' . $this->customer->name)
            ->line('تم إنشاء بطاقة الولاء الجديدة بنجاح!')
            ->line('رقم العضوية: ' . $this->customer->membership_number)
            ->line('المستوى الحالي: ' . $this->customer->tier)
            ->line('النقاط المتاحة: ' . number_format($this->customer->available_points));

        if ($this->passUrl) {
            $message->action('📱 إضافة البطاقة إلى Apple Wallet', $this->passUrl);
        }

        $message->line('شكراً لك على انضمامك إلى برنامج الولاء!')
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
            'type' => 'wallet_pass_created',
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'membership_number' => $this->customer->membership_number,
            'tier' => $this->customer->tier,
            'available_points' => $this->customer->available_points,
            'pass_url' => $this->passUrl,
            'message' => 'تم إنشاء بطاقة الولاء الجديدة بنجاح!',
            'created_at' => now(),
        ];
    }
}
