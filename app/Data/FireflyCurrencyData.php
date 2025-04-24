<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class FireflyCurrencyData extends Data
{
    public function __construct(
        public string $type,
        public string $id,
        public FireflyCurrencyAttributesData $attributes,
    ) {}
}
