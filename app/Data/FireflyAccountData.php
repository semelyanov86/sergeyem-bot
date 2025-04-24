<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class FireflyAccountData extends Data
{
    public function __construct(
        public string $type,
        public string $id,
        public FireflyAccountAttributesData $attributes,
    ) {}
}
