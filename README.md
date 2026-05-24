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

## 環境ごとに値を切り替える（prod / stage など）

GitHub の [Environments](https://docs.github.com/ja/actions/deployment/targeting-different-environments/using-environments-for-deployment) 機能と組み合わせることで、シェルスクリプト不要で環境ごとの値を切り替えられる。

**Secrets の登録方法**

- **リポジトリ Secrets**（共通値）: `DB_PASSWORD` など全環境で共通のものを登録
- **Environment Secrets**（差分のみ）: 環境ごとに変わる値だけを登録。同名 Secret はリポジトリ側の値を上書きする

```
リポジトリ Secrets（共通）
  DB_PASSWORD=xxxx
  APP_DEBUG=true

prod Environment Secrets
  APP_ENV=production
  APP_DEBUG=false        # リポジトリ側を上書き
  APP_URL=https://example.com

stage Environment Secrets
  APP_ENV=stage
  APP_URL=https://stage.example.com
```

**ワークフローの設定**

`environment:` を1行追加するだけで、対象 Environment の Secrets が自動的に適用される。

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
