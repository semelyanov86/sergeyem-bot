<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class FireflySummaryBasicData extends Data
{
    public function __construct(
        public string $key,
        public string $title,
        public string $monetary_value,
        public string $currency_id,
        public string $currency_code,
        public string $currency_symbol,
        public int $currency_decimal_places,
        public string $value_parsed,
        public string $local_icon,
        public string $sub_title,
        public bool $no_available_budgets = false,
    ) {}
}
