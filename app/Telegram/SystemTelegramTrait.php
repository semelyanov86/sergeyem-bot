<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Actions\CbrRates;
use App\Actions\EuroRates;
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

    public function rates(): void
    {
        $this->chat->action(ChatActions::TYPING)->send();
        $msg = '<b>Курсы валют:</b>' . PHP_EOL;
        $eurRates = resolve(EuroRates::class)->handle();
        $msg .= '<b>Курс евро на дату ' . $eurRates['date'] . '</b>' . PHP_EOL;
        $msg .= '- USD: ' . $eurRates['rates']['USD'] . PHP_EOL;
        $msg .= '- GBP: ' . $eurRates['rates']['GBP'] . PHP_EOL;
        $msg .= '- AUD: ' . $eurRates['rates']['AUD'] . PHP_EOL;
        $msg .= '- CZK: ' . $eurRates['rates']['CZK'] . PHP_EOL;
        $msg .= '- PLN: ' . $eurRates['rates']['PLN'] . PHP_EOL;
        $msg .= '- TRY: ' . $eurRates['rates']['TRY'] . PHP_EOL;
        $msg .= '- RUB: ' . $eurRates['rates']['RUB'] . PHP_EOL;
        $msg .= PHP_EOL;
        $msg .= PHP_EOL;
        $cbr = resolve(CbrRates::class)->handle();
        $msg .= '<b>Курс валют ЦБРФ на ' . $cbr['date'] . '</b>' . PHP_EOL;
        $msg .= '- USD: ' . $cbr['rates']['USD']->value . PHP_EOL;
        $msg .= '- EUR: ' . $cbr['rates']['EUR']->value . PHP_EOL;
        $msg .= '- PLN: ' . $cbr['rates']['PLN']->value . PHP_EOL;
        $msg .= '- TRY: ' . $cbr['rates']['TRY']->value . PHP_EOL;
        $msg .= '- CNY: ' . $cbr['rates']['CNY']->value . PHP_EOL;
        $this->reply($msg);
    }
}
