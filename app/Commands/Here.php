<?php

namespace App\Commands;

use App\Git;
use Illuminate\Support\Str;
use ptlis\DiffParser\File;
use UnexpectedValueException;

class Here extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'here {files?} {--all}';

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

        $files = $this->getFilesToSummarize(
            $this->parseDiff($diff)
        );

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
     * Get the files to summarize.
     */
    protected function getFilesToSummarize(array $files): array
    {
        if ($this->option('all')) {
            return $files;
        }

        if ($only = $this->argument('files')) {
            $names = preg_split('/[,\n]/', trim($only));

            return array_filter($files, fn (File $file) => (
                in_array($file->newFilename, $names)
                || in_array($file->originalFilename, $names)
            ));
        }

        $names = array_map(fn (File $file) => (
            $file->newFilename
        ), $files);

        $file = $this->choice('Which changed file do you want to summarize?', $names);

        $index = array_search($file, $names);

        return [$files[$index]];
    }

    /**
     * Ask for a summary of the file.
     */
    protected function askForSummary(File $file): array
    {
        $response = $this->chatgpt()->ask(
            $this->getQuestion((string) $file, 'commit')
        );

        if (! $response) {
            throw new UnexpectedValueException("ChatGPT returned an unexpected response: " . $this->chatgpt()->error());
        }

        switch ($choice = $this->choice($response, ['Commit', 'Retry', 'Pass'], 0)) {
            case 'Retry':
                $this->info('Okay, regenerating summary...');

                return $this->askForSummary($file);
            default:
                return [$choice, $response];
        }
    }
}
