<?php

namespace App\Services;

use App\Models\Bank;
use Illuminate\Support\Collection;

class BankService
{
    public function list(): Collection
    {
        return Bank::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}


