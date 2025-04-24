<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class FireflyBudgetAttributesData extends Data
{
    /**
     * @param  FireflyAmountData[]  $spent
     */
    public function __construct(
        public string $name,
        public int $order,
        public bool $active,
        public ?string $notes,
        public ?string $auto_budget_type,
        public ?string $auto_budget_period,
        public ?string $currency_id,
        public ?string $currency_code,
        public ?string $auto_budget_amount,
        public array $spent,
    ) {}
}
