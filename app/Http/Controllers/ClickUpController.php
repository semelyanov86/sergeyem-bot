<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

final class ClickUpController extends Controller
{
    public function webhook(Request $request): Response
    {
        if (! config('services.clickup.webhook_enabled')) {
            abort(403, 'ClickUp webhooks are disabled');
        }

        Log::info('ClickUp webhook received', [
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
            'raw_content' => $request->getContent(),
        ]);

        return response()->noContent();
    }
}
