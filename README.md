# GitHub Summarizer (ChatGPT)

Summarize GitHub pull requests and commits using ChatGPT, for free. 💸

> **Warning**: This project uses a third-party ChatGPT reverse proxy server using your session token.
> 
> There shouldn't be any adverse effects possible from this, but please consider the risks before using this package.

## Index

- [Requirements](#requirements)
- [Installation](#installation)
- [Authentication Setup](#authentication-setup)
- [Usage](#usage)

## Requirements

- PHP >= 8.1

## Installation

Install GitHub summarizer via the below command:

```bash
composer global require stevebauman/github-summarizer
```

## Authentication Setup

GitHub Summarizer requires two files created in your operating system's `home` directory:

- `.gh_token`
- `.gpt_token`

To find your OS' "home" directory, run the below console command:

```bash
php -r 'echo $_SERVER["HOME"];'
```

### GitHub Access Token

Create a [GitHub access token](https://github.com/settings/tokens).

Copy the token, and paste it in the `.gh_token` file in your home directory.

### ChatGPT Session Token

Login to ChatGPT, inspect the page, click the "Network" tab, and copy the entire `session` JSON response:

> **Tip**: Click the "Fetch/XHR" filter tab, then refresh the page. It will be the first network request sent.

<p align="center">
<img width="750" src="https://user-images.githubusercontent.com/6421846/221437445-610ba3a9-a38c-43c5-ba47-786b21243c8c.png"/>
</p>

Once copied, store the contents in the `.gpt_session` file in your home directory.

> **Important**: This file will need to be updated every 2-3 days (after the token expires).

## Usage

### Setting Your Account Type (Free, Pro, Plus)

```bash
summarize set:account

> "What type of account do you have?:"
  [0] free
  [1] plus
  [2] turbo
```

### Pull Request Summarization

Summarize a GitHub pull request:

```bash
summarize pr {org}/{repo} {--number=} {--state=open} {--style=changelog}
```

List all open PR's for selection:

```bash
summarize pr laravel/framework
```

List all closed PR's for selection:

```bash
summarize pr laravel/framework --state=closed
```

Summarize a specific PR by its number:

```bash
summarize pr laravel/framework 1234
```

Summarize a specific PR by its number responding in a "commit" style:

```bash
summarize pr laravel/framework 1234 --style=commit
```

### Commit Summarization:

Summarize a GitHub commit or range of commits:

```bash
summarize commit {org}/{repo} {sha} {--from=} {--to=} {--style=changelog}
```

List recent commits to summarize:

```bash
summarize commit laravel/framework
```

List recent commits in a specific branch to summarize:

```bash
summarize commit laravel/framework --branch=10.x
```

Summarize a specific commit:

```bash
summarize commit laravel/framework {sha}
```

Summarize a specific commit responding in a "commit" style:

```bash
summarize commit laravel/framework {sha} --style=commit
```

Summarize a range of commits:

```bash
summarize commit laravel/framework --from={sha} --to={sha}
```

Summarize a range of commits (from the given commit to `master`)

```bash
summarize commit laravel/framework --from={sha}
```
