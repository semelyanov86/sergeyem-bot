<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TelegraphChat;
use Carbon\CarbonInterval;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Telegraph as TelegraphBase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class WebhookNotifyTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-notify-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.webhook_notify.token' => $this->token, 'logging.default' => 'null']);
        Sleep::fake();
    }

    public function test_sends_message_to_telegram(): void
    {
        $this->createChat();
        Telegraph::fake();

        $this->callNotify('Hello from webhook')
            ->assertOk()
            ->assertExactJson(['sent' => 1, 'failed' => 0, 'total' => 1]);

        Telegraph::assertSent('Hello from webhook');
        Sleep::assertNeverSlept();
    }

    public function test_escapes_html_before_sending(): void
    {
        $this->createChat();
        Telegraph::fake();

        $this->callNotify('Ошибок < 5 & предупреждений > 2')
            ->assertOk();

        Telegraph::assertSent('Ошибок &lt; 5 &amp; предупреждений &gt; 2');
    }

    public function test_splits_long_message_into_numbered_chunks_on_paragraph_boundaries(): void
    {
        $this->createChat();
        Telegraph::fake();

        $first = str_repeat('A', 2000);
        $second = str_repeat('B', 2000);

        $this->callNotify($first . "\n\n" . $second)
            ->assertOk()
            ->assertExactJson(['sent' => 2, 'failed' => 0, 'total' => 2]);

        Telegraph::assertSent("[1/2]\n" . $first);
        Telegraph::assertSent("[2/2]\n" . $second);
    }

    public function test_pauses_between_chunks_but_not_before_the_first(): void
    {
        $this->createChat();
        Telegraph::fake();

        $this->callNotify($this->reportWithParagraphs(3))
            ->assertOk()
            ->assertExactJson(['sent' => 3, 'failed' => 0, 'total' => 3]);

        // Asserted by duration rather than by Sleep::assertSequence(), whose eagerly
        // built failure message cannot humanise 500 milliseconds under the `ru` locale.
        Sleep::assertSleptTimes(2);
        Sleep::assertSlept(
            static fn (CarbonInterval $duration): bool => $duration->totalMilliseconds === 1500.0,
            times: 2,
        );
    }

    public function test_retries_after_a_rate_limit_and_reports_success(): void
    {
        $this->createChat();

        Http::fake([
            'api.telegram.org/*' => Http::sequence()
                ->push($this->rateLimited(3), 429)
                ->push($this->accepted(), 200),
        ]);

        $this->callNotify('Hello from webhook')
            ->assertOk()
            ->assertExactJson(['sent' => 1, 'failed' => 0, 'total' => 1]);

        // Telegram's retry_after of 3 seconds, padded by one.
        Sleep::assertSequence([Sleep::for(4)->seconds()]);
    }

    public function test_gives_up_after_three_attempts_and_reports_a_server_error(): void
    {
        $this->createChat();

        Telegraph::fake([
            TelegraphBase::ENDPOINT_MESSAGE => ['ok' => false, 'error_code' => 400, 'description' => 'Bad Request'],
        ]);

        $this->callNotify('Hello from webhook')
            ->assertStatus(500)
            ->assertExactJson(['sent' => 0, 'failed' => 1, 'total' => 1]);

        Sleep::assertSequence([
            Sleep::for(2)->seconds(),
            Sleep::for(5)->seconds(),
        ]);
    }

    public function test_reports_multi_status_when_only_some_chunks_are_delivered(): void
    {
        $this->createChat();

        Http::fake([
            'api.telegram.org/*' => Http::sequence()
                ->push($this->accepted(), 200)
                ->push($this->rejected(), 400)
                ->push($this->rejected(), 400)
                ->push($this->rejected(), 400),
        ]);

        $this->callNotify($this->reportWithParagraphs(2))
            ->assertStatus(207)
            ->assertExactJson(['sent' => 1, 'failed' => 1, 'total' => 2]);
    }

    public function test_logs_a_delivery_summary(): void
    {
        $this->createChat();
        Telegraph::fake();
        $records = $this->captureLogRecords();

        $this->callNotify($this->reportWithParagraphs(2))->assertOk();

        $summaries = $this->recordsWithLevel($records, 'info');

        $this->assertCount(1, $summaries);
        $this->assertSame('Telegram report delivered: sent 2 of 2', $summaries[0]->message);
        $this->assertSame(['sent' => 2, 'failed' => 0, 'total' => 2], $summaries[0]->context);
    }

    public function test_logs_a_warning_per_retry_and_an_error_when_giving_up(): void
    {
        $this->createChat();
        $records = $this->captureLogRecords();

        Telegraph::fake([
            TelegraphBase::ENDPOINT_MESSAGE => ['ok' => false, 'error_code' => 429, 'parameters' => ['retry_after' => 1]],
        ]);

        $this->callNotify('Hello from webhook')->assertStatus(500);

        $warnings = $this->recordsWithLevel($records, 'warning');
        $errors = $this->recordsWithLevel($records, 'error');

        $this->assertCount(2, $warnings);
        $this->assertCount(1, $errors);

        $this->assertSame([
            'chunk' => 1,
            'total' => 1,
            'length' => 18,
            'error_code' => 429,
            'description' => null,
            'retry_after' => 1,
            'attempt' => 1,
        ], $warnings[0]->context);

        $this->assertSame(3, $errors[0]->context['attempt']);
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

    /**
     * Collects log records as they are written, so assertions run on what really
     * reached the logger rather than on a mocked facade.
     *
     * @return \ArrayObject<int, MessageLogged>
     */
    private function captureLogRecords(): \ArrayObject
    {
        /** @var \ArrayObject<int, MessageLogged> $records */
        $records = new \ArrayObject();

        Event::listen(static function (MessageLogged $event) use ($records): void {
            $records[] = $event;
        });

        return $records;
    }

    /**
     * @param  \ArrayObject<int, MessageLogged>  $records
     * @return list<MessageLogged>
     */
    private function recordsWithLevel(\ArrayObject $records, string $level): array
    {
        return array_values(array_filter(
            $records->getArrayCopy(),
            static fn (MessageLogged $record): bool => $record->level === $level,
        ));
    }

    /** @return TestResponse<Response> */
    private function callNotify(string $content): TestResponse
    {
        return $this->call('POST', '/webhooks/notify', server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'text/plain',
        ], content: $content);
    }

    /**
     * Paragraphs of 2000 characters, so each one lands in a chunk of its own.
     */
    private function reportWithParagraphs(int $count): string
    {
        $paragraphs = [];

        for ($index = 0; $index < $count; $index++) {
            $paragraphs[] = str_repeat(chr(ord('A') + $index), 2000);
        }

        return implode("\n\n", $paragraphs);
    }

    /** @return array<string, mixed> */
    private function accepted(): array
    {
        return ['ok' => true, 'result' => ['message_id' => 1]];
    }

    /** @return array<string, mixed> */
    private function rejected(): array
    {
        return ['ok' => false, 'error_code' => 400, 'description' => 'Bad Request'];
    }

    /** @return array<string, mixed> */
    private function rateLimited(int $retryAfter): array
    {
        return [
            'ok' => false,
            'error_code' => 429,
            'description' => 'Too Many Requests',
            'parameters' => ['retry_after' => $retryAfter],
        ];
    }

    private function createChat(): void
    {
        $bot = TelegraphBot::create(['token' => 'test-token', 'name' => 'TestBot']);
        TelegraphChat::forceCreate(['chat_id' => '123456', 'name' => 'Test Chat', 'telegraph_bot_id' => $bot->id]);
    }
}
