<?php

namespace App\Verification;

use App\Storage\JsonStorage;
use App\Domain\Account;

class Register{

    private JsonStorage $storage;

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    public function register(string $nickname): Account
    {
        $nickname = trim($nickname);

        if ($nickname === '') {
            throw new \InvalidArgumentException('Ник не может быть пустым');
        }

        // 1. Загружаем все аккаунты
        $data = $this->storage->loadAll();

        foreach ($data as $accountData) {
            if (($accountData['owner'] ?? '') === $nickname){
                throw new \InvalidArgumentException('Данный ник уже занят!');
            }
        }

        // 2. Генерируем ID
        $id = $this->generateId($data);

        // 3. Создаём аккаунт
        $account = new Account(10000, $id, $nickname);

        // 4. Сохраняем
        $this->storage->saveAccount($account);

        return $account;
    }

    private function generateId(array $data): int{
        if(empty($data)){
            return 1;
        }

        return max(array_map('intval', array_keys($data))) + 1;
    }

}