<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Services\EasylistConnector;
use DefStudio\Telegraph\Enums\ChatActions;
use Illuminate\Database\RecordNotFoundException;

trait EasylistTelegramTrait
{
    public function buylists(): void
    {
        $msg = 'Списки из сервиса Easylist' . PHP_EOL;
        $this->chat->action(ChatActions::TYPING)->send();
        $linkService = resolve(EasylistConnector::class);
        $links = $linkService->getLists();
        foreach ($links as $link) {
            $msg .= $link->id . '. <b>' . $link->attributes->name . '</b> (' . $link->attributes->items_count . ')' . PHP_EOL;
        }

        $this->chat->message($msg)->send();
    }

    public function items(string $id): void
    {
        if (! $id) {
            $this->reply('Для получения списка продуктов, нужно передать идентификатор списка. Пожалуйста, введите например /items 3');

            return;
        }
        try {
            $items = resolve(EasylistConnector::class)->getListItems((int) $id);
        } catch (RecordNotFoundException) {
            $this->reply('Список, который вы предоставили не верный. Введите корректный идентификатор');

            return;
        }
        if (count($items) < 1) {
            $this->reply('Не найдено каких-либо элементов');

            return;
        }
        $msg = 'Товары из выбранного вами списка:' . PHP_EOL;
        foreach ($items as $key => $item) {
            $postfix = ' ';
            if ($item->attributes->quantity > 0) {
                $postfix = ' (' . $item->attributes->quantity . ' ' . $item->attributes->quantity_type . ')';
            }
            $msg .= $key + 1 . '. <b>' . $item->attributes->name . '</b>' . $postfix . PHP_EOL . $item->attributes->description . PHP_EOL;
        }
        $this->reply($msg);
    }
}
