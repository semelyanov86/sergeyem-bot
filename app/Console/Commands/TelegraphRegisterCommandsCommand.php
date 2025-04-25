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
            'cancel' => 'Сброс контекста пользователя',
            'link' => 'Сохранение ссылки. Вместе с командой нужно передать ссылку',
            'links' => 'Получить последние сохранённые ссылки',
        ])->send();
    }
}
