<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ProfileData;
use App\Models\TelegraphChat;
use Illuminate\Support\Facades\Http;

final class WebsiteConnector
{
    public function getProfileInfo(): ProfileData
    {
        $response = Http::asJson()->acceptJson()->get('https://sergeyem.ru/api/me');

        if (! $response->ok()) {
            throw new \DomainException('Can not get profile: ' . $response->body());
        }
        $data = ProfileData::from($response->json());
        $chat = TelegraphChat::firstOrFail();
        $data->telegram_id = $chat->chat_id;
        $data->telegram_login = $chat->name;

        return $data;
    }
}
