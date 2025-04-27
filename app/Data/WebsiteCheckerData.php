<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class WebsiteCheckerData extends Data
{
    public function __construct(
        public string $website,
        public int $status,
        public bool $keyword_found,
        public float $speed,
    ) {}
}
