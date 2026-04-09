<?php
// public/register.php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Storage\JsonStorage;
use App\Verification\Register;

session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname'] ?? '');

    if ($nickname === '') {
        $errors[] = 'Ник не может быть пустым';
    } else {
        $storage = new JsonStorage(__DIR__ . '/../data/account.json');
        $register = new Register($storage);

        try {
            $account = $register->register($nickname); // вернёт Account
            // безопасный логин: регенерируем id сессии
            session_regenerate_id(true);
            $_SESSION['account_id'] = $account->getId();

            header('Location: /index.php');
            exit;
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Регистрация</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="output.css" rel="stylesheet">
</head>
<body>
  <main class="mx-auto container my-10">
    <a href="index.php"><h1>BANK BY DIKSTY</h1></a>

    <form method="post" action="register.php" class="mt-6">
      <label for="nickname" class="block">Ник</label>
      <input id="nickname" name="nickname" value="<?= isset($nickname) ? htmlspecialchars($nickname) : '' ?>" required
             class="border p-2 rounded w-full max-w-md" />
      <div class="mt-4">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Зарегистрироваться</button>
      </div>

      <?php if (!empty($errors)): ?>
        <ul class="mt-4 text-red-600">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </form>
  </main>
</body>
</html>
