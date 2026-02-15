<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ClickUpTaskData;
use App\Models\TelegraphChat;
use Illuminate\Support\Facades\Cache;

final readonly class ClickUpWebhookService
{
    private const string ASSIGNEE_EMAIL = 's.emelianov@praxisconcierge.de';

    public function __construct(
        private ClickUpConnector $connector,
    ) {}

    public function handleTaskCreated(string $taskId): void
    {
        $this->handleTaskEvent($taskId, 'created');
    }

    public function handleTaskUpdated(string $taskId): void
    {
        $this->handleTaskEvent($taskId, 'updated');
    }

    /**
     * @param  array<int, array<string, mixed>>  $historyItems
     */
    public function handleCommentPosted(string $taskId, array $historyItems): void
    {
        $task = $this->fetchTask($taskId);

        if ($task === null) {
            return;
        }

        $commentText = '';
        $commenter = '';

        foreach ($historyItems as $item) {
            if (($item['field'] ?? '') === 'comment') {
                /** @var array{text_content?: string} $comment */
                $comment = $item['comment'] ?? [];
                /** @var array{username?: string} $user */
                $user = $item['user'] ?? [];

                $commentText = strval($comment['text_content'] ?? '');
                $commenter = strval($user['username'] ?? '');

                break;
            }
        }

        if ($commentText === '') {
            return;
        }

        $message = $this->buildCommentMessage($task, $commenter, $commentText);
        $this->sendTelegramMessage($message);
    }

    private function handleTaskEvent(string $taskId, string $eventType): void
    {
        $cacheKey = 'clickup_task_notified:' . $taskId;

        if (Cache::has($cacheKey)) {
            return;
        }

        $task = $this->fetchTask($taskId);

        if ($task === null) {
            return;
        }

        $message = $this->buildTaskMessage($task, $eventType);
        $this->sendTelegramMessage($message);

        Cache::put($cacheKey, true, 60);
    }

    private function fetchTask(string $taskId): ?ClickUpTaskData
    {
        $response = $this->connector->getTask($taskId);
        $task = ClickUpTaskData::fromApiResponse($response);

        if (! $task->isAssignedTo(self::ASSIGNEE_EMAIL)) {
            return null;
        }

        return $task;
    }

    private function buildTaskMessage(ClickUpTaskData $task, string $eventType): string
    {
        $eventLabel = $eventType === 'created' ? 'Новый тикет' : 'Тикет обновлён';

        $assigneeNames = collect($task->assignees)
            ->pluck('username')
            ->implode(', ');

        $msg = '<b>' . e($eventLabel) . ': ' . e($task->name) . '</b>' . PHP_EOL;

        if ($task->textContent !== null && $task->textContent !== '') {
            $msg .= PHP_EOL . e($task->textContent) . PHP_EOL;
        }

        $msg .= PHP_EOL . 'Статус: ' . e($task->status);

        if ($task->priority !== null) {
            $msg .= PHP_EOL . 'Приоритет: ' . e($task->priority);
        }

        $msg .= PHP_EOL . 'Ответственные: ' . e($assigneeNames);
        $msg .= PHP_EOL . 'Список: ' . e($task->listName);

        $dateUpdated = $task->formattedDateUpdated();
        if ($dateUpdated !== null) {
            $msg .= PHP_EOL . 'Обновлено: ' . $dateUpdated;
        }

        if ($task->url !== '') {
            $msg .= PHP_EOL . PHP_EOL . '<a href="' . e($task->url) . '">Открыть в ClickUp</a>';
        }

        return $msg;
    }

    private function buildCommentMessage(ClickUpTaskData $task, string $commenter, string $commentText): string
    {
        $msg = '<b>Новый комментарий: ' . e($task->name) . '</b>' . PHP_EOL;
        $msg .= PHP_EOL . e($commenter) . ': ' . e($commentText);

        if ($task->url !== '') {
            $msg .= PHP_EOL . PHP_EOL . '<a href="' . e($task->url) . '">Открыть в ClickUp</a>';
        }

        return $msg;
    }

    private function sendTelegramMessage(string $message): void
    {
        $chat = TelegraphChat::firstOrFail();
        $chat->message($message)->send();
    }
}
