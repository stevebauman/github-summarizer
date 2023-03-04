<?php

namespace App\Commands;

use App\ChatGpt;
use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;

class SetAccount extends Command
{
    public const CACHE_KEY = 'account';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'set:account';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Set the account type to use';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->choice('What type of account do you have?', array_keys(ChatGpt::$models));

        Cache::forever(SetAccount::CACHE_KEY, $type);

        $this->info("Okay, now using [$type] account.");

        return static::SUCCESS;
    }
}
