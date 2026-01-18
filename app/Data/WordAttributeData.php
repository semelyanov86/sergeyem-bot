<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class WordAttributeData extends Data
{
    public function __construct(
        public string $original,
        public string $translated,
        public bool $starred,
        public int $views,
        public int $user_id,
    ) {}
}
