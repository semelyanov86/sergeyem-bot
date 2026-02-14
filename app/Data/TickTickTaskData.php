<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class TickTickTaskData extends Data
{
    public function __construct(
        public string $id,
        public string $projectId,
        public string $title,
        public ?string $content,
        public ?string $startDate,
        public ?string $dueDate,
        public int $priority,
        public int $status,
    ) {}
}
