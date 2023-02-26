<?php

namespace App;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatGpt
{
    protected ?string $error = null;

    public function __construct(
        protected string $token,
        protected string $model = 'text-davinci-002-render-sha',
        protected string $url = 'https://chat.duti.tech/api/conversation',
    ) {}

    public function error()
    {
        return $this->error;
    }

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

    protected function http(): PendingRequest
    {
        return Http::contentType('application/json')
                ->accept('text/event-stream')
                ->withToken($this->token)
                ->throw();
    }
}
