<?php

namespace App\Storage;

use App\Domain\Account;

interface StorageInterface
{
    public function loadAccount(int $id): ?Account;
    public function saveAccount(Account $account): void;
}
