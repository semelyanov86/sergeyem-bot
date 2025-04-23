<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class EasylistItemData extends Data
{
    public function __construct(
        public string $type,
        public string $id,
        public EasylistItemAttributesData $attributes,
    ) {}
}
