# GitHub Summarizer (ChatGPT)

Summarize GitHub pull requests using free ChatGPT.

## Requirements

- PHP >= 8.1

## Installation

Install GitHub summarizer via the below command:

```bash
composer global require stevebauman/github-summarizer
```

Next, you will need to find your PHP's "home" directory to be able to store some token files.

Locate it via the below command:

```bash
php -r 'echo $_SERVER["HOME"];'
```

Then create a [GitHub access token](https://github.com/settings/tokens) and store it in your home directory in a file named `.gh_token`.

Finally, login to ChatGPT and copy the entire `session` JSON response:

![Screenshot 2023-02-26 at 4 04 06 PM](https://user-images.githubusercontent.com/6421846/221437445-610ba3a9-a38c-43c5-ba47-786b21243c8c.png)

Once copied, store the contents in your home directory in a file named `.gpt_session`.

> **Important**: This file will need to be updated 2-3 days (after the token expires).

## Usage

```bash
php summarize pr {org}/{repo}
```
