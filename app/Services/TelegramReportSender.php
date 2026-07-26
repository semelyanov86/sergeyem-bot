<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\TelegramReportResult;
use App\Models\TelegraphChat;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

/**
 * Delivers a long plain-text report to Telegram one chunk at a time.
 *
 * Telegraph's `send()` performs a plain `Http::post()` and reports Telegram-side
 * failures through the response instead of throwing, so every chunk is checked,
 * retried and logged here — otherwise a rate-limited chunk disappears silently.
 *
 * Sending is synchronous by design: the queue worker is not running, so a queued
 * job would never execute. A full report costs roughly 1.5 seconds per chunk.
 */
final readonly class TelegramReportSender
{
    private const int MAX_ATTEMPTS = 3;

    private const float PAUSE_BETWEEN_CHUNKS_SECONDS = 1.5;

    private const int RATE_LIMIT_ERROR_CODE = 429;

    /** Telegram's own `retry_after` is padded by a second to avoid landing on the edge. */
    private const int RATE_LIMIT_EXTRA_SECONDS = 1;

    /** @var list<int> */
    private const array RETRY_BACKOFF_SECONDS = [2, 5];

    public function __construct(
        private TelegramMessageChunker $chunker,
    ) {}

    public function send(string $report): TelegramReportResult
    {
        $chat = TelegraphChat::firstOrFail();
        $chunks = $this->chunker->chunk($report);
        $total = count($chunks);
        $sent = 0;

        foreach ($chunks as $index => $chunk) {
            if ($index > 0) {
                Sleep::for(self::PAUSE_BETWEEN_CHUNKS_SECONDS)->seconds();
            }

            if ($this->sendChunk($chat, $chunk, $index + 1, $total)) {
                $sent++;
            }
        }

        $result = new TelegramReportResult($sent, $total - $sent, $total);

        Log::info('Telegram report delivered: sent ' . $sent . ' of ' . $total, [
            'sent' => $result->sent,
            'failed' => $result->failed,
            'total' => $result->total,
        ]);

        return $result;
    }

    private function sendChunk(TelegraphChat $chat, string $chunk, int $number, int $total): bool
    {
        $context = [
            'chunk' => $number,
            'total' => $total,
            'length' => mb_strlen($chunk),
        ];

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $failure = $this->attemptSend($chat, $chunk);

            if ($failure === null) {
                return true;
            }

            $attemptContext = [...$context, ...$failure, 'attempt' => $attempt];

            if ($attempt === self::MAX_ATTEMPTS) {
                Log::error('Telegram chunk delivery failed, giving up', $attemptContext);

                return false;
            }

            Log::warning('Telegram chunk delivery failed, retrying', $attemptContext);

            Sleep::for($this->retryDelaySeconds($failure, $attempt))->seconds();
        }

        return false;
    }

    /**
     * Sends one chunk, describing the failure if Telegram did not accept it.
     *
     * @return array{error_code: int|null, description: string|null, retry_after: int|null}|null
     */
    private function attemptSend(TelegraphChat $chat, string $chunk): ?array
    {
        try {
            $response = $chat->message($chunk)->send();
        } catch (ConnectionException $exception) {
            return [
                'error_code' => null,
                'description' => $exception->getMessage(),
                'retry_after' => null,
            ];
        }

        if ($response->telegraphOk()) {
            return null;
        }

        return [
            'error_code' => $this->intOrNull($response->json('error_code')),
            'description' => $this->stringOrNull($response->json('description')),
            'retry_after' => $this->intOrNull($response->json('parameters.retry_after')),
        ];
    }

    /**
     * @param  array{error_code: int|null, description: string|null, retry_after: int|null}  $failure
     */
    private function retryDelaySeconds(array $failure, int $attempt): int
    {
        if ($failure['error_code'] === self::RATE_LIMIT_ERROR_CODE) {
            return ($failure['retry_after'] ?? self::RETRY_BACKOFF_SECONDS[0]) + self::RATE_LIMIT_EXTRA_SECONDS;
        }

        return self::RETRY_BACKOFF_SECONDS[$attempt - 1] ?? self::RETRY_BACKOFF_SECONDS[1];
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
