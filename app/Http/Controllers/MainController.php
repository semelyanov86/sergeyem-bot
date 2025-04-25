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
}
