<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class EmailVerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request, $id, $hash)
    {
        try {
            $user = User::findOrFail($id);

            // Cek apakah signature valid (URL sudah kadaluarsa?)
            if (! $request->hasValidSignature()) {
                return view('email-verified')->with('error', 'Link verifikasi sudah kadaluarsa. Silakan minta link baru.');
            }

            // Cek apakah hash valid
            if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
                return view('email-verified')->with('error', 'Link verifikasi tidak valid.');
            }

            // Jika sudah terverifikasi
            if ($user->hasVerifiedEmail()) {
                return view('email-verified')->with('success', 'Email Anda sudah terverifikasi sebelumnya.');
            }

            // Mark email sebagai verified
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return view('email-verified')->with('success', 'Email Anda berhasil diverifikasi! Silakan login untuk melanjutkan.');
        } catch (\Exception $e) {
            Log::error('Email verification error: ' . $e->getMessage());
            return view('email-verified')->with('error', 'Terjadi kesalahan saat verifikasi email. User tidak ditemukan.');
        }
    }
}
