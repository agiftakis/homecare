<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendTransactionalEmail;
use Illuminate\Mail\Mailable;
use Exception;
use Carbon\Carbon;

class MultiGmailEmailService
{
    /**
     * @var array
     */
    protected $accounts;

    /**
     * Daily email limit per account. Set lower than 500 for safety.
     * @var int
     */
    protected const DAILY_LIMIT = 450;

    public function __construct()
    {
        // Initialize accounts from environment variables
        $this->accounts = [
            [
                'mailer' => 'gmail_1',
                'username' => env('GMAIL_1_USERNAME'),
                'password' => env('GMAIL_1_PASSWORD'),
                'from_name' => env('GMAIL_1_FROM_NAME', 'VitaLink Notifications'),
            ],
            [
                'mailer' => 'gmail_2',
                'username' => env('GMAIL_2_USERNAME'),
                'password' => env('GMAIL_2_PASSWORD'),
                'from_name' => env('GMAIL_2_FROM_NAME', 'VitaLink Notifications'),
            ],
            [
                'mailer' => 'gmail_3',
                'username' => env('GMAIL_3_USERNAME'),
                'password' => env('GMAIL_3_PASSWORD'),
                'from_name' => env('GMAIL_3_FROM_NAME', 'VitaLink Notifications'),
            ],
        ];
    }

    /**
     * Dispatches a mailable to the queue using an available Gmail account.
     *
     * @param Mailable $mailable
     * @return bool Returns true on success, false on failure.
     */
    public function dispatch(Mailable $mailable): bool
    {
        try {
            $account = $this->getNextAvailableAccount();

            if (!$account) {
                Log::critical('EMAIL_FAILURE: No available Gmail accounts to send email.');
                return false;
            }

            // Temporarily set the mailer configuration for this job dispatch
            $this->setMailerConfig($account);

            // Dispatch the job to the queue
            SendTransactionalEmail::dispatch($mailable);
            
            // Increment the send count for the used account
            $this->incrementSendCount($account['mailer']);

            Log::info("Email job dispatched using account: {$account['mailer']}");
            
            return true;

        } catch (Exception $e) {
            Log::error("EMAIL_DISPATCH_ERROR: Failed to dispatch email job. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds the next available Gmail account that is under its daily limit.
     *
     * @return array|null
     */
    protected function getNextAvailableAccount(): ?array
    {
        foreach ($this->accounts as $account) {
            // Skip accounts that don't have a configured username
            if (empty($account['username'])) {
                continue;
            }

            $count = $this->getSendCount($account['mailer']);

            if ($count < self::DAILY_LIMIT) {
                return $account;
            }
        }

        return null; // No accounts available
    }

    /**
     * Gets the current daily send count for a specific mailer.
     * Resets the count if it's a new day.
     *
     * @param string $mailer
     * @return int
     */
    protected function getSendCount(string $mailer): int
    {
        $cacheKey = 'email_count_' . $mailer;
        $today = Carbon::today()->toDateString();
        
        $data = Cache::get($cacheKey, ['count' => 0, 'date' => $today]);

        // If the date in cache is not today, reset the count
        if ($data['date'] !== $today) {
            $data = ['count' => 0, 'date' => $today];
            Cache::put($cacheKey, $data, Carbon::now()->addDay());
        }

        return $data['count'];
    }

    /**
     * Increments the daily send count for a specific mailer.
     *
     * @param string $mailer
     */
    protected function incrementSendCount(string $mailer): void
    {
        $cacheKey = 'email_count_' . $mailer;
        $today = Carbon::today()->toDateString();
        
        $data = Cache::get($cacheKey, ['count' => 0, 'date' => $today]);
        $data['count']++;
        
        Cache::put($cacheKey, $data, Carbon::now()->addDay());
    }

    /**
     * Temporarily sets Laravel's mail configuration for the selected account.
     *
     * @param array $account
     */
    protected function setMailerConfig(array $account): void
    {
        Config::set('mail.default', $account['mailer']);
        Config::set("mail.mailers.{$account['mailer']}.username", $account['username']);
        Config::set("mail.mailers.{$account['mailer']}.password", $account['password']);
        Config::set('mail.from.address', $account['username']);
        Config::set('mail.from.name', $account['from_name']);
    }
}