<?php

namespace App;

use OpenAI;
use Exception;
use OpenAI\Client;
use Illuminate\Support\Str;
use OpenAI\Responses\Chat\CreateResponse;

class ChatGpt
{
    /**
     * The last error that occurred.
     */
    protected ?string $error = null;

    /**
     * The OpenAI API client.
     */
    protected Client $client;

    /**
     * Constructor.
     */
    public function __construct(string $token) {
        $this->client = OpenAI::client($token);
    }

    /**
     * Get the last error that occurred.
     */
    public function error(): ?string
    {
        return $this->error;
    }

    /**
     * Ask a question.
     */
    public function ask(string $question): string|false
    {
        $this->error = null;

        /** @var CreateResponse $result */
        $result = retry(
            times: 3,
            callback: fn () => $this->askQuestion($question),
            sleepMilliseconds: 1000,
            // Only retry if the exception isn't due to the prompt being unsafe.
            when: fn (Exception $e) => ! Str::contains($e->getMessage(), 'safety system')
        );

        return head($result->choices)->message->content;
    }

    /**
     * Send a question to ChatGPT.
     */
    protected function askQuestion(string $question, int $temperature = 0): CreateResponse
    {
        return $this->client->chat()->create([
            'model' => 'gpt-4-0125-preview',
            'temperature' => $temperature,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $question,
                ]
            ],
        ]);
    }
}
