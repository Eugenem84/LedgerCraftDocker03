<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Письмо после смены пароля — страховка на случай, если смену инициировал
 * не владелец аккаунта.
 *
 * Клиент — приложение, веб-страницы пароля нет, поэтому кнопки-ссылки в письме
 * нет: пользователь просто открывает приложение обычным способом.
 */
class PasswordChangedNotification extends Notification
{
    use Queueable;

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Ledger Craft: пароль изменён')
            ->greeting('Пароль изменён')
            ->line('Пароль к вашему аккаунту в Ledger Craft успешно изменён.')
            ->line('Если это были вы — ничего делать не нужно, новый пароль уже действует в приложении.')
            ->line('Если вы не меняли пароль, срочно восстановите доступ через «Забыли пароль?» на экране входа в приложение.')
            ->salutation('Ledger Craft');

        // Ответы пользователей уводим на живой ящик, а не на no-reply
        $replyTo = (string) config('mail.reply_to.address');
        if ($replyTo !== '') {
            $message->replyTo($replyTo, (string) config('mail.reply_to.name'));
        }

        return $message;
    }
}
