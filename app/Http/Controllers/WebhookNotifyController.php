<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TelegramReportSender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WebhookNotifyController extends Controller
{
    public function __invoke(Request $request, TelegramReportSender $sender): JsonResponse
    {
        if ($request->bearerToken() !== config('services.webhook_notify.token')) {
            abort(403, 'Invalid token');
        }

        $report = $request->getContent();

        if (empty($report)) {
            return response()->json(['error' => 'Empty message'], 422);
        }

        $result = $sender->send($report);

        return response()->json($result->toArray(), $result->httpStatus());
    }
}
