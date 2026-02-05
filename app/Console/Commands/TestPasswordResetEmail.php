<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;

class TestPasswordResetEmail extends Command
{
    protected $signature = 'test:password-reset {email}';
    protected $description = 'Test password reset email functionality';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }
        
        $this->info("Attempting to send password reset email to: {$email}");
        
        $status = Password::sendResetLink(['email' => $email]);
        
        if ($status === Password::RESET_LINK_SENT) {
            $this->info("✅ Password reset email sent successfully!");
            $this->info("Check the email inbox or logs/laravel.log");
            return 0;
        }
        
        $this->error("❌ Failed to send password reset email: " . $status);
        return 1;
    }
}
