<?php

namespace App\Commands;

use Github\AuthMethod;
use Github\Client;
use Illuminate\Support\Facades\File;

trait InteractsWithGitHub
{
    /**
     * The GitHub API client.
     */
    protected ?Client $github = null;

    /**
     * Make a GitHub API client.
     */
    public function github(): Client
    {
        return $this->github ??= tap(new Client)->authenticate(
            $this->getGitHubAccessToken(),
            authMethod: AuthMethod::ACCESS_TOKEN
        );
    }

    /**
     * Get the GitHub access token from the token file.
     */
    protected function getGitHubAccessToken(): string
    {
        $tokenPath = $this->getGitHubAccessTokenPath();

        if (! File::exists($tokenPath)) {
            $this->warn("No GitHub access token file exists at path [$tokenPath]. Attempting to create file...");

            File::put($tokenPath, '');

            $this->info("Successfully created access token file at [$tokenPath]. Please fill in your GitHub access token.");

            exit(static::FAILURE);
        }

        if (empty($token = File::get($tokenPath))) {
            $this->error("GitHub access token file at [$tokenPath] is empty.");

            exit(static::FAILURE);
        }

        return $token;
    }

    /**
     * Get the Chat GPT session file path.
     */
    protected function getGitHubAccessTokenPath(): string
    {
        return $_SERVER['HOME'] . DIRECTORY_SEPARATOR . '.gh_token';
    }
}
