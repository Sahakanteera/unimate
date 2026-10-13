<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActivityNotification extends Notification
{
    use Queueable;

    public string $message;

    /** ลิงก์ที่จะพาไปเมื่อกดการแจ้งเตือน (null = ไม่มีลิงก์) */
    public ?string $url;

    /** ใช้เลือกไอคอน: info, success, danger, request, review, warning */
    public string $kind;

    public function __construct(string $message, ?string $url = null, string $kind = 'info')
    {
        $this->message = $message;
        $this->url = $url;
        $this->kind = $kind;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'kind' => $this->kind,
        ];
    }
}
