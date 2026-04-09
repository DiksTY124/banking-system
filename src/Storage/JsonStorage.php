<?php

namespace App\Storage;

use App\Domain\Account;
use App\Domain\Transaction;
use App\Storage\StorageInterface;

class JsonStorage implements StorageInterface
{
    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function loadAccount(int $id): ?Account
    {
        if (!file_exists($this->file)) {
            return null;
        }

        $json = file_get_contents($this->file);
        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data[$id])) {
            return null;
        }

        $accData = $data[$id];

        $account = new Account(
            (float) ($accData['balance'] ?? 0),
            (int) ($accData['id'] ?? $id),
            (string) ($accData['owner'] ?? '')
        );

        if (!empty($accData['transactions']) && is_array($accData['transactions'])) {
            foreach ($accData['transactions'] as $txData) {
                // пытаемся взять строку createdAt, если есть
                $createdAt = null;
                if (!empty($txData['createdAt'])) {
                    try {
                        $createdAt = new \DateTimeImmutable($txData['createdAt']);
                    } catch (\Throwable $e) {
                        // если строка некорректна, используем текущее время
                        $createdAt = new \DateTimeImmutable();
                    }
                }

                $tx = new Transaction(
                    (float) ($txData['amount'] ?? 0),
                    (string) ($txData['type'] ?? Transaction::TYPE_DEPOSIT),
                    (int) ($txData['accountId'] ?? $account->getId()),
                    $createdAt
                );

                $account->addTransaction($tx);
            }
}

        return $account;
    }

    public function loadAll(): array
    {
        if (!file_exists($this->file)) {
            return [];
        }
        $raw = file_get_contents($this->file);
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function findAccountByOwner(string $owner): ?Account
    {
        if (!file_exists($this->file)) {
            return null;
        }

        $raw = file_get_contents($this->file);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            return null;
        }

        foreach ($data as $id => $accData) {
            if (($accData['owner'] ?? '') === $owner) {
                return $this->loadAccount((int)$id);
            }
        }

        return null;
    }

    public function deleteUser(string $owner): bool
    {
        if (!file_exists($this->file)) {
            return false;
        }

        $raw = file_get_contents($this->file);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            return false;
        }

        foreach ($data as $id => $accData) {
            if (($accData['owner'] ?? '') === $owner) {
                unset($data[$id]);

                // сохраняем файл
                $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                file_put_contents($this->file, $json);

                return true;
            }
        }

        return false;
    }


    public function saveAccount(Account $account): void
    {   


        // 1) Получаем текущие данные файла (или пустой массив)
        $data = [];

        if (file_exists($this->file)) {
            $raw = file_get_contents($this->file);
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }

        // 2) Собираем список транзакций в простые массивы
        $transactionsData = [];
        foreach ($account->getTransactions() as $transaction) {
            $transactionsData[] = [
                'amount'    => $transaction->getAmount(),
                'type'      => $transaction->getType(),
                'accountId' => $transaction->getAccountId(),
                'createdAt' => $transaction->getCreatedAt()->format('c'),
            ];
        }

        // 3) Формируем массив данных для этого аккаунта
        $accountData = [
            'id'           => $account->getId(),
            'owner'        => $account->getOwner(),
            'balance'      => $account->getBalance(),
            'transactions' => $transactionsData,
        ];

        // 4) Помещаем/перезаписываем запись по ключу id
        $data[$account->getId()] = $accountData;

        // 5) Сериализуем и записываем в файл (атомарно через tmp + rename)
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $tmp = $this->file . '.tmp';
        file_put_contents($tmp, $json);
        rename($tmp, $this->file);
    }
}
