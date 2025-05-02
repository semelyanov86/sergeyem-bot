<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Models\TelegraphChat;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Stringable;

/**
 * @property TelegraphChat $chat
 */
final class IncomingHandler extends WebhookHandler
{
    use EasylistTelegramTrait;
    use EasywordsTelegramTrait;
    use FireflyTelegramBotTrait;
    use GeneralInfoTrait;
    use LinkAceTelegramTrait;
    use SystemTelegramTrait;

    #[\Override]
    protected function handleChatMessage(Stringable $text): void
    {
        $method = $this->chat->state->value;

        $this->$method($text, $this->chat->context);
    }
}
