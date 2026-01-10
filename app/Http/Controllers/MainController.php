<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DataBuilderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

final class MainController extends Controller
{
    public const string IP_CACHE = 'LAST_IP';

    public function subscribe(Request $request, DataBuilderService $builderService): \Illuminate\Http\JsonResponse|Response
    {
        $data = $request->all();
        $builderService->validateData($data);
        if (isset($data['mautic.form_on_submit']['submission'])) {
            $submissionData = $data['mautic.form_on_submit']['submission'];
        } elseif (isset($data['mautic.form_on_submit'][0]['submission'])) {
            $submissionData = $data['mautic.form_on_submit'][0]['submission'];
        } else {
            throw new \DomainException('No data!');
        }
        $newIp = $submissionData['results']['ip'];
        if (Cache::has(self::IP_CACHE)) {
            $lastIp = Cache::get(self::IP_CACHE);
            if ($lastIp === $newIp) {
                return response()->json('You already submit the form', 200);
            }
        }

        try {
            $msg = $builderService->generateMessage($submissionData);
            Cache::forget(self::IP_CACHE);
            Cache::put(self::IP_CACHE, $newIp);
            $builderService->sendMessage($msg);

            return response()->noContent();
        } catch (\DomainException $exception) {
            return response()->json($exception->getMessage(), 200);
        }
    }

    public function subscribeSergey(Request $request, DataBuilderService $builderService): \Illuminate\Http\JsonResponse|Response
    {
        $data = $request->all();
        $builderService->validateData($data);
        if (isset($data['mautic.form_on_submit']['submission'])) {
            $submissionData = $data['mautic.form_on_submit']['submission'];
        } elseif (isset($data['mautic.form_on_submit'][0]['submission'])) {
            $submissionData = $data['mautic.form_on_submit'][0]['submission'];
        } else {
            throw new \DomainException('No data!');
        }
        $newIp = $submissionData['results']['ip'];
        if (Cache::has(self::IP_CACHE)) {
            $lastIp = Cache::get(self::IP_CACHE);
            if ($lastIp === $newIp) {
                return response()->json('You already submit the form', 200);
            }
        }
        try {
            $msg = $builderService->generateMessage($submissionData, 'sergeyem');
            Cache::forget(self::IP_CACHE);
            Cache::put(self::IP_CACHE, $newIp);
            $builderService->sendMessage($msg);

            return response()->noContent();
        } catch (\DomainException $exception) {
            return response()->json($exception->getMessage(), 200);
        }
    }

    public function meeting(Request $request, DataBuilderService $builderService): Response
    {
        $token = $request->header('token');
        if ($token !== config('app.api_token')) {
            abort(401, 'Unauthorized');
        }

        $data = $request->all();

        if (! isset($data['action']) || $data['action'] !== 'appointment_save') {
            abort(400, 'Invalid action');
        }

        $payload = $data['payload'] ?? [];

        $msg = '<b>📅 Новая встреча назначена!</b>' . PHP_EOL;
        $msg .= 'ID: ' . $payload['id'] . PHP_EOL;
        $msg .= 'Статус: ' . $payload['status'] . PHP_EOL;
        $msg .= 'Начало: ' . $payload['start_datetime'] . PHP_EOL;
        $msg .= 'Конец: ' . $payload['end_datetime'] . PHP_EOL;
        $msg .= 'Provider ID: ' . $payload['id_users_provider'] . PHP_EOL;
        $msg .= 'Customer ID: ' . $payload['id_users_customer'] . PHP_EOL;

        if (! empty($payload['id_services'])) {
            $msg .= 'Service ID: ' . $payload['id_services'] . PHP_EOL;
        }

        if (! empty($payload['notes'])) {
            $msg .= 'Заметки: ' . $payload['notes'] . PHP_EOL;
        }

        if (! empty($payload['location'])) {
            $msg .= 'Локация: ' . $payload['location'] . PHP_EOL;
        }

        $builderService->sendMessage($msg);

        return response()->noContent();
    }
}
