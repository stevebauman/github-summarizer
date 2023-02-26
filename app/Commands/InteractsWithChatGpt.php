<?php

namespace App\Commands;

use App\ChatGpt;
use Illuminate\Support\Facades\File;

/** @mixin \LaravelZero\Framework\Commands\Command */
trait InteractsWithChatGpt
{
    protected function chatgpt()
    {
        return new ChatGpt($this->getToken());
    }

    protected function getToken()
    {
        $tokenPath = $this->getTokenPath();

        if (! File::exists($tokenPath)) {
            $this->warn("No access token exists at path [$tokenPath]. Attempting to create file...");

            File::put($tokenPath, '');

            $this->info("Successfully created access token file at [$tokenPath]. Please fill in your Chat GPT access token.");

            return static::FAILURE;
        }

        if (empty($token = File::get($tokenPath))) {
            $this->error("Access token file at [$tokenPath] is empty.");

            return static::FAILURE;
        }

        return $token;
    }

    protected function getTokenPath()
    {
        return $_SERVER['HOME'] . DIRECTORY_SEPARATOR . '.gpt_token';
    }
}
