<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\CreateTickTickTaskData;
use App\Data\TickTickTaskData;
use App\Services\TickTickConnector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TickTickConnectorTest extends TestCase
{
    public function test_get_tasks_returns_array_of_task_data(): void
    {
        Http::fake([
            'ticktick.com/open/v1/project/inbox/data' => Http::response([
                'tasks' => [
                    [
                        'id' => 'task1',
                        'projectId' => 'inbox115259477',
                        'title' => 'Test task',
                        'content' => 'Some description',
                        'startDate' => null,
                        'dueDate' => '2026-03-01',
                        'priority' => 0,
                        'status' => 0,
                    ],
                    [
                        'id' => 'task2',
                        'projectId' => 'inbox115259477',
                        'title' => 'Another task',
                        'content' => null,
                        'startDate' => null,
                        'dueDate' => null,
                        'priority' => 1,
                        'status' => 0,
                    ],
                ],
            ]),
        ]);

        $tasks = resolve(TickTickConnector::class)->getTasks();

        $this->assertCount(2, $tasks);
        $this->assertInstanceOf(TickTickTaskData::class, $tasks[0]);
        $this->assertSame('Test task', $tasks[0]->title);
        $this->assertSame('2026-03-01', $tasks[0]->dueDate);
        $this->assertSame('Another task', $tasks[1]->title);
        $this->assertNull($tasks[1]->dueDate);
    }

    public function test_get_tasks_throws_exception_on_error(): void
    {
        Http::fake([
            'ticktick.com/open/v1/project/inbox/data' => Http::response('Unauthorized', 401),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can not get tasks:');

        resolve(TickTickConnector::class)->getTasks();
    }

    public function test_create_task_returns_task_data(): void
    {
        Http::fake([
            'ticktick.com/open/v1/task' => Http::response([
                'id' => 'new-task-id',
                'projectId' => 'inbox115259477',
                'title' => 'New task',
                'content' => 'Task description',
                'startDate' => null,
                'dueDate' => '2026-04-01',
                'priority' => 0,
                'status' => 0,
            ]),
        ]);

        $taskData = new CreateTickTickTaskData(
            title: 'New task',
            content: 'Task description',
            dueDate: '2026-04-01',
        );

        $result = resolve(TickTickConnector::class)->createTask($taskData);

        $this->assertInstanceOf(TickTickTaskData::class, $result);
        $this->assertSame('new-task-id', $result->id);
        $this->assertSame('New task', $result->title);
        $this->assertSame('Task description', $result->content);
    }

    public function test_create_task_throws_exception_on_error(): void
    {
        Http::fake([
            'ticktick.com/open/v1/task' => Http::response('Bad Request', 400),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can not create task:');

        $taskData = new CreateTickTickTaskData(title: 'New task');

        resolve(TickTickConnector::class)->createTask($taskData);
    }

    public function test_create_task_without_optional_fields(): void
    {
        Http::fake([
            'ticktick.com/open/v1/task' => Http::response([
                'id' => 'task-no-extras',
                'projectId' => 'inbox115259477',
                'title' => 'Simple task',
                'content' => null,
                'startDate' => null,
                'dueDate' => null,
                'priority' => 0,
                'status' => 0,
            ]),
        ]);

        $taskData = new CreateTickTickTaskData(title: 'Simple task');

        $result = resolve(TickTickConnector::class)->createTask($taskData);

        $this->assertSame('Simple task', $result->title);
        $this->assertNull($result->content);
        $this->assertNull($result->dueDate);
        $this->assertSame('inbox115259477', $result->projectId);
    }

    public function test_complete_task_sends_post_request(): void
    {
        Http::fake([
            'ticktick.com/open/v1/project/inbox/task/task123/complete' => Http::response(null, 200),
        ]);

        resolve(TickTickConnector::class)->completeTask('task123');

        Http::assertSent(fn ($request) => $request->url() === 'https://ticktick.com/open/v1/project/inbox/task/task123/complete'
                && $request->method() === 'POST');
    }

    public function test_complete_task_throws_exception_on_error(): void
    {
        Http::fake([
            'ticktick.com/open/v1/project/inbox/task/task123/complete' => Http::response('Not Found', 404),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can not complete task:');

        resolve(TickTickConnector::class)->completeTask('task123');
    }
}
