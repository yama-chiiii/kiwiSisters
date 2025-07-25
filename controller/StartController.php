<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_POST['name'], $_POST['password'])
) {
    header('Location: ../index.php');
    exit;
}

// DB接続
$pdo = new PDO(
    "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
);

// ユーザー名だけで取得（パスワードは後で照合）
$sql = $pdo->prepare('SELECT * FROM login WHERE name=?');
$sql->execute([$_POST['name']]);
$user = $sql->fetch();

if ($user && password_verify($_POST['password'], $user['password'])) {
    // ログイン成功
    $_SESSION['login'] = ['name' => $user['name']];
    header('Location: StartMenu.php');
    exit;
} else {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="ja">
    <head>
    <meta charset="UTF-8">
    <title>ログインエラー</title>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Kiwi+Maru&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="../css/error.css">
    </head>
    <body>
        <h1 class="error-title">IDまたはパスワードが違います</h1>
        <p class="error-message">3秒後にログインページに戻ります...</p>
        <script>
            setTimeout(() => location.href = '../index.php', 3000);
        </script>
    </body>
    </html>
    HTML;
    exit;
}