<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Письмо с ссылкой на подтверждение почты при регистрации.
 *
 * Ссылка подписанная и с истечением, ведёт на web-роут `verification.verify`:
 * он помечает адрес подтверждённым (побочный эффект — поэтому именно сервер, а не
 * приложение) и уводит на https-адрес App Link (`/app/verified`), где приложение
 * открывается напрямую. Подтверждение почты **мягкое**: пока адрес не подтверждён,
 * вход, работа и синк не блокируются — приложение показывает баннер и предлагает
 * отправить письмо повторно.
 */
class VerifyEmailNotification extends Notification
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
        $expire = (int) config('app-links.verification_expire', 60);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes($expire),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        $message = (new MailMessage)
            ->subject('Ledger Craft: подтвердите почту')
            ->greeting('Добро пожаловать в Ledger Craft, '.$notifiable->name.'!')
            ->line('Осталось подтвердить почту — нажмите кнопку ниже, откроется приложение Ledger Craft.')
            ->line('Подтверждение нужно, чтобы восстановить пароль, если вы его забудете. Работа в приложении доступна и без него.')
            ->action('Подтвердить почту', $url)
            ->line('Ссылка действует '.$expire.' минут.')
            ->line('Если вы не регистрировались в Ledger Craft — просто проигнорируйте это письмо.')
            ->salutation('Ledger Craft');

        // Ответы пользователей уводим на живой ящик, а не на no-reply
        $replyTo = (string) config('mail.reply_to.address');
        if ($replyTo !== '') {
            $message->replyTo($replyTo, (string) config('mail.reply_to.name'));
        }

        return $message;
    }
}
