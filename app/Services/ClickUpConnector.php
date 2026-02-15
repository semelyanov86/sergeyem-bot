<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class ClickUpConnector
{
    /**
     * @return array<string, mixed>
     *
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getTask(string $taskId): array
    {
        $response = $this->getRequest()->get('https://api.clickup.com/api/v2/task/' . $taskId);

        if (! $response->ok()) {
            throw new \DomainException('Can not get ClickUp task: ' . $response->body());
        }

        /** @var array<string, mixed> */
        return $response->json();
    }

    protected function getRequest(): PendingRequest
    {
        /** @var string $token */
        $token = config('services.clickup.token');

        return Http::withToken($token, '')->asJson()->acceptJson();
    }
}
