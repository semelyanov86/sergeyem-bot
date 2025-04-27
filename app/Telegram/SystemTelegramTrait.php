<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Actions\WebsiteChecker;
use DefStudio\Telegraph\Enums\ChatActions;
use Illuminate\Http\Response;

trait SystemTelegramTrait
{
    public function check(): void
    {
        $this->chat->action(ChatActions::TYPING)->send();
        $msg = '<b>Результат проверки наших сайтов:</b>' . PHP_EOL;
        /** @var array<string, string> $websites */
        $websites = config('services.checker.websites');

        foreach ($websites as $link => $website) {
            $result = WebsiteChecker::run($link, $website);
            $msg .= '- <u>' . $link . '</u>: ';
            if ($result->status === Response::HTTP_OK) {
                $msg .= 'OK ';
            } else {
                $msg .= 'ОШИБКА (статус ' . $result->status . ')';
            }
            $msg .= PHP_EOL;
            if ($result->keyword_found) {
                $msg .= 'Контент корректный';
            } else {
                $msg .= 'Ключевое слово не найдено - ' . $website;
            }
            $msg .= PHP_EOL;
            $msg .= 'Скорость загрузки: ' . $result->speed . ' сек' . PHP_EOL;
        }
        $this->reply($msg);
    }
}
