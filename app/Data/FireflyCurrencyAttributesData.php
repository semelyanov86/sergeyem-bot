<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class FireflyCurrencyAttributesData extends Data
{
    public function __construct(
        public string $name,
        public string $code,
        public ?string $symbol,
        public ?int $decimal_places,
        public bool $native,
        public bool $default,
        public bool $enabled,
    ) {}
}
