<?php

namespace App\Commands;

use Illuminate\Support\Str;

class Commit extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'commit {repo?} {sha?} {--from=} {--to=master} {--branch=master}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Summarize a commit or range of commits';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        [$org, $name] = $this->getRepository();

        $files = $this->getCommitFiles($org, $name);

        $this->line(sprintf("Summarizing %s file commits...", count($files)));

        $this->summarize($files);

        $this->newLine();

        return static::SUCCESS;
    }

    /**
     * Get the commit files to summarize.
     */
    protected function getCommitFiles(string $org, string $repo): array
    {
        if ($sha = $this->argument('sha')) {
            return $this->github()->api('repo')->commits()->show($org, $repo, $sha)['files'];
        }

        if (($from = $this->option('from')) && ($to = $this->option('to'))) {
            return $this->github()->api('repo')->commits()->compare($org, $repo, $from, $to)['files'];
        }

        $commits = $this->github()->api('repo')->commits()->all($org, $repo, ['sha' => $this->option('branch')]);

        $titles = array_map(fn ($commit) => (
            implode('|', [
                Str::limit($commit['commit']['message'], 20),
                $commit['commit']['author']['name'],
                $commit['sha']
            ])
        ), $commits);

        [,,$sha] = explode('|', $this->choice('Commit?', $titles));

        return $this->github()->api('repo')->commits()->show($org, $repo, $sha)['files'];
    }
}
