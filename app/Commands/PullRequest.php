<?php

namespace App\Commands;

class PullRequest extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'pr {repo?} {--number=} {--state=open} {--style=changelog}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Summarize a GitHub pull request';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        [$org, $name] = $this->getRepository();

        $pr = $this->getPullRequest($org, $name);

        $files = $this->getCommits($org, $name, $pr['merge_commit_sha'])['files'];

        $this->line("Summarizing pull request '{$pr['title']}'...");

        $this->newLine();

        $this->summarize($files);

        return static::SUCCESS;
    }

    /**
     * Get a list of commits from the given PR SHA.
     */
    protected function getCommits(string $org, string $repo, string $commitSha): array
    {
        return $this->github()->api('repo')->commits()->show($org, $repo, $commitSha);
    }

    /**
     * Get the pull request to summarize.
     */
    protected function getPullRequest( string $org, string $repo): array
    {
        if ($number = $this->option('number')) {
            return $this->github()->api('pull_request')->show($org, $repo, $number);
        }

        $prs = $this->github()->api('pull_request')->all($org, $repo, ['state' => $this->option('state')]);

        $titles = array_map(fn ($pr) => $pr['number'].'|'.$pr['title'], $prs);

        $title = $this->choice('Which pull request do you want to summarize?', $titles);

        [$number] = explode('|', $title);

        return collect($prs)->where('number', $number)->sole();
    }
}
