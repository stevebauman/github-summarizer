<?php

namespace App\Commands;

use App\ChatGpt;
use Illuminate\Support\Facades\Cache;

/** @mixin \LaravelZero\Framework\Commands\Command */
trait InteractsWithChatGpt
{
    use InteractsWithBaseDir;

    /**
     * The ChatGPT API client.
     */
    protected ?ChatGpt $chatGpt = null;

    /**
     * Create a new Chat GPT client.
     */
    protected function chatgpt(): ChatGpt
    {
        return $this->chatGpt ??= new ChatGpt(
            $this->getChatGptToken(),
            $this->getChatGptModel(),
        );
    }

    /**
     * Get the Chat GPT model to utilize.
     */
    protected function getChatGptModel(): string
    {
        return ChatGpt::$models[Cache::get(SetAccount::CACHE_KEY, ChatGpt::ACCOUNT_FREE)];
    }

    /**
     * Get the CHAT GPT token from the session file.
     */
    protected function getChatGptToken(): string
    {
        $contents = $this->getOrCreateInHomeDir('.gpt_session', $title = 'Chat GPT session');

        if (! ($json = json_decode($contents, true))) {
            $this->error("$title file contains invalid JSON.");

            exit(static::FAILURE);
        }

        if (empty($token = $json['accessToken'] ?? null)) {
            $this->error("$title file does not contain an [accessToken] JSON key.");

            exit(static::FAILURE);
        }

        return $token;
    }
}
