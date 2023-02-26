# GitHub Summarizer (ChatGPT)

Summarize GitHub pull requests using ChatGPT, for free.

## Index

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## Requirements

- PHP >= 8.1

## Installation

Install GitHub summarizer via the below command:

```bash
composer global require stevebauman/github-summarizer
```

Next, you will need to find your PHP's "home" directory to be able to store some token files.

Locate it by running the below console command:

```bash
php -r 'echo $_SERVER["HOME"];'
```

Then create a [GitHub access token](https://github.com/settings/tokens) and store it in your home directory in a file named `.gh_token`.

Finally, login to ChatGPT and copy the entire `session` JSON response:

![Screenshot 2023-02-26 at 4 04 06 PM](https://user-images.githubusercontent.com/6421846/221437445-610ba3a9-a38c-43c5-ba47-786b21243c8c.png)

Once copied, store the contents in your home directory in a file named `.gpt_session`.

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
