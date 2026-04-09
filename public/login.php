<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Storage\JsonStorage;
use App\Verification\Login;

session_start();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storage = new JsonStorage(__DIR__ . '/../data/account.json');
    $login = new Login($storage);

    try {
        $account = $login->login($_POST['nickname']);

        session_regenerate_id(true);
        $_SESSION['account_id'] = $account->getId();

        header('Location: /index.php');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="output.css">
</head>
<body>
<main class="mx-auto container my-10">
    <a href="index.php"><h1>BANK BY DIKSTY</h1></a>

    <form method="post" class="mt-6">
        <label>Ник</label>
        <input name="nickname" class="border p-2 mb-6 block" required>

        <button class="btn-red">
            Войти
        </button>
    </form>

    <?php if ($error): ?>
        <p class="text-red-600 mt-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</main>
</body>
</html>
