<?php

namespace App\Commands;

use Github\AuthMethod;
use Github\Client;

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
     * Get the GitHub access token from the token file.
     */
    protected function getGitHubAccessToken(): string
    {
        return $this->getOrCreateInHomeDir('.gh_token', 'GitHub access token');
    }
}
