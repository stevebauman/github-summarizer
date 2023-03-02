<?php

namespace App\Commands;

use App\Git;

class Here extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'here';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Summarize the commits in the local repository and commit the result';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dir = getcwd();

        if (! Git::isRepository($dir)) {
            $this->error(<<<EOT
            It looks like you are not in a git repository.
            Run this command from the root of a git repository, or initialize one using `git init`.
            EOT);

            return static::FAILURE;
        }

        if (! Git::hasCommits($dir)) {
            if (! $this->confirm('There are no staged files to commit. Do you want to stage all files (git add *)?')) {
                return static::FAILURE;
            }

            Git::add($dir, '*');

            $this->info('Okay, all files have been staged.');
        }

        array_map(fn ($file) => (
            Git::add($dir, $file)
        ), Git::untrackedFiles($dir));

        $files = $this->parseDiff(Git::diff($dir));

        foreach ($files as $file) {
            $names = array_unique([$file->originalFilename, $file->newFilename]);

            $this->info(sprintf('Analyzing [%s]...', implode(' -> ', $names)));

            $response = $this->chatgpt()->ask(
                $this->getQuestion((string) $file, 'commit')
            );

            if (! $this->confirm($response)) {
                continue;
            }

            if (! Git::commit($dir, $file->newFilename, $response)) {
                $this->error('Failed to commit file. Stopping...');

                return static::FAILURE;
            }

            $this->info('Okay, commit has been saved.');
        }

        return static::SUCCESS;
    }
}
