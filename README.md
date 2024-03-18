<h1 align="center">GitHub Summarizer (ChatGPT)</h1>

<p align="center">Summarize GitHub pull requests and commits using OpenAI</p>

---

## Index

- [Requirements](#requirements)
- [Installation](#installation)
- [Setup](#setup)
- [Usage](#usage)

## Requirements

- PHP >= 8.1

## Installation

Install GitHub summarizer via the below command:

```bash
composer global require stevebauman/github-summarizer
```

## Setup

GitHub Summarizer will prompt you for an OpenAI token the first time you attempt to summarize local
commits, as well as a GitHub token the first time you attempt to summarize a pull request.

## Usage

### Local Git Commit Summarization

Summarize local git commits in the current working directory:

```bash
summarize here {files?} {--all}
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

Summarize a range of commits from the tagged version to `master`:

```bash
summarize commit laravel/framework --from=v10.0.1
```

Summarize a range of commits from the tagged version to another tagged version:

```bash
summarize commit laravel/framework --from=v10.0.1 --to=v10.0.2
```

Summarize a range of commits:

```bash
summarize commit laravel/framework --from={sha} --to={sha}
```

Summarize a range of commits (from the given commit to `master`)

```bash
summarize commit laravel/framework --from={sha}
```
