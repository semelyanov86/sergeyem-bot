<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ClaudeConnector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClaudeConnectorTest extends TestCase
{
    public function test_ask_returns_result_string(): void
    {
        Http::fake([
            'ask.sergeyem.ru/api/claude/raw' => Http::response([
                'result' => 'This is the Claude response.',
            ]),
        ]);

        $result = resolve(ClaudeConnector::class)->ask('What is Laravel?');

        $this->assertSame('This is the Claude response.', $result);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://ask.sergeyem.ru/api/claude/raw') {
                return false;
            }

            /** @var array<int, array{name: string, contents: string}> $data */
            $data = $request->data();

            foreach ($data as $field) {
                if ($field['name'] === 'prompt' && $field['contents'] === 'What is Laravel?') {
                    return true;
                }
            }

            return false;
        });
    }

    public function test_ask_throws_exception_on_error(): void
    {
        Http::fake([
            'ask.sergeyem.ru/api/claude/raw' => Http::response('Unauthorized', 401),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can not get Claude response:');

        resolve(ClaudeConnector::class)->ask('Test prompt');
    }

    public function test_ask_with_file_sends_attachment(): void
    {
        Http::fake([
            'ask.sergeyem.ru/api/claude/raw' => Http::response([
                'result' => 'File analysis result.',
            ]),
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'claude_test_');
        file_put_contents($tempFile, 'test file content');

        try {
            $result = resolve(ClaudeConnector::class)->ask('Analyze this file', $tempFile, 'test.txt');

            $this->assertSame('File analysis result.', $result);

            Http::assertSent(fn ($request) => $request->url() === 'https://ask.sergeyem.ru/api/claude/raw'
                    && $request->hasFile('file'));
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function test_ask_sends_authorization_header(): void
    {
        Http::fake([
            'ask.sergeyem.ru/api/claude/raw' => Http::response([
                'result' => 'Response',
            ]),
        ]);

        config(['services.claude.token' => 'test-token-123']);

        resolve(ClaudeConnector::class)->ask('Hello');

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-token-123'));
    }
}
