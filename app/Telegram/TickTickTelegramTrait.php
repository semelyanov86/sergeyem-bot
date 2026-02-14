<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Data\CreateTickTickTaskData;
use App\Enums\ChatStateEnum;
use App\Services\TickTickConnector;
use DefStudio\Telegraph\Enums\ChatActions;
use DefStudio\Telegraph\Keyboard\Keyboard;
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
                $msg .= ' (до ' . \Illuminate\Support\Facades\Date::parse($task->dueDate)->format('d.m.Y') . ')';
            }
            $msg .= PHP_EOL;
        }

        $this->reply($msg);
    }

    public function done(): void
    {
        $this->chat->action(ChatActions::TYPING)->send();

        $tasks = resolve(TickTickConnector::class)->getTasks();
        if (empty($tasks)) {
            $this->reply('Список задач пуст');

            return;
        }

        $this->chat->message('Выберите задачу для завершения:')->keyboard(function (Keyboard $keyboard) use ($tasks) {
            $keyboard = $keyboard->chunk(1);
            foreach ($tasks as $task) {
                $keyboard->button($task->title)->action('completeTask')->param('id', $task->id);
            }

            return $keyboard;
        })->send();
    }

    public function completeTask(string $id): void
    {
        $this->chat->action(ChatActions::TYPING)->send();

        try {
            resolve(TickTickConnector::class)->completeTask($id);
            $this->reply('Задача успешно завершена.');
        } catch (\DomainException $e) {
            $this->reply('Ошибка при завершении задачи: ' . $e->getMessage());
        }
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

        $context['dueDate'] = $text->value() === '-' ? null : \Illuminate\Support\Facades\Date::parse($text->value())->format('Y-m-d\TH:i:sO');

        $result = resolve(TickTickConnector::class)->createTask(CreateTickTickTaskData::from($context));
        $this->reply('Задача "<b>' . $result->title . '</b>" успешно создана.');
    }
}
