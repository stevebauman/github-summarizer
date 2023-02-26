<?php

namespace App\Commands;

use App\ChatGpt;
use Illuminate\Support\Facades\File;

/** @mixin \LaravelZero\Framework\Commands\Command */
trait InteractsWithChatGpt
{
    /**
     * Create a new Chat GPT client.
     */
    protected function chatgpt(string $token): ChatGpt
    {
        return new ChatGpt($token);
    }

    /**
     * Get the CHAT GPT token from the session file.
     */
    protected function getChatGptToken(): int|string
    {
        $sessionPath = $this->getChatGptSessionPath();

        if (! File::exists($sessionPath)) {
            $this->warn("No Chat GPT session file exists at path [$sessionPath]. Attempting to create file...");

            File::put($sessionPath, '');

            $this->info("Successfully created session file at [$sessionPath]. Please fill in your Chat GPT session JSON.");

            return static::FAILURE;
        }

        if (empty($contents = File::get($sessionPath))) {
            $this->error("Chat GPT session file at [$sessionPath] is empty.");

            return static::FAILURE;
        }

        if (! ($json = json_decode($contents, true))) {
            $this->error("Chat GPT session file at [$sessionPath] contains invalid JSON.");

            return static::FAILURE;
        }

        if (empty($token = $json['accessToken'] ?? null)) {
            $this->error("Chat GPT session file does not contain an [accessToken] JSON key.");

            return static::FAILURE;
        }

        return $token;
    }

    /**
     * Get the Chat GPT session file path.
     */
    protected function getChatGptSessionPath(): string
    {
        return $_SERVER['HOME'] . DIRECTORY_SEPARATOR . '.gpt_session';
    }
}
