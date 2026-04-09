<?php
namespace App\Verification;

use App\Storage\JsonStorage;
use App\Domain\Account;

class Login
{
    private JsonStorage $storage;

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    public function login(string $nickname): Account
    {
        $nickname = trim($nickname);

        if ($nickname === '') {
            throw new \InvalidArgumentException('Ник не может быть пустым');
        }

        $account = $this->storage->findAccountByOwner($nickname);

        if ($account === null) {
            throw new \RuntimeException('Пользователь не найден');
        }

        return $account;
    }
}
