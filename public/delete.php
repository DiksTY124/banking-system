<?php

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

use App\Storage\JsonStorage;

$storage = new JsonStorage(__DIR__ . '/../data/account.json');

if (!isset($_SESSION['account_id'])) {
    header('Location: /login.php');
    exit;
}

$account = $storage->loadAccount($_SESSION['account_id']);

if ($account) {
    $storage->deleteUser($account->getOwner());
    session_destroy();
}

header('Location: /register.php');
exit;
