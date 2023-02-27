<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    use InteractsWithGitHub;
    use InteractsWithChatGpt;

    /**
     * Summarize the patch files using Chat GPT.
     */
    protected function summarize(array $files): void
    {
        $chatgpt = $this->chatgpt();

        foreach ($files as $file) {
            $diff = <<<EOT
            --- {$file['filename']}
            +++ {$file['filename']}
            {$file['patch']}
            EOT;

            $response = $chatgpt->ask(<<<EOT
                Describe below diff in a short sentence like a changelog entry:
                $diff
                EOT
            );

            if ($response === false) {
                $this->error("ChatGPT Error: " . $chatgpt->error());

                exit(static::FAILURE);
            }

            $this->line("- $response");
        }
    }

    /**
     * Get the repsotory to query.
     */
    protected function getRepository(): array
    {
        if (! ($repo = $this->argument('repo'))) {
            $repo = $this->ask('Which repository?');
        }

        return explode('/', $repo);
    }
}
