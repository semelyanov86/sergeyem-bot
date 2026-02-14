<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Data\CreateTickTickTaskData;
use App\Enums\ChatStateEnum;
use App\Services\TickTickConnector;
use Carbon\Carbon;
use DefStudio\Telegraph\Enums\ChatActions;
use Illuminate\Support\Stringable;

trait TickTickTelegramTrait
{
    public function tasks(): void
    {
        $this->chat->action(ChatActions::TYPING)->send();

        $tasks = resolve(TickTickConnector::class)->getTasks();
        if (empty($tasks)) {
            $this->reply('Список задач пуст');

            return;
        }

        $msg = '<b>Список задач</b>' . PHP_EOL;
        foreach ($tasks as $key => $task) {
            $msg .= $key + 1 . '. ' . $task->title;
            if ($task->dueDate) {
                $msg .= ' (до ' . Carbon::parse($task->dueDate)->format('d.m.Y') . ')';
            }
            $msg .= PHP_EOL;
        }

        $this->reply($msg);
    }

    public function task(): void
    {
        $this->chat->state = ChatStateEnum::ASK_TASK_TITLE;
        $this->chat->context = [];
        $this->chat->save();
        $this->reply('Создаём новую задачу в TickTick.' . PHP_EOL . 'Введите название задачи:');
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    public function askTaskTitle(Stringable $text, ?array $context): void
    {
        if (! $text->value()) {
            $this->reply('Введите корректное название задачи');

            return;
        }

        $this->chat->state = ChatStateEnum::ASK_TASK_CONTENT;
        $context['title'] = $text->value();
        $this->chat->context = $context;
        $this->chat->save();
        $this->reply('Введите описание задачи (или <b>-</b> чтобы пропустить):');
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    public function askTaskContent(Stringable $text, ?array $context): void
    {
        if (! $text->value()) {
            $this->reply('Введите корректное значение');

            return;
        }

        $this->chat->state = ChatStateEnum::ASK_TASK_DUE_DATE;
        $context['content'] = $text->value() === '-' ? null : $text->value();
        // @phpstan-ignore-next-line
        $this->chat->context = $context;
        $this->chat->save();
        $this->reply('Введите дату выполнения в формате <b>YYYY-MM-DD</b> (или <b>-</b> чтобы пропустить):');
    }

    /**
     * @param  array<string, scalar>|null  $context
     */
    public function askTaskDueDate(Stringable $text, ?array $context): void
    {
        $this->chat->action(ChatActions::TYPING)->send();

        if (! $context) {
            $this->reply('Некорректное значение контекста. Начните процесс заново.');

            return;
        }

        if (! $text->value()) {
            $this->reply('Введите корректное значение');

            return;
        }

        $this->chat->state = ChatStateEnum::ACTIVE;
        $this->chat->context = [];
        $this->chat->save();

        $context['dueDate'] = $text->value() === '-' ? null : Carbon::parse($text->value())->format('Y-m-d\TH:i:sO');

        $result = resolve(TickTickConnector::class)->createTask(CreateTickTickTaskData::from($context));
        $this->reply('Задача "<b>' . $result->title . '</b>" успешно создана.');
    }
}
