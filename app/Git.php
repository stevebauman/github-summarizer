<?php

namespace App;

use TitasGailius\Terminal\Terminal;

class Git
{
    /**
     * Determine if the repository contains commits.
     */
    public static function hasCommits(string $dir): bool
    {
        $response = Terminal::builder()
            ->in($dir)
            ->run('git diff')
            ->throw()
            ->output();

        return strlen($response) > 0;
    }

    /**
     * Determine if the directory is a git repository.
     */
    public static function isRepository(string $dir): bool
    {
        $response = Terminal::builder()
            ->in($dir)
            ->run('git rev-parse --is-inside-work-tree')
            ->throw()
            ->output();

        return trim($response) === 'true';
    }

    /**
     * Add a file to be tracked.
     */
    public static function add(string $dir, string $filename): bool
    {
        return Terminal::builder()
            ->in($dir)
            ->with('filename', $filename)
            ->run('git add {{ $filename }}')
            ->throw()
            ->successful();
    }

    /**
     * Get all untracked files.
     *
     * @return string[]
     */
    public static function untrackedFiles(string $dir): array
    {
        $response = Terminal::builder()
            ->in($dir)
            ->run('git ls-files --others --exclude-standard')
            ->throw()
            ->output();

        if (empty($response)) {
            return [];
        }

        return preg_split('/\n/', trim($response));
    }

    /**
     * Get all the diffs in the given directory.
     */
    public static function diff(string $dir = null): string
    {
        return Terminal::builder()
            ->in($dir)
            ->run('git diff HEAD')
            ->throw()
            ->output();
    }

    /**
     * Commit the file with the given message.
     */
    public static function commit(string $dir, string $filename, string $message): bool
    {
        return Terminal::builder()
            ->in($dir)
            ->with('filename', $filename)
            ->with('message', $message)
            ->run('git commit -m {{ $message }} -- {{ $filename }}')
            ->throw()
            ->successful();
    }
}
