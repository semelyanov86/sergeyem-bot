<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class EasylistListAttributeData extends Data
{
    public function __construct(
        public string $name,
        public ?int $folder_id,
        public int $items_count,
    ) {}
}
