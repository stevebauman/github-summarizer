<?php

namespace App;

class FilePatch
{
    /**
     * Constructor.
     */
    public function __construct(
        public readonly string $filename,
        public readonly ?string $previousFilename = null,
        public readonly string $contents = ''
    ) {}
}
