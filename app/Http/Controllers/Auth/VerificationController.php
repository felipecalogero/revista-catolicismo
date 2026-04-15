<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Exibe o aviso de verificação de e-mail.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $email = (string) $user->email;

        return view('auth.verify-email', [
            'maskedEmail' => $this->maskEmail($email),
            'currentEmail' => $email,
        ]);
    }

    /**
     * Processa o link de verificação de e-mail.
     */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()->intended(route('dashboard').'?verified=1');
    }

    /**
     * Reenvia o e-mail de verificação.
     */
    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    public function updateEmail(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $newEmail = strtolower(trim((string) $validated['email']));
        $currentEmail = strtolower(trim((string) $user->email));

        if ($newEmail !== $currentEmail) {
            $user->forceFill([
                'email' => $newEmail,
                'email_verified_at' => null,
            ])->save();
        }

        $user->sendEmailVerificationNotification();

        return back()
            ->with('status', 'verification-link-sent')
            ->with('email-updated', true);
    }

    protected function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $localLength = strlen($local);

        if ($localLength <= 2) {
            $maskedLocal = substr($local, 0, 1).'*';
        } else {
            $maskedLocal = substr($local, 0, 2).str_repeat('*', max(1, $localLength - 2));
        }

        return $maskedLocal.'@'.$domain;
    }
}
