<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Services\EasylistConnector;
use DefStudio\Telegraph\Enums\ChatActions;

trait EasylistTelegramTrait
{
    public function buylists(): void
    {
        $msg = 'Списки из сервиса Easylist' . PHP_EOL;
        $this->chat->action(ChatActions::TYPING)->send();
        $linkService = app(EasylistConnector::class);
        $links = $linkService->getLists();
        foreach ($links as $link) {
            $msg .= $link->id . '. <b>' . $link->attributes->name . '</b> (' . $link->attributes->items_count . ')' . PHP_EOL;
        }

        $this->chat->message($msg)->send();
    }
}
