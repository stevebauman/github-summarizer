<?php

namespace App;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Authenticator
{
    protected string $userAgent = '"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36"';

    public function __construct(
        protected string $email,
        protected string $password,
        protected CookieJar $cookieJar = new CookieJar()
    ) {
    }

    public function execute()
    {
        $response = $this->http()
            ->throw()
            ->get('https://explorer.api.openai.com/api/auth/csrf');

        $this->first($response->json('csrfToken'));
    }

    public function first(string $csrfToken)
    {
        $url = $this->http()
            ->throw()
            ->post('https://explorer.api.openai.com/api/auth/signin/auth0?prompt=login', [
                'callbackUrl' => '/',
                'csrfToken' => $csrfToken,
                'json' => 'true',
            ])->json('url');

        if ($url === 'https://explorer.api.openai.com/api/auth/error?error=OAuthSignin' || str_contains($url, 'error')) {
            throw new \Exception('You have been rate limited. Please try again later.');
        }

        $this->second($url);
    }

    public function second(string $url)
    {
        $data = $this->http()
            ->throw()
            ->get($url)
            ->body();

        preg_match('/state=(.*)/', $data, $matches);

        $state = Str::before(explode('=', $matches[0])[1], '"');

        $this->third($state);
    }

    public function third(string $state)
    {
        $this->http()->get('https://auth0.openai.com/u/login/identifier', [
            'state' => $state
        ]);

        $this->fourth($state);
    }

    public function fourth(string $state)
    {
        $url =  "https://auth0.openai.com/u/login/identifier?state=$state";

        $this->http()
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->post($url, [
                'state' => $state,
                'username' => $this->email,
                'js-available' => 'false',
                'webauthn-available' => 'true',
                'is-brave' => 'false',
                'webauthn-platform-available' => 'true',
                'action' => 'default',
            ]);

        $this->fifth($state);
    }

    public function fifth(string $state)
    {
        $body = $this->http()
            ->withHeaders([
                "Content-Type" => "application/x-www-form-urlencoded",
            ])
            ->post("https://auth0.openai.com/u/login/password?state=$state", [
                'state' => $state,
                'action' => 'default',
                'username' => $this->email,
                'password' => $this->password,
            ])->body();

        dd($body);

        $state = Str::before(explode('=', $matches[0])[1], '"');

        dd($state);
    }

    protected function http()
    {
        return Http::withOptions([
            'cookies' => $this->cookieJar,
        ]);
    }
}
