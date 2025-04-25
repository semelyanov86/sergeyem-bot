<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TelegraphChat;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Database\Seeder;

final class BotSeeder extends Seeder
{
    public function run(): void
    {
        $bot = TelegraphBot::create([
            'name' => 'sergeyem_bot',
            'token' => config('telegraph.default_token'),
        ]);
        TelegraphChat::create([
            'chat_id' => '303437427',
            'name' => '[private] sergeyem',
            'telegraph_bot_id' => $bot->id,
        ]);
    }
}
