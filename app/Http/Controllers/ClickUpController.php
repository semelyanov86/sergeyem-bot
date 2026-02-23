<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ClickUpWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class ClickUpController extends Controller
{
    public function webhook(Request $request, ClickUpWebhookService $service): JsonResponse
    {
        if (! config('services.clickup.webhook_enabled')) {
            abort(403, 'ClickUp webhooks are disabled');
        }

        $event = $request->string('event')->value();
        $taskId = $request->string('task_id')->value();

        /** @var array<int, array<string, mixed>> $historyItems */
        $historyItems = $request->input('history_items', []);

        match ($event) {
            'taskCreated' => $service->handleTaskCreated($taskId),
            'taskUpdated' => $service->handleTaskUpdated($taskId),
            'taskCommentPosted' => $service->handleCommentPosted($taskId, $historyItems),
            default => null,
        };

        return response()->json(['success' => 'ok'], 200);
    }
}
