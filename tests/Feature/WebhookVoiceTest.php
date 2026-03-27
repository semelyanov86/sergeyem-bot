<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TelegraphChat;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Telegraph as TelegraphBase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;

class WebhookVoiceTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-notify-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.webhook_notify.token' => $this->token]);
        Storage::fake('local');
    }

    public function test_sends_voice_to_telegram(): void
    {
        $this->createChat();
        Telegraph::fake();

        $file = UploadedFile::fake()->create('voice.ogg', 10, 'audio/ogg');

        $this->callVoice($file)->assertOk();

        Telegraph::assertSentFiles(TelegraphBase::ENDPOINT_SEND_VOICE);
    }

    public function test_rejects_invalid_token(): void
    {
        $file = UploadedFile::fake()->create('voice.ogg', 10, 'audio/ogg');

        $this->call('POST', '/webhooks/voice', [], [], ['voice' => $file], [
            'HTTP_AUTHORIZATION' => 'Bearer wrong-token',
        ])->assertForbidden();
    }

    public function test_rejects_missing_token(): void
    {
        $file = UploadedFile::fake()->create('voice.ogg', 10, 'audio/ogg');

        $this->call('POST', '/webhooks/voice', [], [], ['voice' => $file])
            ->assertForbidden();
    }

    public function test_rejects_missing_file(): void
    {
        $this->createChat();

        $this->call('POST', '/webhooks/voice', server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
        ])->assertUnprocessable();
    }

    /** @return TestResponse<Response> */
    private function callVoice(UploadedFile $file): TestResponse
    {
        return $this->call('POST', '/webhooks/voice', [], [], ['voice' => $file], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
        ]);
    }

    private function createChat(): void
    {
        $bot = TelegraphBot::create(['token' => 'test-token', 'name' => 'TestBot']);
        TelegraphChat::forceCreate(['chat_id' => '123456', 'name' => 'Test Chat', 'telegraph_bot_id' => $bot->id]);
    }
}
