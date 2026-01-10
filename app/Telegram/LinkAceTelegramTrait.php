<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Services\LinkAceConnector;
use DefStudio\Telegraph\Enums\ChatActions;
use DefStudio\Telegraph\Keyboard\Keyboard;

trait LinkAceTelegramTrait
{
    public function link(string $text): void
    {
        $this->chat->action(ChatActions::TYPING)->send();
        $linkService = resolve(LinkAceConnector::class);
        $result = $linkService->createLink($text);
        $this->reply("Ссылка была успешно добавлена. Её идентификатор: {$result->id}. Заголовок страницы: {$result->title}");
    }

    public function links(string $text): void
    {
        $msg = 'Ваши последние сохранённые ссылки из всех категорий 👇 ' . PHP_EOL;
        $this->chat->action(ChatActions::TYPING)->send();
        $linkService = resolve(LinkAceConnector::class);
        $perPage = 6;
        if ($text) {
            $perPage = (int) $text;
        }
        $links = $linkService->getAllLinks($perPage);
        foreach ($links as $key => $link) {
            $msg .= $key + 1 . '. <b>' . $link->title . '</b> -> ' . $link->url . PHP_EOL;
        }
        $this->reply($msg);
    }

    public function listlinks(): void
    {
        $msg = 'Выберите список, в котором вы хотите посмотреть ссылки ⌨️';
        $this->chat->action(ChatActions::TYPING)->send();
        $linkService = resolve(LinkAceConnector::class);
        $lists = $linkService->getLists();

        $this->chat->message($msg)->keyboard(function (Keyboard $keyboard) use ($lists) {
            $keyboard = $keyboard->chunk(2);
            foreach ($lists as $list) {
                $keyboard->button($list->name)->action('linksFromList')->param('id', $list->id);
            }

            return $keyboard;
        })->send();
    }

    public function linksFromList(int|string $id): void
    {
        $msg = "Последние ссылки из списка {$id} 👉 " . PHP_EOL;
        $this->chat->action(ChatActions::TYPING)->send();
        $linkService = resolve(LinkAceConnector::class);
        $links = $linkService->getLinksFromList((int) $id);
        foreach ($links as $key => $link) {
            $msg .= $key + 1 . '. ' . $link->title . ' -> ' . $link->url . PHP_EOL;
        }

        $this->chat->message($msg)->send();
    }
}
