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

    /**
     * @var \Illuminate\Mail\Mailable
     */
    public $mailable;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Mail\Mailable $mailable
     */
    public function __construct(Mailable $mailable)
    {
        $this->mailable = $mailable;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            Mail::send($this->mailable);
        } catch (Throwable $e) {
            // Log the error and allow the job to be released back to the queue for another try.
            Log::error("Failed to send email: " . $e->getMessage());
            $this->fail($e);
        }
    }
}