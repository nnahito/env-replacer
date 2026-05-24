# env-replacer

[日本語](README.md)

A Composite Action that replaces `.env` values with GitHub Secrets.

Only keys that already exist in `.env` are replaced, so unrelated Secrets are never injected.

## Usage

```yaml
steps:
  - uses: actions/checkout@v4

  - uses: nnahito/env-replacer@main
    with:
      secrets: ${{ toJSON(secrets) }}
      copy-from-example: 'true' # ← optional: copy .env.example to .env before replacing. true or false. Default is false
      env-file: 'subdir/.env'  # ← optional: path to .env if outside the working directory. Default is .env
      env-example: '.env.example' # ← optional: path to .env.example if outside the working directory. Default is .env.example
```

## Switching values per environment (prod / stage, etc.)

By combining with GitHub [Environments](https://docs.github.com/en/actions/deployment/targeting-different-environments/using-environments-for-deployment), you can switch values per environment without any shell scripting.

**How to register Secrets**

- **Repository Secrets** (shared): Register values common to all environments, such as `DB_PASSWORD`
- **Environment Secrets** (overrides only): Register only the values that differ per environment. Environment-level Secrets take precedence over repository-level ones with the same name

```
Repository Secrets (shared)
  DB_PASSWORD=xxxx
  APP_DEBUG=true

prod Environment Secrets
  APP_ENV=production
  APP_DEBUG=false        # overrides repository-level value
  APP_URL=https://example.com

stage Environment Secrets
  APP_ENV=stage
  APP_URL=https://stage.example.com
```

**Workflow configuration**

Just add one `environment:` line — the Secrets for that Environment are automatically applied.

```yaml
jobs:
  deploy:
    environment: ${{ github.event.inputs.target }}  # "prod" or "stage"
    steps:
      - uses: actions/checkout@v4

      - uses: nnahito/env-replacer@main
        with:
          secrets: ${{ toJSON(secrets) }}
```
