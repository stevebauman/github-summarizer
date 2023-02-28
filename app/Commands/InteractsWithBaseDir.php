<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use XdgBaseDir\Xdg;

trait InteractsWithBaseDir
{
    /**
     * Get the user's home directory path.
     */
    protected function getHomeDir(): string
    {
        return (new Xdg())->getHomeDir();
    }

    /**
     * Get or create the file in the user's home directory.
     */
    protected function getOrCreateInHomeDir(string $filename, string $title): string
    {
        $path = $this->getHomeDir() . DIRECTORY_SEPARATOR . $filename;

        if (! File::exists($path)) {
            $this->warn("No $title file exists at path [$path]. Attempting to create file...");

            File::put($path, '');

            $this->info("Successfully created $title at [$path]. Please open the file and paste in your $title.");

            exit(static::FAILURE);
        }

        if (empty($contents = File::get($path))) {
            $this->error("$title file at [$path] is empty.");

            exit(static::FAILURE);
        }

        return $contents;
    }
}
