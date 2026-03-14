<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TelegraphChat;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookNotifyTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-notify-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.webhook_notify.token' => $this->token]);
    }

    public function test_sends_message_to_telegram(): void
    {
        $this->createChat();
        Telegraph::fake();

        $this->callNotify('Hello from webhook')
            ->assertOk();

        Telegraph::assertSent('Hello from webhook');
    }

    public function test_splits_long_message_into_chunks(): void
    {
        $this->createChat();
        Telegraph::fake();

        $message = str_repeat('A', 8500);

        $this->callNotify($message)
            ->assertOk();

        Telegraph::assertSent(str_repeat('A', 4000));
        Telegraph::assertSent(str_repeat('A', 500));
    }

    public function test_rejects_invalid_token(): void
    {
        $this->call('POST', '/webhooks/notify', server: [
            'HTTP_AUTHORIZATION' => 'Bearer wrong-token',
            'CONTENT_TYPE' => 'text/plain',
        ], content: 'Hello')
            ->assertForbidden();
    }

    public function test_rejects_missing_token(): void
    {
        $this->call('POST', '/webhooks/notify', server: [
            'CONTENT_TYPE' => 'text/plain',
        ], content: 'Hello')
            ->assertForbidden();
    }

    public function test_rejects_empty_message(): void
    {
        $this->createChat();

        $this->callNotify('')
            ->assertUnprocessable();
    }

    /** @return \Illuminate\Testing\TestResponse<\Illuminate\Http\Response> */
    private function callNotify(string $content): \Illuminate\Testing\TestResponse
    {
        return $this->call('POST', '/webhooks/notify', server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'text/plain',
        ], content: $content);
    }

    private function createChat(): void
    {
        $bot = TelegraphBot::create(['token' => 'test-token', 'name' => 'TestBot']);
        TelegraphChat::forceCreate(['chat_id' => '123456', 'name' => 'Test Chat', 'telegraph_bot_id' => $bot->id]);
    }
}
