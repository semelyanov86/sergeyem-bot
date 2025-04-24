<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class LinkData extends Data
{
    public function __construct(
        public int $id,
        public string $url,
        public string $title,
        public string $description,
    ) {}
}
