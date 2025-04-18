<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;

class EmailVerificationController extends Controller
{
    public function show()
    {
        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();
        return redirect()->route('attendance.show')->with('success', 'メール認証が完了しました');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        $user->sendEmailVerificationNotification();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        return back()->with([
            'message' => '認証メールを再送しました',
            'verification_link' => $verificationUrl,
        ]);
    }
}
