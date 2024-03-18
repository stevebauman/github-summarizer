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
        );
    }

    /**
     * Get the ChatGPT token.
     */
    protected function getChatGptToken(): string
    {
        return Cache::rememberForever('openai_token', function () {
            return $this->secret('Enter your OpenAI ChatGPT token');
        });
    }
}
