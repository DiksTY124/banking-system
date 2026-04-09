<?php

namespace App\Domain;

use App\Exception\InsufficientFundsException;
use App\Domain\Transaction;

class Account
{
    private float $balance;
    private int $id;
    private string $owner;

    private array $transactions = [];

    public function __construct(float $balance, int $id, string $owner) {
        $this->balance = $balance;
        $this->id = $id;
        $this->owner = $owner;
    }

    public function getBalance(): float {
        return $this->balance;
    }

    public function deposit(float $money) : void {
    if ($money <= 0){
        throw new \InvalidArgumentException('Сумма должна быть положительной');
    }

    $this->balance += $money;

    $this->transactions[] = new Transaction($money, Transaction::TYPE_DEPOSIT, $this->id);
    }

    public function withdraw(float $money) : void {
        
    if ($money <= 0) {
        throw new \InvalidArgumentException('Сумма должна быть положительной');
    }

    if ($this->balance < $money) {
        throw new InsufficientFundsException('Недостаточно средств');
    }

    $this->balance -= $money;

    $this->transactions[] = new Transaction($money, Transaction::TYPE_WITHDRAW, $this->id);

    }

    public function transferTo(Account $recipient, float $amount): void
    {
        if ($recipient->getId() === $this->id) {
            throw new \InvalidArgumentException('Нельзя перевести самому себе');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Сумма должна быть положительной');
        }

        if ($this->balance < $amount) {
            throw new \InvalidArgumentException('Недостаточно средств');
        }

        // списание
        $this->balance -= $amount;
        $this->transactions[] = new Transaction(
            $amount,
            Transaction::TYPE_TRANSFER_OUT,
            $this->id
        );

        // зачисление
        $recipient->balance += $amount;
        $recipient->addTransaction(
            new Transaction(
                $amount,
                Transaction::TYPE_TRANSFER_IN,
                $recipient->getId()
            )
        );
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getOwner() : string
    {
        return $this->owner;
    }

    public function addTransaction(Transaction $tx): void
    {
        $this->transactions[] = $tx;
    }

}
