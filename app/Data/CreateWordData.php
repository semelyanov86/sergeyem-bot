<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class CreateWordData extends Data
{
    public function __construct(
        public string $original,
        public string $translated,
        public string $language = 'DE',
        public int $views = 0,
    ) {}
}
