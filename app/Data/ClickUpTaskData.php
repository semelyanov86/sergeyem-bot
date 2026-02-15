<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class ClickUpTaskData extends Data
{
    /**
     * @param  array<int, array{id: int, username: string, email: string}>  $assignees
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $textContent,
        public string $status,
        public ?string $priority,
        public array $assignees,
        public string $listName,
        public string $url,
        public ?string $dateUpdated,
    ) {}

    /**
     * @param  array<string, mixed>  $apiResponse
     */
    public static function fromApiResponse(array $apiResponse): self
    {
        /** @var array<int, array{id: int, username: string, email: string}> $assignees */
        $assignees = $apiResponse['assignees'] ?? [];

        /** @var array{status?: string} $statusData */
        $statusData = $apiResponse['status'] ?? [];

        /** @var array{priority?: string} $priorityData */
        $priorityData = $apiResponse['priority'] ?? [];

        /** @var array{name?: string} $listData */
        $listData = $apiResponse['list'] ?? [];

        /** @var string $id */
        $id = $apiResponse['id'] ?? '';
        /** @var string $name */
        $name = $apiResponse['name'] ?? 'Untitled';
        /** @var string $url */
        $url = $apiResponse['url'] ?? '';

        return new self(
            id: $id,
            name: $name,
            textContent: isset($apiResponse['text_content']) ? (string) $apiResponse['text_content'] : null, // @phpstan-ignore cast.string
            status: $statusData['status'] ?? '-',
            priority: $priorityData['priority'] ?? null,
            assignees: $assignees,
            listName: $listData['name'] ?? '-',
            url: $url,
            dateUpdated: isset($apiResponse['date_updated']) ? (string) $apiResponse['date_updated'] : null, // @phpstan-ignore cast.string
        );
    }

    public function isAssignedTo(string $email): bool
    {
        foreach ($this->assignees as $assignee) {
            if ($assignee['email'] === $email) {
                return true;
            }
        }

        return false;
    }

    public function formattedDateUpdated(): ?string
    {
        if ($this->dateUpdated === null) {
            return null;
        }

        $timestamp = intdiv((int) $this->dateUpdated, 1000);

        return date('d.m.Y H:i', $timestamp);
    }
}
