<?php

/**
 * .env に書き込む値を整形する。
 * スペース・#・引用符・バックスラッシュ・$ など
 * シェル上で特殊な意味を持つ文字が含まれる場合はダブルクォートで囲む。
 */
function formatEnvValue(string $rawEnvValue): string
{
    if ($rawEnvValue === '') {
        return '';
    }
    if (preg_match('/[\s#"\'\\\\$`]/', $rawEnvValue)) {
        // 内部のバックスラッシュとダブルクォートをエスケープしてから囲む
        $escapedEnvValue = str_replace(['\\', '"'], ['\\\\', '\\"'], $rawEnvValue);
        return '"' . $escapedEnvValue . '"';
    }
    return $rawEnvValue;
}

function main(): void
{
    // action.yml から環境変数経由で渡される入力値を取得
    $copyFromExample = strtolower(getenv('INPUT_COPY_FROM_EXAMPLE') ?: 'false') === 'true';
    $envFile = getenv('INPUT_ENV_FILE') ?: '.env';
    $envExample = getenv('INPUT_ENV_EXAMPLE') ?: '.env.example';

    // copy-from-example が true の場合、.env.example を .env としてコピーする
    if ($copyFromExample) {
        if (!file_exists($envExample)) {
            fwrite(STDERR, "::error::コピー元ファイルが見つかりません: {$envExample}\n");
            exit(1);
        }
        copy($envExample, $envFile);
        echo "{$envExample} を {$envFile} としてコピーしました\n";
    }

    // .env ファイルの存在確認
    if (!file_exists($envFile)) {
        fwrite(STDERR, "::error::.env ファイルが見つかりません: {$envFile}\n");
        exit(1);
    }

    // GitHub Secrets を JSON 文字列からパース
    $secretsJson = getenv('INPUT_SECRETS') ?: '{}';
    $secretMap = json_decode($secretsJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        fwrite(STDERR, "::error::Secrets の JSON パースに失敗しました: " . json_last_error_msg() . "\n");
        exit(1);
    }

    // .env を1行ずつ読み込む
    $lineList = file($envFile);
    if ($lineList === false) {
        fwrite(STDERR, "::error::.env ファイルの読み込みに失敗しました: {$envFile}\n");
        exit(1);
    }

    $replacedKeyList = [];
    $newLineList = [];

    foreach ($lineList as $line) {
        // KEY= で始まる行だけを対象にする（コメント行・空行はスキップ）
        if (preg_match('/^([A-Za-z_][A-Za-z0-9_]*)=/', $line, $matchList)) {
            $key = $matchList[1];
            // .env のキーが Secrets に存在する場合のみ置換
            // Secrets にあっても .env にないキーは無視される
            if (array_key_exists($key, $secretMap)) {
                $formattedEnvValue = formatEnvValue((string) $secretMap[$key]);
                $newLineList[] = "$key=$formattedEnvValue\n";
                $replacedKeyList[] = $key;
                continue; // 元の行は捨てて次の行へ
            }
        }
        // 置換対象外の行はそのまま保持
        $newLineList[] = $line;
    }

    // 置換結果を .env に書き戻す
    file_put_contents($envFile, implode('', $newLineList));

    $replacedKeyCount = count($replacedKeyList);
    if ($replacedKeyCount > 0) {
        echo "::notice::$replacedKeyCount 件のキーを置換しました\n";
    } else {
        echo "::notice::Secrets と一致するキーが .env に存在しませんでした — ファイルは変更されていません\n";
    }
}

main();
