<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordResetController extends Controller
{
    /**
     * Show the password reset form.
     */
    public function show(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        // Validasi apakah token dan email ada
        if (!$token || !$email) {
            return view('password-reset-form', [
                'error' => 'Link reset password tidak valid.',
                'token' => null,
                'email' => null
            ]);
        }

        return view('password-reset-form', [
            'token' => $token,
            'email' => $email,
            'error' => null
        ]);
    }

    /**
     * Handle the password reset.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/admin')->with('status', 'Password berhasil direset! Silakan login dengan password baru Anda.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
