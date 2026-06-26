<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public int $templateId = 5;

    public string $name;
    public string $email;
    public string $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $usuario, string $token)
    {
        $this->name = $usuario->name;
        $this->email = $usuario->email;
        $this->link = env('FRONT_URL', 'http://localhost:4200') . "/alterar-senha?token={$token}";
    }

    public function params(): array
    {
        return [
            'name' => $this->name ?? '',
            'URI' => $this->link,
            'appEmail' => env('BREVO_SENDER_EMAIL', 'Error'),
            'appName' => env('APP_NAME', 'Error'),
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this;
    }
}
