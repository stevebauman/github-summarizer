<?php

namespace App;

use TitasGailius\Terminal\Terminal;

class Commit
{
    public static function last(string $dir): string
    {
        $result = Terminal::builder()
            ->in($dir)
            ->run('git rev-parse --short HEAD')
            ->throw()
            ->output();

        return trim(preg_replace('/\s+/', ' ', $result));
    }
}
