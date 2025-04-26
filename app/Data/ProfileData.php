<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class ProfileData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $main_phone,
        public string $vk,
        public string $youtube,
        public string $facebook,
        public string $address,
        public ?string $telegram_id = null,
        public ?string $telegram_login = null,
    ) {}
}
