<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TelegraphChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WebhookNotifyController extends Controller
{
    private const int MAX_MESSAGE_LENGTH = 4000;

    public function __invoke(Request $request): JsonResponse
    {
        if ($request->bearerToken() !== config('services.webhook_notify.token')) {
            abort(403, 'Invalid token');
        }

        $message = $request->getContent();

        if (empty($message)) {
            return response()->json(['error' => 'Empty message'], 422);
        }

        $chat = TelegraphChat::firstOrFail();

        $chunks = mb_str_split($message, self::MAX_MESSAGE_LENGTH);

        foreach ($chunks as $chunk) {
            $chat->message($chunk)->send();
        }

        return response()->json(['success' => 'ok']);
    }
}
