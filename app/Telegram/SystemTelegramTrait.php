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
        $msg = 'Результат проверки наших сайтов:' . PHP_EOL;
        $websites = [
            'https://sergeyem.ru' => 'WEB-разработчик, внедрение CRM. Программист Laravel',
            'https://sergeyem.eu' => 'Ein erfahrener Webentwickler',
            'https://cloud.sergeyem.ru/index.php/login' => '<a href="https://owncloud.com" target="_blank" rel="noreferrer">ownCloud</a>',
            'https://keys.sergeyem.ru:8443/#/login' => '<i class="bwi bwi-spinner bwi-spin bwi-3x tw-text-muted" title="Loading" aria-hidden="true"></i>',
            'https://itvolga.com' => '+7(8352)22-36-06',
            'https://creditcoop.ru' => 'Кредитная кооперация Чувашии: кредитные кооперативы и союзы',
            'https://mautic.sergeyem.ru/s/login' => 'keep me logged in',
            'https://mautic.itvolga.com/s/login' => 'keep me logged in',
            'https://links.sergeyem.ru/login' => 'Forgot your password?',
            'https://easywordsapp.ru/#/login' => '<link rel="stylesheet" href="https://easywordsapp.ru/build/assets',
        ];

        foreach ($websites as $link => $website) {
            $result = WebsiteChecker::run($link, $website);
            $msg .= '- ' . $link . ': ';
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
