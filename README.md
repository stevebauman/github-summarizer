# GitHub Summarizer (ChatGPT)

Summarize GitHub pull requests using ChatGPT, for free.

## Requirements

- PHP >= 8.1

## Installation

Install GitHub summarizer via the below command:

```bash
composer global require stevebauman/github-summarizer
```

Then create a [GitHub access token](https://github.com/settings/tokens) and store it in your user home directory in a file named `.gh_token`.

Finally, login to ChatGPT and copy the entire `session` JSON response:

Once copied, store the contents in your user home directory in a file named `.gpt_session`.

> **Important**: This file will need to be updated 2-3 days.

## Usage

```bash
php summarize pr {org}/{repo}
```
