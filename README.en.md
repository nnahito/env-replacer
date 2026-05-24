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
```

To copy from `.env.example` before replacing:

```yaml
steps:
  - uses: actions/checkout@v4

  - uses: nnahito/env-replacer@main
    with:
      secrets: ${{ toJSON(secrets) }}
      copy-from-example: 'true'
```
