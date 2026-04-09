<?php

namespace App\Domain;

class Transaction
{
    private float $amount;
    private string $type;
    private int $accountId;
    private \DateTimeImmutable $createdAt;

    const TYPE_DEPOSIT = 'ДЕПОЗИТ';
    const TYPE_WITHDRAW = 'СНЯТИЕ';
    const TYPE_TRANSFER_OUT = 'ПЕРЕВОД (ИСХОДЯЩИЙ)';
    const TYPE_TRANSFER_IN  = 'ПЕРЕВОД (ВХОДЯЩИЙ)';

    public function __construct(
        float $amount,
        string $type,
        int $accountId,
        ?\DateTimeImmutable $createdAt = null
    ) {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Сумма должна быть положительной');
        }

        if ($accountId <= 0) {
            throw new \InvalidArgumentException('ID должен быть положительным');
        }

        if (
        $type !== self::TYPE_DEPOSIT &&
        $type !== self::TYPE_WITHDRAW &&
        $type !== self::TYPE_TRANSFER_OUT &&
        $type !== self::TYPE_TRANSFER_IN
        ) {
            throw new \InvalidArgumentException('Неверный тип транзакции');
        }

        $this->amount = $amount;
        $this->type = $type;
        $this->accountId = $accountId;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}