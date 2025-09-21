<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendTransactionalEmail;
use Illuminate\Mail\Mailable;
use Exception;
use Carbon\Carbon;

class MultiGmailEmailService
{
    protected $accounts;
    protected const DAILY_LIMIT = 450;

    public function __construct()
    {
        $this->accounts = [ 'gmail_1', 'gmail_2', 'gmail_3' ];
    }

    public function dispatch(Mailable $mailable): bool
    {
        try {
            $mailerName = $this->getNextAvailableMailer();

            if (!$mailerName) {
                Log::critical('EMAIL_FAILURE: No available Gmail accounts to send email.');
                return false;
            }

            SendTransactionalEmail::dispatch($mailable, $mailerName);
            
            $this->incrementSendCount($mailerName);

            Log::info("Email job dispatched using mailer: {$mailerName}");
            
            return true;

        } catch (Exception $e) {
            Log::error("EMAIL_DISPATCH_ERROR: Failed to dispatch email job. Error: " . $e->getMessage());
            return false;
        }
    }

    protected function getNextAvailableMailer(): ?string
    {
        foreach ($this->accounts as $mailerName) {
            // Check if the corresponding username is configured in the .env
            $username = config("mail.mailers.{$mailerName}.username");
            if (empty($username)) {
                continue;
            }

            if ($this->getSendCount($mailerName) < self::DAILY_LIMIT) {
                return $mailerName;
            }
        }
        return null;
    }

    protected function getSendCount(string $mailer): int
    {
        $cacheKey = 'email_count_' . $mailer;
        $today = Carbon::today()->toDateString();
        $data = Cache::get($cacheKey, ['count' => 0, 'date' => $today]);
        if ($data['date'] !== $today) {
            $data = ['count' => 0, 'date' => $today];
            Cache::put($cacheKey, $data, Carbon::now()->addDay());
        }
        return $data['count'];
    }

    protected function incrementSendCount(string $mailer): void
    {
        $cacheKey = 'email_count_' . $mailer;
        $today = Carbon::today()->toDateString();
        $data = Cache::get($cacheKey, ['count' => 0, 'date' => $today]);
        $data['count']++;
        Cache::put($cacheKey, $data, Carbon::now()->addDay());
    }
}