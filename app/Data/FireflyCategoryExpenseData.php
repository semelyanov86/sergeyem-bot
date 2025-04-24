<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class FireflyCategoryExpenseData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $difference,
        public float $difference_float,
        public string $currency_id,
        public string $currency_code,
    ) {}
}
