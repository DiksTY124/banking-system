<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Storage\JsonStorage;

session_start();
date_default_timezone_set('Europe/Moscow');

if (!isset($_SESSION['account_id'])) {
    die('Вы не авторизованы');
}

$storage = new JsonStorage(__DIR__ . '/../data/account.json');

$logged = $storage->loadAccount((int) $_SESSION['account_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientId = (int) ($_POST['recipient_id'] ?? 0);
    $amount = (float) ($_POST['amount'] ?? 0);

    $recipient = $storage->loadAccount($recipientId);
    if (!$recipient) {
        die('Получатель не найден');
    }

    try {
        $logged->transferTo($recipient, $amount);
        $storage->saveAccount($logged);
        $storage->saveAccount($recipient);
        header('Location: index.php');
        exit;
    } catch (\Throwable $e) {
        die('Ошибка: ' . $e->getMessage());
    }
}

$rawAccounts = $storage->loadAll();
$accounts = [];

foreach ($rawAccounts as $id => $accData) {
    $account = $storage->loadAccount((int)$id);

    if ($account && $account->getId() !== $logged->getId()) {
        $accounts[] = $account;
    }
}

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Перевод средств</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="output.css" rel="stylesheet">
</head>
<body>
<main class="mx-auto container my-10">
    <a href="index.php"><h1>BANK BY DIKSTY</h1></a>
    <p>Привет, <strong><?= htmlspecialchars($logged->getOwner()) ?></strong></p>
    <p>Баланс: <strong><?= htmlspecialchars((string)$logged->getBalance()) ?></strong></p>

    <h1>Перевод средств</h1>
        
    <form method="post" class="max-w-sm">
        <div class="mb-4">
            <label for="recipient_id" class="block mb-1">Получатель</label>
            <select name="recipient_id" id="recipient_id" class="w-full border p-2 rounded">
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= htmlspecialchars((string)$account->getId()) ?>">
                        <?= htmlspecialchars($account->getOwner()) ?> (Баланс: <?= htmlspecialchars((string)$account->getBalance()) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="amount" class="block mb-1">Сумма</label>
            <input type="number" step="0.01" name="amount" id="amount" class="w-full border p-2 rounded" required>
        </div>
        <button type="submit" class="btn-primary">Перевести</button>
        <a href="index.php" class="btn-secondary ml-2">Назад</a>
    </form>
</main>
</body>
</html>