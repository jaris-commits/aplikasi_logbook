<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\LogbookWorkflowNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorkflowNotifier
{
    public function notify(User $user, string $title, string $message, string $url): void
    {
        $user->notify(new LogbookWorkflowNotification($title, $message, $url));

        $token = config('services.telegram.bot_token');
        if (! $token || ! $user->telegram_chat_id || ! $user->telegram_consent_at) {
            return;
        }

        try {
            $response = Http::asForm()->connectTimeout(5)->timeout(10)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $user->telegram_chat_id,
                    'text' => $title.': Ada pembaruan pada aplikasi Logbook Magang. Silakan masuk untuk melihat detail.',
                    'disable_web_page_preview' => true,
                ]);

            if (! $response->successful()) {
                Log::warning('Telegram notification delivery failed.', ['status' => $response->status()]);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}