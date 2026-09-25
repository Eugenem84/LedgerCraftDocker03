<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Письмо со ссылкой для сброса пароля.
 *
 * Клиент — Android-приложение, поэтому ссылка ведёт на https-адрес бэкенда
 * (`/app/reset`), а не на страницу SPA. Оттуда Android открывает приложение
 * (App Links), либо bridge-страница уводит по схеме `ledgercraft://reset-password`.
 * Так ссылка кликабельна из любого мейл-клиента, а токен сброса остаётся запросом
 * страницы и не проверяется до отправки нового пароля.
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $token  Токен сброса пароля из брокера Laravel
     */
    public function __construct(public string $token)
    {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $query = http_build_query([
            'token' => $this->token,
            'email' => (string) $notifiable->email,
        ]);

        $url = url((string) config('app-links.reset_path')).'?'.$query;

        $message = (new MailMessage)
            ->subject('Ledger Craft: сброс пароля')
            ->greeting('Сброс пароля')
            ->line('Вы запросили сброс пароля. Чтобы задать новый, нажмите кнопку ниже — откроется приложение Ledger Craft.')
            ->action('Задать новый пароль', $url)
            ->line('Ссылка действует 60 минут.')
            ->line('Если вы не запрашивали сброс пароля — просто проигнорируйте это письмо, пароль останется прежним.')
            ->salutation('Ledger Craft');

        // Ответы пользователей уводим на живой ящик, а не на no-reply
        $replyTo = (string) config('mail.reply_to.address');
        if ($replyTo !== '') {
            $message->replyTo($replyTo, (string) config('mail.reply_to.name'));
        }

        return $message;
    }
}
