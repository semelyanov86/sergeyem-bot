<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ClickUpConnector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClickUpConnectorTest extends TestCase
{
    public function test_get_task_returns_array_direct(): void
    {
        config(['services.clickup.proxy_url' => null]);

        Http::fake([
            'api.clickup.com/api/v2/task/abc123' => Http::response([
                'id' => 'abc123',
                'name' => 'Test Task',
                'text_content' => 'Description here',
                'status' => ['status' => 'Open'],
                'assignees' => [],
                'url' => 'https://app.clickup.com/t/abc123',
            ]),
        ]);

        $result = resolve(ClickUpConnector::class)->getTask('abc123');

        $this->assertSame('abc123', $result['id']);
        $this->assertSame('Test Task', $result['name']);
    }

    public function test_get_task_throws_exception_on_error_direct(): void
    {
        config(['services.clickup.proxy_url' => null]);

        Http::fake([
            'api.clickup.com/api/v2/task/bad' => Http::response('Not Found', 404),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can not get ClickUp task:');

        resolve(ClickUpConnector::class)->getTask('bad');
    }

    public function test_get_task_via_proxy(): void
    {
        config([
            'services.clickup.proxy_url' => 'https://proxy.example.com',
            'services.clickup.proxy_secret' => 'test-proxy-secret',
            'services.clickup.token' => 'pk_test_token',
        ]);

        Http::fake([
            'proxy.example.com/proxy' => Http::response([
                'id' => 'abc123',
                'name' => 'Proxied Task',
                'status' => ['status' => 'Open'],
                'assignees' => [],
                'url' => 'https://app.clickup.com/t/abc123',
            ]),
        ]);

        $result = resolve(ClickUpConnector::class)->getTask('abc123');

        $this->assertSame('abc123', $result['id']);
        $this->assertSame('Proxied Task', $result['name']);

        Http::assertSent(fn (\Illuminate\Http\Client\Request $request) => $request->url() === 'https://proxy.example.com/proxy'
                && $request->method() === 'POST'
                && $request->header('X-Proxy-Token')[0] === 'test-proxy-secret'
                && $request->header('X-Target-Url')[0] === 'https://api.clickup.com/api/v2/task/abc123'
                && $request->header('X-Target-Method')[0] === 'GET'
                && $request->header('X-Target-Token')[0] === 'pk_test_token');
    }

    public function test_get_task_via_proxy_throws_exception_on_error(): void
    {
        config([
            'services.clickup.proxy_url' => 'https://proxy.example.com',
            'services.clickup.proxy_secret' => 'secret',
        ]);

        Http::fake([
            'proxy.example.com/proxy' => Http::response('Not Found', 404),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can not get ClickUp task:');

        resolve(ClickUpConnector::class)->getTask('bad');
    }
}
