<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class FireflyCategoryData extends Data
{
    public function __construct(
        public string $id,
        public string $type,
        public FireflyCategoryAttributeData $attributes,
    ) {}
}
