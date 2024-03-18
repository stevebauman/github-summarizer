<?php

namespace App\Commands;

use Github\AuthMethod;
use Github\Client;
use Illuminate\Support\Facades\Cache;

trait InteractsWithGitHub
{
    use InteractsWithBaseDir;

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
     * Get the GitHub access token.
     */
    protected function getGitHubAccessToken(): string
    {
        return Cache::rememberForever('github_token', function () {
            return $this->secret('Enter your GitHub access token');
        });
    }
}
