<?php

namespace App\Commands;

use App\ChatGpt;
use App\Commit;
use App\Diff;
use Gioni06\Gpt3Tokenizer\Gpt3Tokenizer;
use Gioni06\Gpt3Tokenizer\Gpt3TokenizerConfig;
use Github\AuthMethod;
use Github\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use ptlis\DiffParser\Parser;

class PullRequest extends Command
{
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
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client();

        $client->authenticate('token', authMethod: AuthMethod::ACCESS_TOKEN);

        if (! ($repo = $this->argument('repo'))) {
            $repo = $this->ask('Which repository?');
        }

        [$org, $name] = explode('/', $repo);

        $prs = $client->api('pull_request')->all($org, $name, ['state' => 'open']);

        $titles = array_map(fn ($pr) => $pr['number'].'|'.$pr['title'], $prs);

        $title = $this->choice('Which pull request do you want to summarize?', $titles);

        [$number] = explode('|', $title);

        $pr = collect($prs)->firstWhere('number', $number);

        $commits = $client->api('repo')->commits()->show($org, $name, $pr['merge_commit_sha']);

        $files = $commits['files'];

        if (($token = $this->getToken()) === static::FAILURE) {
            return $token;
        }

        $gpt = new ChatGpt($token);

        $this->line("Summarizing pull request [$title]...");

        $this->newLine();

        // $tokenizer = new Gpt3Tokenizer(new Gpt3TokenizerConfig);

        foreach ($files as $file) {
            $diff = <<<EOT
            --- {$file['filename']}
            +++ {$file['filename']}
            {$file['patch']}
            EOT;

//            /** @var  \ptlis\DiffParser\File $changeset */
//            $changeset = head((new Parser)->parse($diff, Parser::VCS_GIT)->files);
//
//            $lines = $changeset->hunks[0]->lines;
//
//            $count = count($lines);
//
//            $firstHalf = array_slice($lines, 0, $count / 2);
//            $secondHalf = array_slice($lines, $count / 2);

            $response = retry(3, fn () => (
                $gpt->ask(<<<EOT
                    Describe below diff in a short sentence like a changelog entry:
                    $diff
                    EOT
                )
            ), 1000);

            if ($response === false) {
                $this->error($gpt->error());

                return static::FAILURE;
            }

            $this->line("- $response");
        }

        return static::SUCCESS;
    }


}
