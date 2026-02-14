<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChatStateEnum;
use DefStudio\Telegraph\Models\TelegraphChat as Chat;

/**
 * @property array<string, scalar> $context
 * @property ChatStateEnum $state
 */
class TelegraphChat extends Chat
{
    protected $fillable = [
        'chat_id',
        'name',
        'state',
        'context',
    ];

    #[\Override]
    protected function casts(): array
    {
        return [
            'state' => ChatStateEnum::class,
            'context' => 'array',
        ];
    }
}
