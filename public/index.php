<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Storage\JsonStorage;

session_start();

$storage = new JsonStorage(__DIR__ . '/../data/account.json');

$logged = null;
if (!empty($_SESSION['account_id'])) {
    $logged = $storage->loadAccount((int) $_SESSION['account_id']);
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Bank</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="output.css" rel="stylesheet">
</head>
<body>
  <main class="mx-auto container my-10">
        <?php if ($logged): ?>
            <div class='flex justify-between items-center'>
                <div>
                    <h1>BANK BY DIKSTY</h1>
                    <p>Привет, <strong><?= htmlspecialchars($logged->getOwner()) ?></strong></p>
                    <p>Баланс: <strong><?= htmlspecialchars((string)$logged->getBalance()) ?></strong></p>
                    <a href="deposite.php" class="btn-primary">Депнуть!</a>
                    <a href="withdraw.php" class="btn-red">Снять средства!</a>
                    <a href="transfer.php" class="btn-black">Перевести средства!</a>
                </div>
                <div class="flex justify-between gap-3 content-center">
                    <div><a href="logout.php" class="btn-primary">Выйти</a></div>
                    <div>
                        <form method="post" action="/delete.php">
                        <button type="submit" class="btn-red">Удалить аккаунт</button>
                        </form>
                    </div> 
                </div>
            </div>
        <?php else: ?>
            <div class='flex justify-between items-center'>
                <div>
                    <h1>BANK BY DIKSTY</h1>
                </div>
                <div class="flex justify-between gap-3">
                    <a href="login.php" class="btn-black">Войти</a>
                    <a href="register.php" class="btn-primary">Зарегистрироваться</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($logged): ?>

    <h2 class="mt-6 font-bold">История операций</h2>

    <?php $transactions = $logged->getTransactions(); ?>

    <?php if (empty($transactions)): ?>
        <p>Транзакций пока нет.</p>
    <?php else: ?>
        <ul class="mt-4 space-y-2">
            <?php foreach ($transactions as $transaction): ?>
                <li class="border p-2 rounded">
                    <strong><?= htmlspecialchars($transaction->getType()) ?></strong>
                    —
                    <?= htmlspecialchars((string)$transaction->getAmount()) ?> деняг
                     (Дата: <?= htmlspecialchars($transaction->getCreatedAt()->format('Y-m-d H:i:s')) ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

<?php endif; ?>
  </main>
</body>
</html>
