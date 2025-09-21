<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendTransactionalEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $mailable;
    public $mailerName;

    public function __construct(Mailable $mailable, string $mailerName)
    {
        $this->mailable = $mailable;
        $this->mailerName = $mailerName;
    }

    public function handle(): void
    {
        try {
            // ✅ THE FIX: Use Mail::mailer() to select the correct mailer configuration
            Mail::mailer($this->mailerName)->send($this->mailable);
        } catch (Throwable $e) {
            Log::error("Failed to send email using mailer [{$this->mailerName}]: " . $e->getMessage());
            $this->fail($e);
        }
    }
}