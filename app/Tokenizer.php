<?php

namespace App;

use Danny50610\BpeTokeniser\Encoding;
use Danny50610\BpeTokeniser\EncodingFactory;

class Tokenizer
{
    /**
     * The cached encoder instances.
     */
    protected static array $encoders = [];

    /**
     * Count the number of tokens in the prompt.
     */
    public static function count(string $prompt, string $model = 'gpt-4'): int
    {
        return count(
            Tokenizer::encoder($model)->encode($prompt)
        );
    }

    /**
     * Get a new encoder instance.
     */
    protected static function encoder(string $model): Encoding
    {
        return static::$encoders[$model] ??= EncodingFactory::createByModelName($model);
    }
}
