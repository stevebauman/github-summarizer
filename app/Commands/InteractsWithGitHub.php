<?php

namespace App\Commands;

use Github\AuthMethod;
use Github\Client;
use Illuminate\Support\Facades\File;

trait InteractsWithGitHub
{
    /**
     * Make a GitHub API client.
     */
    public function github(string $accessToken): Client
    {
        $client = new Client();

        $client->authenticate($accessToken, authMethod: AuthMethod::ACCESS_TOKEN);

        return $client;
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

            return static::FAILURE;
        }

        if (empty($token = File::get($tokenPath))) {
            $this->error("GitHub access token file at [$tokenPath] is empty.");

            return static::FAILURE;
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
