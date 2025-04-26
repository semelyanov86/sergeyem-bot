<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Models\TelegraphChat;
use DefStudio\Telegraph\Handlers\WebhookHandler;

/**
 * @property TelegraphChat $chat
 */
final class IncomingHandler extends WebhookHandler
{
    use EasylistTelegramTrait;
    use GeneralInfoTrait;
    use LinkAceTelegramTrait;
}
