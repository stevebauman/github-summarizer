# GitHub Summarizer (ChatGPT)

Summarize GitHub pull requests using ChatGPT, for free.

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

Login to ChatGPT and copy the entire `session` JSON response.

> **Tip**: Click the "Fetch/XHR" filter tab, then refresh the page. It will be the first network request sent.

![Screenshot 2023-02-26 at 4 04 06 PM](https://user-images.githubusercontent.com/6421846/221437445-610ba3a9-a38c-43c5-ba47-786b21243c8c.png)

Once copied, store the contents in the `.gpt_session` file in your home directory.

> **Important**: This file will need to be updated every 2-3 days (after the token expires).

## Usage

### Pull Request Summarization

Summarize a GitHub pull request:

```bash
summarize pr {org}/{repo} {--number=} {--state=open}
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
summarize pr laravel/framework --number=1234
```
