<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// .env読み込み
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// POST & アップロードファイルチェック
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $file = $_FILES['uploaded_file'];
    $filename = basename($file['name']);
    error_log("📦 アップロードファイル: $filename");

    if (pathinfo($filename, PATHINFO_EXTENSION) !== 'php') {
        echo "PHPファイルのみアップロード可能です。";
        exit;
    }

    $correctjumpTarget = $_POST['correctjumpTarget'] ?? 1;
    $incorrectjumpTarget = $_POST['incorrectjumpTarget'] ?? 1;
    $chapter = $_POST['chapter'] ?? 4;

    // 失敗カウント初期化
    if (!isset($_SESSION['failure_count_4'])) {
        $_SESSION['failure_count_4'] = 0;
    }

    $code = file_get_contents($file['tmp_name']);
    $code = preg_replace('/^\s*<\?php\s*/', '', $code);
    $code = preg_replace('/\s*\?>\s*$/', '', $code);

    $output = '';
    $evalSucceeded = true;

    try {
        ob_start();
        eval($code);
        $output = ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        error_log("🔥 Eval error: " . $e->getMessage());
        $evalSucceeded = false;
    }

    error_log("🧪 出力: $output");

    if ($evalSucceeded && strpos($output, "あの日の記憶を取り戻す") !== false) {
        $nextPage = $correctjumpTarget;
        $_SESSION['cleared_program_4'] = true;
        $_SESSION['failure_count_4'] = 0;
        error_log("✅ 成功: メモリ回復検出。次ページ = $correctjumpTarget");
    } else {
        $_SESSION['failure_count_4'] += 1;

        if ($_SESSION['failure_count_4'] >= 5) {
            $nextPage = 320; // BadEnd
            error_log("💀 5回失敗: BadEnd に遷移");
        } else {
            $nextPage = $incorrectjumpTarget;
            error_log("❌ 失敗: 回数 = {$_SESSION['failure_count_4']} → 次ページ = $incorrectjumpTarget");
        }
    }

    $_SESSION['nextPageAfterUpload'] = $nextPage;
    $_SESSION['chapterAfterUpload'] = $chapter;

    header("Location: /controller/story/StoryPlayController4.php?fromUpload=1");
    exit;
} else {
    echo "ファイルが選択されていません。";
}
