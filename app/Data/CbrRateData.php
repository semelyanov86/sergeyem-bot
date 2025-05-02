<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class CbrRateData extends Data
{
    public function __construct(
        public string $id,
        public string $num_code,
        public string $char_code,
        public int $nominal,
        public string $name,
        public float $value,
        public float $vunit_rate,
    ) {}
}
