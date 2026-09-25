<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Пользователь.
 *
 * `MustVerifyEmail` подключён, но верификация **мягкая**: письмо со ссылкой
 * подтверждения уходит при регистрации, а `verified`-middleware на синк/работу не
 * ставится. Пока адрес не подтверждён, приложение показывает баннер и предлагает
 * отправить письмо повторно, офлайн-работа не блокируется.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Письмо со ссылкой на сброс пароля.
     *
     * Клиент — приложение, поэтому ссылка ведёт на https-bridge бэкенда
     * (`config/app-links.php`), а не на страницу веб-SPA.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Письмо с ссылкой на подтверждение почты (подписанная, с истечением).
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    //отношение к специализации
    public function specializations()
    {
        return $this->hasMany(Specialization::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
