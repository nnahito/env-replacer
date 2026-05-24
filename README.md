# env-replacer

[English](README.en.md)

GitHub Actions の Secrets で `.env` ファイルの値を置換する Composite Action。

`.env` に存在するキーだけを対象に置換するため、不要な Secrets が混入しない。

## 使い方

```yaml
steps:
  - uses: actions/checkout@v4

  - uses: nnahito/env-replacer@main
    with:
      secrets: ${{ toJSON(secrets) }}
```

`.env.example` からコピーして置換する場合：

```yaml
steps:
  - uses: actions/checkout@v4

  - uses: nnahito/env-replacer@main
    with:
      secrets: ${{ toJSON(secrets) }}
      copy-from-example: 'true'
```
