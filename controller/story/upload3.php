<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// .env 読み込み
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// ログイン確認
if (!isset($_SESSION['login'])) {
    header('Location: ../../index.php');
    exit;
}

$username = $_SESSION['login']['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $file = $_FILES['uploaded_file'];
    $filename = basename($file['name']);
    error_log("📦 アップロードファイル: $filename");

    if (pathinfo($filename, PATHINFO_EXTENSION) !== 'php') {
        echo "PHPファイルのみアップロード可能です。";
        exit;
    }

    // 入力パラメータの取得
    $correctjumpTarget = $_POST['correctjumpTarget'] ?? 1;
    $incorrectjumpTarget = $_POST['incorrectjumpTarget'] ?? 1;
    $chapter = $_POST['chapter'] ?? 3;

    $status = null;
    $code = file_get_contents($file['tmp_name']);
    $code = preg_replace('/^\s*<\?php\s*/', '', $code);
    $code = preg_replace('/\s*\?>\s*$/', '', $code);

    try {
        ob_start();
        eval($code);
        ob_end_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        error_log("🔥 Eval error: " . $e->getMessage());
        echo "Eval error: " . $e->getMessage();
        exit;
    }

    error_log("📥 評価結果 status: $status");

    if ($status === "ok") {
        $nextPage = $correctjumpTarget;
        $_SESSION['cleared_program_3'] = true;
        error_log("✅ 正解: 次のページは $correctjumpTarget");

        // progress更新
        try {
            $pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $stmt = $pdo->prepare('SELECT progress FROM login WHERE name = ?');
            $stmt->execute([$username]);
            $currentProgress = $stmt->fetchColumn();

            if ($currentProgress % 5 !== 0) {
                $newProgress = $currentProgress * 5;
                $updateStmt = $pdo->prepare('UPDATE login SET progress = ? WHERE name = ?');
                $updateStmt->execute([$newProgress, $username]);
                error_log("🔁 progress 更新: $currentProgress → $newProgress");
            } else {
                error_log("🟢 progress はすでに5の倍数 ($currentProgress)");
            }

        } catch (PDOException $e) {
            error_log("🛑 DBエラー: " . $e->getMessage());
        }
    } else {
        $nextPage = $incorrectjumpTarget;
        error_log("❌ 不正解: 次のページは $incorrectjumpTarget");
    }

    $_SESSION['nextPageAfterUpload'] = $nextPage;
    $_SESSION['chapterAfterUpload'] = $chapter;

    header("Location: /controller/story/StoryPlayController3.php?fromUpload=1");
    exit;
} else {
    echo "ファイルが選択されていません。";
}
