<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Приветственное письмо после подтверждения почты.
 *
 * Подтверждение адреса в проекте мягкое, поэтому письмо — просто «аккаунт
 * настроен»: напоминаем, что приложение работает офлайн и синхронизируется само.
 * Кнопки-ссылки нет: клиент — приложение, а не веб-страница.
 */
class WelcomeNotification extends Notification
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
            ->subject('Ledger Craft: почта подтверждена')
            ->greeting('Почта подтверждена, '.$notifiable->name.'!')
            ->line('Спасибо за регистрацию в Ledger Craft — приложении для учёта работ и запчастей.')
            ->line('Теперь доступно восстановление пароля, если вы его забудете.')
            ->line('Приложение работает офлайн и синхронизируется само, когда есть интернет.')
            ->salutation('Ledger Craft');

        // Ответы пользователей уводим на живой ящик, а не на no-reply
        $replyTo = (string) config('mail.reply_to.address');
        if ($replyTo !== '') {
            $message->replyTo($replyTo, (string) config('mail.reply_to.name'));
        }

        return $message;
    }
}
