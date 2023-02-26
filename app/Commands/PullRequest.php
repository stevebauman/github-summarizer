<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class PullRequest extends Command
{
    use InteractsWithGitHub;
    use InteractsWithChatGpt;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'pr {repo?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Summarize a GitHub pull request';

    /**
     * Execute the console command.
     */
    public function handle(): mixed
    {
        if (($githubAccessToken = $this->getGitHubAccessToken()) === static::FAILURE) {
            return $githubAccessToken;
        }

        $github = $this->github($githubAccessToken);

        if (! ($repo = $this->argument('repo'))) {
            $repo = $this->ask('Which repository?');
        }

        [$org, $name] = explode('/', $repo);

        $prs = $github->api('pull_request')->all($org, $name, ['state' => 'open']);

        $titles = array_map(fn ($pr) => $pr['number'].'|'.$pr['title'], $prs);

        $title = $this->choice('Which pull request do you want to summarize?', $titles);

        [$number] = explode('|', $title);

        $pr = collect($prs)->firstWhere('number', $number);

        $commits = $github->api('repo')->commits()->show($org, $name, $pr['merge_commit_sha']);

        $files = $commits['files'];

        if (($chatGptToken = $this->getChatGptToken()) === static::FAILURE) {
            return $chatGptToken;
        }

        $chatgpt = $this->chatgpt($chatGptToken);

        $this->line("Summarizing pull request [$title]...");

        $this->newLine();

        foreach ($files as $file) {
            $diff = <<<EOT
            --- {$file['filename']}
            +++ {$file['filename']}
            {$file['patch']}
            EOT;

            $response = retry(3, fn () => (
                $chatgpt->ask(<<<EOT
                    Describe below diff in a short sentence like a changelog entry:
                    $diff
                    EOT
                )
            ), 1000);

            if ($response === false) {
                $this->error($chatgpt->error());

                return static::FAILURE;
            }

            $this->line("- $response");
        }

        return static::SUCCESS;
    }
}
