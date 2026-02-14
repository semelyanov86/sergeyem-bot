<?php

declare(strict_types=1);

namespace App\Console\Commands;

use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Console\Command;

final class TelegraphRegisterCommandsCommand extends Command
{
    protected $signature = 'telegraph:register-commands';

    protected $description = 'Команда по регистрации команд в боте';

    public function handle(): void
    {
        $bot = TelegraphBot::firstOrFail();
        $bot->registerWebhook()->send();
        $bot->registerCommands([
            'start' => 'Регистрация пользователя в боте',
            'help' => 'Выводит список всех доступных комманд',
            'me' => 'Выводит информацию о текущем пользователе',
            'cancel' => 'Сброс контекста пользователя',
            'link' => 'Сохранение ссылки. Вместе с командой нужно передать ссылку',
            'links' => 'Получить последние сохранённые ссылки из Linkace',
            'listlinks' => 'Показать ссылки из Linkace определённого списка',
            'buylists' => 'Выводит списки покупок из Easylist',
            'items' => 'Получить товаров из предоставленного списка. В качестве параметра передайте id',
            'random' => 'Получить список случайных слов для изучения',
            'saveword' => 'Сохранить новое слово для изучения',
            'balance' => 'Получить баланс по счетам в Firefly',
            'accounts' => 'Доступные остатки по счетам',
            'transactions' => 'Проведённые финансовые трансакции за несколько дней',
            'categories' => 'Финансовая статистика по категориям',
            'budgets' => 'Общая статистика по бюджетам',
            'expense' => 'Создание новой расходной транзакции',
            'delete' => 'Удаление транзакции по ИД',
            'rates' => 'Показывает курсы валют',
            'check' => 'Проверяет соединение с сайтами',
            'tasks' => 'Получает список последних задач',
            'task' => 'Позволяет создать задачу в TickTick',
            'done' => 'Отметить задачу как выполненную',
        ])->send();
    }
}
