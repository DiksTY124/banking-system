<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Storage\JsonStorage;

session_start();

// проверка авторизации
if (empty($_SESSION['account_id'])) {
    header('Location: /login.php');
    exit;
}

// storage
$storage = new JsonStorage(__DIR__ . '/../data/account.json');

// загружаем аккаунт по id из сессии
$account = $storage->loadAccount((int) $_SESSION['account_id']);
if ($account === null) {
    // аккаунт не найден — разлогинить или показать ошибку
    session_unset();
    session_destroy();
    header('Location: /login.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // безопасно приводим к float
    $amount = (float) ($_POST['amount'] ?? 0);

    try {

        // бизнес-логика: добавить деньги
        $account->withdraw($amount);

        // сохранить изменённый аккаунт
        $storage->saveAccount($account);

        // редирект обратно на главную
        header('Location: /index.php');
        exit;
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Снятие</title>
  <link rel="stylesheet" href="output.css">
</head>
<body>
<main class="mx-auto container my-10">
    <a href="index.php"><h1>BANK BY DIKSTY</h1></a>
    <p>Привет, <strong><?= htmlspecialchars($account->getOwner()) ?></strong></p>
    <p>Баланс: <strong><?= htmlspecialchars((string)$account->getBalance()) ?></strong></p>

    <?php if ($error): ?>
      <p class="text-red-600"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="mt-6">
        <label>Сумма</label>
        <input name="amount" type="number" step="0.01" required class="border p-2 mb-6 block" />

        <button class="btn-red" type="submit">Снять</button>
    </form>
</main>
</body>
</html>