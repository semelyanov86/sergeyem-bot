<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class FireflyCategoryAttributeData extends Data
{
    /**
     * @param  FireflyAmountData[]|null  $spent
     * @param  FireflyAmountData[]|null  $earned
     */
    public function __construct(
        public string $name,
        public ?string $notes,
        public ?string $native_currency_id,
        public ?string $native_currency_code,
        public ?string $natice_currency_symbol,
        public ?array $spent,
        public ?array $earned,
    ) {}
}
