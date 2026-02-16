<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TelegraphChat;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClickUpWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.clickup.proxy_url' => null]);
    }

    public function test_task_created_sends_telegram_message(): void
    {
        $this->createChat();
        Telegraph::fake();

        Http::fake([
            'api.clickup.com/api/v2/task/abc123' => Http::response($this->clickUpTaskResponse()),
        ]);

        $this->postJson('/webhooks/clickup', [
            'event' => 'taskCreated',
            'task_id' => 'abc123',
            'history_items' => [],
        ])->assertNoContent();

        Telegraph::assertSent('Новый тикет', exact: false);
    }

    public function test_task_updated_sends_telegram_message(): void
    {
        $this->createChat();
        Telegraph::fake();

        Http::fake([
            'api.clickup.com/api/v2/task/abc123' => Http::response($this->clickUpTaskResponse()),
        ]);

        $this->postJson('/webhooks/clickup', [
            'event' => 'taskUpdated',
            'task_id' => 'abc123',
            'history_items' => [],
        ])->assertNoContent();

        Telegraph::assertSent('Тикет обновлён', exact: false);
    }

    public function test_task_updated_debounce_prevents_duplicate_message(): void
    {
        $this->createChat();
        Telegraph::fake();

        Http::fake([
            'api.clickup.com/api/v2/task/abc123' => Http::response($this->clickUpTaskResponse()),
        ]);

        $this->postJson('/webhooks/clickup', [
            'event' => 'taskUpdated',
            'task_id' => 'abc123',
            'history_items' => [],
        ])->assertNoContent();

        Telegraph::assertSent('Тикет обновлён', exact: false);

        // Second call should be debounced
        Telegraph::fake();

        $this->postJson('/webhooks/clickup', [
            'event' => 'taskUpdated',
            'task_id' => 'abc123',
            'history_items' => [],
        ])->assertNoContent();

        Telegraph::assertNothingSent();
    }

    public function test_task_event_skips_when_not_assigned_to_me(): void
    {
        $this->createChat();
        Telegraph::fake();

        Http::fake([
            'api.clickup.com/api/v2/task/abc123' => Http::response($this->clickUpTaskResponse(assignedToMe: false)),
        ]);

        $this->postJson('/webhooks/clickup', [
            'event' => 'taskCreated',
            'task_id' => 'abc123',
            'history_items' => [],
        ])->assertNoContent();

        Telegraph::assertNothingSent();
    }

    public function test_comment_posted_sends_telegram_message(): void
    {
        $this->createChat();
        Telegraph::fake();

        Http::fake([
            'api.clickup.com/api/v2/task/abc123' => Http::response($this->clickUpTaskResponse()),
        ]);

        $this->postJson('/webhooks/clickup', [
            'event' => 'taskCommentPosted',
            'task_id' => 'abc123',
            'history_items' => [
                [
                    'field' => 'comment',
                    'user' => ['id' => 100734434, 'username' => 'Sergei Emelianov'],
                    'comment' => [
                        'text_content' => 'This is a test comment',
                    ],
                ],
            ],
        ])->assertNoContent();

        Telegraph::assertSent('Новый комментарий', exact: false);
    }

    public function test_comment_skips_when_not_assigned_to_me(): void
    {
        $this->createChat();
        Telegraph::fake();

        Http::fake([
            'api.clickup.com/api/v2/task/abc123' => Http::response($this->clickUpTaskResponse(assignedToMe: false)),
        ]);

        $this->postJson('/webhooks/clickup', [
            'event' => 'taskCommentPosted',
            'task_id' => 'abc123',
            'history_items' => [
                [
                    'field' => 'comment',
                    'user' => ['id' => 999, 'username' => 'Other User'],
                    'comment' => [
                        'text_content' => 'Some comment',
                    ],
                ],
            ],
        ])->assertNoContent();

        Telegraph::assertNothingSent();
    }

    public function test_disabled_webhook_returns_403(): void
    {
        config(['services.clickup.webhook_enabled' => false]);

        $this->postJson('/webhooks/clickup', [
            'event' => 'taskCreated',
            'task_id' => 'abc123',
        ])->assertForbidden();
    }

    public function test_unknown_event_does_nothing(): void
    {
        Telegraph::fake();

        $this->postJson('/webhooks/clickup', [
            'event' => 'unknownEvent',
            'task_id' => 'abc123',
        ])->assertNoContent();

        Telegraph::assertNothingSent();
    }

    public function test_task_message_includes_date_updated(): void
    {
        $this->createChat();
        Telegraph::fake();

        Http::fake([
            'api.clickup.com/api/v2/task/abc123' => Http::response(array_merge(
                $this->clickUpTaskResponse(),
                ['date_updated' => '1770900579157'],
            )),
        ]);

        $this->postJson('/webhooks/clickup', [
            'event' => 'taskCreated',
            'task_id' => 'abc123',
            'history_items' => [],
        ])->assertNoContent();

        Telegraph::assertSent('Обновлено:', exact: false);
    }

    private function createChat(): void
    {
        $bot = TelegraphBot::create(['token' => 'test-token', 'name' => 'TestBot']);
        TelegraphChat::forceCreate(['chat_id' => '123456', 'name' => 'Test Chat', 'telegraph_bot_id' => $bot->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function clickUpTaskResponse(string $taskId = 'abc123', bool $assignedToMe = true): array
    {
        $assignees = $assignedToMe
            ? [['id' => 100734434, 'username' => 'Sergei Emelianov', 'email' => 's.emelianov@praxisconcierge.de']]
            : [['id' => 999, 'username' => 'Other User', 'email' => 'other@example.com']];

        return [
            'id' => $taskId,
            'name' => 'Test ClickUp Task',
            'text_content' => 'Task description text.',
            'status' => ['status' => 'Open'],
            'priority' => ['priority' => 'urgent'],
            'assignees' => $assignees,
            'list' => ['name' => 'Tickets'],
            'url' => 'https://app.clickup.com/t/' . $taskId,
        ];
    }
}
