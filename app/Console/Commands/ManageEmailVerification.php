<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ManageEmailVerification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:email-verification {email : User email address} {action : Action to perform (verify|unverify|status)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage email verification status for a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $action = strtolower($this->argument('action'));

        $validActions = ['verify', 'unverify', 'status'];
        if (! in_array($action, $validActions)) {
            $this->error("Invalid action '{$action}'. Valid actions are: ".implode(', ', $validActions));

            return Command::FAILURE;
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("User with email '{$email}' not found.");

            return Command::FAILURE;
        }

        switch ($action) {
            case 'verify':
                return $this->verifyUser($user);
            case 'unverify':
                return $this->unverifyUser($user);
            case 'status':
                return $this->showStatus($user);
        }

        return Command::FAILURE;
    }

    /**
     * Verify a user's email address.
     */
    protected function verifyUser(User $user): int
    {
        if ($user->hasVerifiedEmail()) {
            $this->info("✅ User '{$user->email}' is already verified.");

            return Command::SUCCESS;
        }

        $user->markEmailAsVerified();
        $this->info("✅ Successfully verified email for user '{$user->email}'.");
        $this->line("Verification timestamp: {$user->fresh()->email_verified_at->format('Y-m-d H:i:s')}");

        return Command::SUCCESS;
    }

    /**
     * Unverify a user's email address.
     */
    protected function unverifyUser(User $user): int
    {
        if (! $user->hasVerifiedEmail()) {
            $this->info("⚠️  User '{$user->email}' is already unverified.");

            return Command::SUCCESS;
        }

        $user->email_verified_at = null;
        $user->save();

        $this->info("❌ Successfully unverified email for user '{$user->email}'.");
        $this->line('User will now need to verify their email address before accessing the full application.');

        return Command::SUCCESS;
    }

    /**
     * Show the email verification status of a user.
     */
    protected function showStatus(User $user): int
    {
        $this->info("Email Verification Status for '{$user->email}':");
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");

        if ($user->hasVerifiedEmail()) {
            $this->line('Status: ✅ Verified');
            $this->line("Verified at: {$user->email_verified_at->format('Y-m-d H:i:s')}");
        } else {
            $this->line('Status: ❌ Not Verified');
            $this->line('The user needs to verify their email address to access the full application.');
        }

        return Command::SUCCESS;
    }
}
