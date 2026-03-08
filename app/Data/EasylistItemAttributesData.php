<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class EasylistItemAttributesData extends Data
{
    public function __construct(
        public string $name,
        public string $description,
        public int $quantity,
        public float $price,
        public string $quantity_type,
        public bool $is_starred,
        public bool $is_done,
        public ?int $list_id = null,
    ) {}
}
