<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

final class FireflyAccountAttributesData extends Data
{
    public function __construct(
        public bool $active,
        public ?int $order,
        public string $name,
        public string $type,
        public ?string $account_role,
        public ?int $currency_id,
        public ?string $currency_code,
        public ?string $currency_symbol,
        public ?int $currency_decimal_places,
        public ?int $native_currency_id,
        public ?string $native_currency_code,
        public ?string $native_currency_symbol,
        public ?int $native_currency_decimal_places,
        public string $current_balance,
        public ?string $native_current_balance,
        public Carbon|string $current_balance_date,
        public ?string $notes,
        public ?string $monthly_payment_date,
        public ?string $credit_card_type,
        public ?string $account_number,
        public ?string $iban,
        public ?string $bic,
        public string $virtual_balance,
        public ?string $native_virtual_balance,
        public string $opening_balance,
        public ?string $native_opening_balance,
        public ?string $opening_balance_date,
        public ?string $liability_type,
        public ?string $liability_direction,
        public ?string $interest,
        public ?string $interest_period,
        public ?string $current_debt,
        public bool $include_net_worth,
        public ?float $longitude,
        public ?float $latitude,
        public ?int $zoom_level,
    ) {}
}
