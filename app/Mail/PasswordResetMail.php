<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $resetLink,
        public int $expiresIn = 30
    ) {}

    public function build()
    {
        return $this->subject('إعادة تعيين كلمة المرور')
            ->html("
                <h2>إعادة تعيين كلمة المرور</h2>
                <p>اضغط على الرابط لتغيير كلمة المرور (صالح لمدة {$this->expiresIn} دقيقة):</p>
                <p><a href='{$this->resetLink}'>{$this->resetLink}</a></p>
            ");
    }
}

