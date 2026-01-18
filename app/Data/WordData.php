<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class WordData extends Data
{
    public function __construct(
        public int $id,
        public string $type,
        public WordAttributeData $attributes,
    ) {}
}
