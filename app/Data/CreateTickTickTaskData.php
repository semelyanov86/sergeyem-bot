<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class CreateTickTickTaskData extends Data
{
    public function __construct(
        public string $title,
        public ?string $content = null,
        public ?string $dueDate = null,
        public string $projectId = 'inbox115259477',
    ) {}
}
