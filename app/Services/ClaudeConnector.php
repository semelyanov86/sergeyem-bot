<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class ClaudeConnector
{
    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function ask(string $prompt, ?string $filePath = null, ?string $filename = null): string
    {
        /** @var string $url */
        $url = config('services.claude.url');

        $request = $this->getRequest()->asMultipart();

        if ($filePath !== null) {
            $contents = file_get_contents($filePath);
            if ($contents === false) {
                throw new \DomainException('Can not read file: ' . $filePath);
            }
            $request = $request->attach('file', $contents, $filename);
        }

        $response = $request->post($url . '/api/claude/raw', [
            ['name' => 'prompt', 'contents' => $prompt],
        ]);

        if (! $response->ok()) {
            throw new \DomainException('Can not get Claude response: ' . $response->body());
        }

        /** @var string $result */
        $result = $response->json('result');

        return $result;
    }

    protected function getRequest(): PendingRequest
    {
        /** @var string $token */
        $token = config('services.claude.token');

        return Http::withToken($token)->acceptJson()->timeout(120);
    }
}
