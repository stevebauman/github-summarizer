<?php

namespace App;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatGpt
{
    /**
     * The last error that occurred.
     */
    protected ?string $error = null;

    /**
     * Constructor.
     */
    public function __construct(
        protected string $token,
        protected string $model = 'text-davinci-002-render-sha',
        protected string $url = 'https://chat.duti.tech/api/conversation',
    ) {}

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
        $body = $this->http()->post($this->url, $this->makeMessage($question))->body();

        if ($json = json_decode($body, true)) {
            $this->error = $json['detail']['message'];

            return false;
        }

        preg_match_all('/(?<=data:).*?(?=\n)/', $body, $matches);

        $matched = $matches[0];

        array_pop($matched);

        $response = last($matched);

        $parts = json_decode($response, true)['message']['content']['parts'];

        return implode(' ', $parts);
    }

    /**
     * Make a new Chat GPT message.
     */
    protected function makeMessage(string $question): array
    {
        return [
            'action' => 'next',
            'model' => $this->model,
            'parent_message_id' => Str::uuid(),
            'messages' => [
                [
                    'id' => Str::uuid(),
                    'role' => 'user',
                    'content' => [
                        'content_type' => 'text',
                        'parts' => [$question],
                    ],
                ]
            ],
        ];
    }

    /**
     * Make a new HTTP request.
     */
    protected function http(): PendingRequest
    {
        return Http::contentType('application/json')
                ->accept('text/event-stream')
                ->withToken($this->token)
                ->throw();
    }
}
