<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\TelegramMessageChunker;
use PHPUnit\Framework\TestCase;

class TelegramMessageChunkerTest extends TestCase
{
    private const int MAX_CHUNK_LENGTH = 3500;

    /** Longest prefix the chunker can prepend, `[999/999]\n`. */
    private const int MAX_PREFIX_LENGTH = 10;

    private const int TELEGRAM_HARD_LIMIT = 4096;

    private TelegramMessageChunker $chunker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chunker = new TelegramMessageChunker();
    }

    public function test_short_report_stays_a_single_unnumbered_chunk(): void
    {
        $chunks = $this->chunker->chunk('Краткий отчёт.');

        $this->assertSame(['Краткий отчёт.'], $chunks);
    }

    public function test_empty_report_produces_no_chunks(): void
    {
        $this->assertSame([], $this->chunker->chunk(''));
    }

    public function test_escapes_html_special_characters(): void
    {
        $chunks = $this->chunker->chunk('Ошибок < 5 & предупреждений > 2');

        $this->assertSame(['Ошибок &lt; 5 &amp; предупреждений &gt; 2'], $chunks);
    }

    public function test_splits_on_paragraph_boundaries(): void
    {
        $first = str_repeat('A', 2000);
        $second = str_repeat('B', 2000);

        $chunks = $this->chunker->chunk($first . "\n\n" . $second);

        $this->assertSame([
            "[1/2]\n" . $first,
            "[2/2]\n" . $second,
        ], $chunks);
    }

    public function test_splits_a_long_paragraph_on_line_boundaries(): void
    {
        $lines = [];

        for ($i = 0; $i < 60; $i++) {
            $lines[] = str_pad('line-' . $i, 100, '.');
        }

        $chunks = $this->chunker->chunk(implode("\n", $lines));

        $this->assertGreaterThan(1, count($chunks));

        foreach ($this->stripPrefixes($chunks) as $chunk) {
            foreach (explode("\n", $chunk) as $line) {
                $this->assertContains($line, $lines, 'A line was cut in half: ' . $line);
            }
        }
    }

    public function test_splits_a_long_line_on_word_boundaries(): void
    {
        $words = [];

        for ($i = 0; $i < 800; $i++) {
            $words[] = str_pad((string) $i, 8, '0', STR_PAD_LEFT);
        }

        $chunks = $this->chunker->chunk(implode(' ', $words));

        $this->assertGreaterThan(1, count($chunks));

        foreach ($this->stripPrefixes($chunks) as $chunk) {
            foreach (explode(' ', $chunk) as $word) {
                $this->assertSame(8, mb_strlen($word), 'A word was cut in half: ' . $word);
            }
        }
    }

    public function test_hard_splits_a_single_oversized_word_without_losing_characters(): void
    {
        $word = str_repeat('Ы', 8000);

        $chunks = $this->stripPrefixes($this->chunker->chunk($word));

        $this->assertSame(3, count($chunks));
        $this->assertSame($word, implode('', $chunks));
    }

    public function test_hard_split_never_bisects_an_html_entity(): void
    {
        // The ampersand escapes to `&amp;` straddling the naive cut at 3500.
        $report = str_repeat('A', self::MAX_CHUNK_LENGTH - 2) . '&' . str_repeat('B', 2000);

        foreach ($this->chunker->chunk($report) as $chunk) {
            $this->assertSame(
                0,
                preg_match('/&(?![a-z]+;)/', $chunk),
                'A chunk contains a truncated HTML entity: ' . mb_substr($chunk, -10),
            );
        }
    }

    public function test_numbers_every_chunk_when_there_is_more_than_one(): void
    {
        $chunks = $this->chunker->chunk(str_repeat("Абзац.\n\n", 2000));

        $total = count($chunks);
        $this->assertGreaterThan(1, $total);

        foreach ($chunks as $index => $chunk) {
            $this->assertStringStartsWith('[' . ($index + 1) . '/' . $total . "]\n", $chunk);
        }
    }

    public function test_no_chunk_exceeds_the_telegram_limit(): void
    {
        foreach ([100, 3499, 3500, 3501, 8500, 40000] as $length) {
            $chunks = $this->chunker->chunk($this->buildReport($length));

            foreach ($chunks as $chunk) {
                $this->assertLessThanOrEqual(
                    self::MAX_CHUNK_LENGTH + self::MAX_PREFIX_LENGTH,
                    mb_strlen($chunk),
                    'Chunk overflowed for a report of ' . $length . ' characters',
                );
                $this->assertLessThanOrEqual(self::TELEGRAM_HARD_LIMIT, mb_strlen($chunk));
            }
        }
    }

    public function test_keeps_every_non_whitespace_character_of_a_realistic_report(): void
    {
        $report = $this->buildReport(40000);

        $chunks = $this->stripPrefixes($this->chunker->chunk($report));

        $this->assertSame(
            preg_replace('/\s+/u', '', htmlspecialchars($report, ENT_NOQUOTES, 'UTF-8')),
            preg_replace('/\s+/u', '', implode('', $chunks)),
        );
    }

    public function test_keeps_multibyte_characters_intact(): void
    {
        $chunks = $this->chunker->chunk(str_repeat('Привет, мир! 🎉 ', 1000));

        foreach ($this->stripPrefixes($chunks) as $chunk) {
            $this->assertTrue(mb_check_encoding($chunk, 'UTF-8'), 'Chunk is not valid UTF-8');
        }
    }

    /**
     * A report shaped like the ones the trainer sends: paragraphs of wrapped lines.
     */
    private function buildReport(int $length): string
    {
        $report = '';
        $paragraph = 0;

        while (mb_strlen($report) < $length) {
            $paragraph++;
            $lines = [];

            for ($line = 1; $line <= 6; $line++) {
                $lines[] = str_pad('Раздел ' . $paragraph . ', строка ' . $line . ' ', 120, 'текст ');
            }

            $report .= implode("\n", $lines) . "\n\n";
        }

        return mb_substr($report, 0, $length);
    }

    /**
     * @param  list<string>  $chunks
     * @return list<string>
     */
    private function stripPrefixes(array $chunks): array
    {
        return array_map(
            static fn (string $chunk): string => (string) preg_replace('/^\[\d+\/\d+\]\n/', '', $chunk),
            $chunks,
        );
    }
}
