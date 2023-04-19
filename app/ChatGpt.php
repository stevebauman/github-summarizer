<?php

namespace App;

use App\Commands\SetAccount;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use UnexpectedValueException;

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
     * The available proxy URL's.
     */
    public static $urls = [
        'https://api.pawan.krd/backend-api/conversation',
        'https://ai.fakeopen.com/api/conversation',
    ];

    /**
     * Constructor.
     */
    public function __construct(
        protected string $token,
        protected string $model,
    ) {
        $this->assertValidModel($model);
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

        $body = retry(count(static::$urls), fn ($attempt) => (
            $this->http()->post(static::$urls[--$attempt], $this->makeMessage($question))
        ))->body();

        if (json_decode($body, true)) {
            $this->error = $body;

            return false;
        }

        return $this->getResponse($body);
    }

    /**
     * Get the response to the question from Chat GPT.
     *
     * @throws UnexpectedValueException
     */
    protected function getResponse(string $body): string
    {
        preg_match_all('/(?<=data:).*?(?=\n)/', $body, $matches);

        $matched = $matches[0];

        array_pop($matched);

        $response = last($matched);

        $data = json_decode($response, true);

        if (! Arr::has($data, 'message.content.parts')) {
            throw new UnexpectedValueException("Unexpected response received from ChatGPT: $body");
        }

        return implode(' ', $data['message']['content']['parts']);
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

    /**
     * Assert that the given model is valid.
     */
    protected function assertValidModel(string $model): void
    {
        throw_if(
            ! in_array($model, $models = array_values(static::$models)),
            sprintf('Model [%s] is invalid. Available models are [%s].', $model ?? 'NULL', implode(', ', $models))
        );
    }
}
