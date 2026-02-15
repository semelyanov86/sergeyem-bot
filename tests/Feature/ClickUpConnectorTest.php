<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ClickUpConnector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClickUpConnectorTest extends TestCase
{
    public function test_get_task_returns_array(): void
    {
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

    public function test_get_task_throws_exception_on_error(): void
    {
        Http::fake([
            'api.clickup.com/api/v2/task/bad' => Http::response('Not Found', 404),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can not get ClickUp task:');

        resolve(ClickUpConnector::class)->getTask('bad');
    }
}
