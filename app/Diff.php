<?php

namespace App;

use TitasGailius\Terminal\Terminal;

class Diff
{
    public function __construct(
        protected ?string $dir = null
    ) {
    }

    public function get(string $to = null, string $from = null): string
    {
        $args = array_filter([
            'from' => $from ?? $to ? 'master' : null,
            'to' => $to,
        ]);

        $command = count($args) === 2
            ? 'git diff {{ $from}}..{{ $to }}'
            : 'git diff';

        return Terminal::builder()
            ->in($this->dir)
            ->with($args)
            ->run($command)
            ->throw()
            ->output();
    }
}
