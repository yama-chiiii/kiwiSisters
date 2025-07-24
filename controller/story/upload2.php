<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    error_log("📦 アップロードされたファイル: $filename");

    if (pathinfo($filename, PATHINFO_EXTENSION) !== 'php') {
        echo "PHPファイルのみアップロード可能です。";
        exit;
    }

    // POSTパラメータ取得
    $correctjumpTarget = $_POST['correctjumpTarget'] ?? 1;
    $incorrectjumpTarget = $_POST['incorrectjumpTarget'] ?? 1;
    $chapter = $_POST['chapter'] ?? 2;

    // ファイルの内容取得・PHPタグ除去
    $code = file_get_contents($file['tmp_name']);
    $code = preg_replace('/^\s*<\?php\s*/', '', $code);
    $code = preg_replace('/\s*\?>\s*$/', '', $code);

    $status = null;

    try {
        ob_start();
        eval($code);
        ob_end_clean(); // 出力を抑制
    } catch (Throwable $e) {
        ob_end_clean();
        error_log("🔥 Eval error: " . $e->getMessage());
        echo "Eval error: " . $e->getMessage();
        exit;
    }

    error_log("✅ 評価結果: status = $status");

    if ($status === "ok") {
        $_SESSION['cleared_program_2'] = true;
        $nextPage = $correctjumpTarget;

        // progress更新処理
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

            if ($currentProgress % 3 !== 0) {
                $newProgress = $currentProgress * 3;
                $update = $pdo->prepare('UPDATE login SET progress = ? WHERE name = ?');
                $update->execute([$newProgress, $username]);
                error_log("🔁 progress 更新: {$currentProgress} → {$newProgress}");
            } else {
                error_log("🎯 progress はすでに3の倍数 ({$currentProgress})");
            }

        } catch (PDOException $e) {
            error_log("🛑 DBエラー: " . $e->getMessage());
        }

    } else {
        $nextPage = $incorrectjumpTarget;
    }

    $_SESSION['nextPageAfterUpload'] = $nextPage;
    $_SESSION['chapterAfterUpload'] = $chapter;

    header("Location: /controller/story/StoryPlayController2.php?fromUpload=1");
    exit;
} else {
    echo "ファイルが選択されていません。";
}
