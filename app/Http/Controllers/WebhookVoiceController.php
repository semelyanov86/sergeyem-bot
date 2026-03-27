<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TelegraphChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class WebhookVoiceController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($request->bearerToken() !== config('services.webhook_notify.token')) {
            abort(403, 'Invalid token');
        }

        if (! $request->hasFile('voice')) {
            return response()->json(['error' => 'No voice file provided'], 422);
        }

        $file = $request->file('voice');

        $path = (string) $file->store('voices', 'local');

        try {
            $chat = TelegraphChat::firstOrFail();
            $chat->voice(storage_path('app/private/' . $path), $file->getClientOriginalName())->send();
        } finally {
            Storage::disk('local')->delete($path);
        }

        return response()->json(['success' => 'ok']);
    }
}
