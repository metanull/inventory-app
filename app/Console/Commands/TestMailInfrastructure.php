<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailInfrastructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {recipient : The email address to send the test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify mail infrastructure.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recipient = $this->argument('recipient');
        try {
            Mail::raw('This is a test email from your Laravel deployment.', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Laravel Mail Test');
            });
            $this->info("Test email sent to {$recipient}. Check your inbox.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to send test email: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
