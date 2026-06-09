<?php

namespace App\Listeners;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailAfterVerification implements ShouldQueue
{
    /**
     * Envia o e-mail de boas-vindas somente após o usuário confirmar a posse do e-mail,
     * eliminando o vetor de abuso de cadastros com e-mails de terceiros.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user));
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar e-mail de boas-vindas após verificação: '.$e->getMessage(), [
                'user_id' => $user->id,
            ]);
        }
    }
}
