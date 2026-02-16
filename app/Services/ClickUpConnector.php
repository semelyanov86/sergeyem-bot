<?php

declare(strict_types=1);

namespace App\Services;

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
        /** @var string $baseUrl */
        $baseUrl = config('services.clickup.base_url');
        $targetUrl = $baseUrl . '/task/' . $taskId;

        $response = $this->sendRequest('GET', $targetUrl);

        if (! $response->ok()) {
            throw new \DomainException('Can not get ClickUp task: ' . $response->body());
        }

        /** @var array<string, mixed> */
        return $response->json();
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function sendRequest(string $method, string $targetUrl, array $body = []): \Illuminate\Http\Client\Response
    {
        /** @var string $token */
        $token = config('services.clickup.token');

        /** @var string|null $proxyUrl */
        $proxyUrl = config('services.clickup.proxy_url');

        if ($proxyUrl !== null && $proxyUrl !== '') {
            return $this->sendViaProxy($proxyUrl, $method, $targetUrl, $token, $body);
        }

        return $this->sendDirect($method, $targetUrl, $token, $body);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function sendViaProxy(string $proxyUrl, string $method, string $targetUrl, string $token, array $body): \Illuminate\Http\Client\Response
    {
        /** @var string $proxySecret */
        $proxySecret = config('services.clickup.proxy_secret');

        $request = Http::asJson()
            ->acceptJson()
            ->timeout(30)
            ->withHeaders([
                'X-Proxy-Token' => $proxySecret,
                'X-Target-Url' => $targetUrl,
                'X-Target-Method' => $method,
                'X-Target-Token' => $token,
            ]);

        return $request->post($proxyUrl . '/proxy', $body);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function sendDirect(string $method, string $targetUrl, string $token, array $body): \Illuminate\Http\Client\Response
    {
        $request = Http::withToken($token, '')->asJson()->acceptJson();

        return match ($method) {
            'POST' => $request->post($targetUrl, $body),
            'PUT' => $request->put($targetUrl, $body),
            'DELETE' => $request->delete($targetUrl),
            default => $request->get($targetUrl),
        };
    }
}
