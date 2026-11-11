<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramLogbookNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $message,
        private readonly ?string $chatId
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pemberitahuan logbook',
            'message' => $this->message,
            'url' => route('dashboard'),
        ];
    }

    public function sendToTelegram(): void
    {
        $token = config('services.telegram.bot_token');

        if (! $token || ! $this->chatId) {
            return;
        }

        $response = Http::asForm()->connectTimeout(5)->timeout(10)
            ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $this->message,
                'disable_web_page_preview' => true,
            ]);

        if (! $response->successful()) {
            Log::warning('Telegram notification delivery failed.', ['status' => $response->status()]);
        }
    }
}