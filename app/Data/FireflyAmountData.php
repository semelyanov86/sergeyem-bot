<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class FireflyAmountData extends Data
{
    public function __construct(
        public string $currency_id,
        public string $currency_code,
        public string $currency_symbol,
        public string $sum,
    ) {}
}
