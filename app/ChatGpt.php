<?php

namespace App;

use App\Commands\SetAccount;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatGpt
{
    public const ACCOUNT_FREE = 'free';
    public const ACCOUNT_PLUS = 'plus';
    public const ACCOUNT_TURBO = 'turbo';

    /**
     * The last error that occurred.
     */
    protected ?string $error = null;

    /**
     * The available models.
     */
    public static $models = [
        ChatGpt::ACCOUNT_FREE => 'text-davinci-002-render',
        ChatGpt::ACCOUNT_PLUS => 'text-davinci-002-render-paid',
        ChatGpt::ACCOUNT_TURBO => 'text-davinci-002-render-sha',
    ];

    /**
     * Constructor.
     */
    public function __construct(
        protected string $token,
        protected string $model = ChatGpt::ACCOUNT_FREE,
        protected string $url = 'https://bypass.duti.tech/api/conversation',
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
        $this->error = null;

        $body = $this->http()->post($this->url, $this->makeMessage($question))->body();

        if ($json = json_decode($body, true)) {
            $this->error = is_array($json['detail'])
                ? $json['detail']['message']
                : $json['detail'];

            return false;
        }

        return $this->getResponse($body);
    }

    /**
     * Get the response to the question from Chat GPT.
     */
    protected function getResponse(string $body): string
    {
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
            'model' => Cache::get(SetAccount::CACHE_KEY, $this->model),
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
