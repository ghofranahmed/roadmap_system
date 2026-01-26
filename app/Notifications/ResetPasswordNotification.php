<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class ResetPasswordNotification extends Notification
{
    use Queueable;

   

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(config('app.url').route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(Lang::get('إعادة تعيين كلمة المرور'))
            ->line(Lang::get('لقد تلقيت هذا البريد الإلكتروني لأننا تلقينا طلبًا لإعادة تعيين كلمة المرور لحسابك.'))
            ->action(Lang::get('إعادة تعيين كلمة المرور'), $url)
            ->line(Lang::get('ستنتهي صلاحية رابط إعادة تعيين كلمة المرور خلال :count دقيقة.', [
                'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')
            ]))
            ->line(Lang::get('إذا لم تطلب إعادة تعيين كلمة المرور، فلا داعي لاتخاذ أي إجراء آخر.'));
    }
}
   
