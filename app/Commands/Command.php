<?php

namespace App\Commands;

use App\FilePatch;
use Gioni06\Gpt3Tokenizer\Gpt3Tokenizer;
use Gioni06\Gpt3Tokenizer\Gpt3TokenizerConfig;
use LaravelZero\Framework\Commands\Command as BaseCommand;
use ptlis\DiffParser\File;
use ptlis\DiffParser\Parser;

abstract class Command extends BaseCommand
{
    use InteractsWithGitHub;
    use InteractsWithChatGpt;

    public const MAX_TOKENS = 4096;

    /**
     * The GTP3 tokenizer instance.
     */
    protected ?Gpt3Tokenizer $tokenizer = null;

    /**
     * Summarize the patch files using Chat GPT.
     */
    protected function summarize(array $files): void
    {
        foreach ($files as $file) {
            $this->generateFileSummary(
                new FilePatch(
                    $file['filename'],
                    $file['previous_filename'] ?? null,
                    $file['patch']
                )
            );
        }
    }

    /**
     * Generate a summary for the file patch.
     */
    protected function generateFileSummary(FilePatch $patch)
    {
        $originalFilename = $patch->previousFilename ?? $patch->filename;

        $diff = <<<EOT
        --- $originalFilename
        +++ $patch->filename
        $patch->contents
        EOT;

        $tokens = $this->tokenizer()->count($diff);

        if ($tokens >= static::MAX_TOKENS) {
            return $this->splitUpDiffAndSummarize($diff);
        }

        $response = retry(2, fn () => (
            $this->chatgpt()->ask($this->getQuestion($diff, $this->option('style')))
        ), 2000);

        if ($response === false) {
            $this->error("ChatGPT Error: " . $this->chatgpt()->error());

            exit(static::FAILURE);
        }

        $this->line("- $response");
    }

    /**
     * Split the diff in half and attempt summarization.
     */
    protected function splitUpDiffAndSummarize(string $diff)
    {
        $file = head($this->parseDiff($diff));

        $lines = $file->hunks[0]->lines;

        $count = count($lines);

        $firstHalf = array_slice($lines, 0, $count / 2);
        $secondHalf = array_slice($lines, $count / 2);

        $this->summarize([
            $this->assembleDiffWithLines($file, $firstHalf),
            $this->assembleDiffWithLines($file, $secondHalf),
        ]);
    }

    /**
     * Parse the diff.
     *
     * @return \ptlis\DiffParser\File[]
     */
    protected function parseDiff(string $diff): array
    {
        return (new Parser)->parse($diff, Parser::VCS_GIT)->files;
    }

    /**
     * Assemble a new diff with the given lines.
     */
    protected function assembleDiffWithLines(File $file, array $lines): array
    {
        return [
            'patch' => implode('\\n', $lines),
            'filename' => $file->newFilename,
            'previous_filename' => $file->originalFilename,
        ];
    }

    /**
     * Get the question to ask Chat GPT.
     */
    protected function getQuestion(string $diff, string $style): false|string
    {
        $type = match ($style) {
            'commit' => "commit message. Use words like 'add', 'remove' and 'change'",
            default => "changelog entry. Use words like 'added', 'removed' and 'changed'",
        };

        return <<<EOT
        Describe below diff in a short sentence like a $type:
        $diff
        EOT;
    }

    /**
     * Get or create the tokenizer instance.
     */
    protected function tokenizer(): Gpt3Tokenizer
    {
        return $this->tokenizer ??= new Gpt3Tokenizer(
            new Gpt3TokenizerConfig()
        );
    }

    /**
     * Get the repository to query.
     */
    protected function getRepository(): array
    {
        if (! ($repo = $this->argument('repo'))) {
            $repo = $this->ask('Which repository?');
        }

        return explode('/', $repo);
    }
}
