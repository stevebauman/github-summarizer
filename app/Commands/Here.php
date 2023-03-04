<?php

namespace App\Commands;

use App\Git;
use ptlis\DiffParser\File;

class Here extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'here {--all}';

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

        $diff = Git::diff($dir);

        $files = $this->parseDiff($diff);

        if (! $this->option('all')) {
            $names = array_map(fn (File $file) => (
                $file->newFilename
            ), $files);

            $file = $this->choice('Which changed file do you want to summarize?', $names);

            $index = array_search($file, $names);

            $files = [$files[$index]];
        }

        foreach ($files as $file) {
            $names = array_unique([
                $file->originalFilename === '/dev/null'
                    ? $file->newFilename
                    : $file->originalFilename,
                $file->newFilename,
            ]);

            $this->info(sprintf('Analyzing [%s]...', implode(' -> ', $names)));

            [$choice, $response] = $this->askForSummary($file);

            if ($choice === 'Commit') {
                Git::commit($dir, $file->newFilename, $response);

                $this->info('Okay, commit has been saved.');
            }
        }

        $this->newLine();

        $this->info('Done.');

        return static::SUCCESS;
    }

    /**
     * Ask for a summary of the file.
     */
    protected function askForSummary(File $file): array
    {
        $response = $this->chatgpt()->ask(
            $this->getQuestion((string) $file, 'commit')
        );

        switch ($choice = $this->choice($response, ['Commit', 'Retry', 'Pass'], 0)) {
            case 'Retry':
                $this->info('Okay, regenerating summary...');

                return $this->askForSummary($file);
            default:
                return [$choice, $response];
        }
    }
}
