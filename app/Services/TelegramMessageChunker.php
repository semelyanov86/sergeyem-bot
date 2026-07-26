<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Splits a plain-text report into Telegram-sized messages along text boundaries.
 *
 * Telegram caps a message at 4096 characters and this class stays at 3500 to leave
 * room for the `[i/N]` prefix and for multi-byte characters that Telegram counts as
 * two UTF-16 units (mostly emoji) while PHP counts them as one.
 *
 * The text is HTML-escaped up front because `config('telegraph.default_parse_mode')`
 * is HTML, so an unescaped `<` or `&` in the report would make Telegram reject the
 * whole message with a 400 "can't parse entities".
 */
final class TelegramMessageChunker
{
    private const int MAX_CHUNK_LENGTH = 3500;

    /** Length of the longest entity htmlspecialchars() can emit: `&amp;`. */
    private const int MAX_ENTITY_LENGTH = 5;

    private const string PARAGRAPH_SEPARATOR = "\n\n";

    private const string LINE_SEPARATOR = "\n";

    private const string WORD_SEPARATOR = ' ';

    /**
     * @return list<string>
     */
    public function chunk(string $report): array
    {
        $escaped = htmlspecialchars($report, ENT_NOQUOTES, 'UTF-8');

        $chunks = array_values(array_filter(
            $this->splitBy($escaped, [self::PARAGRAPH_SEPARATOR, self::LINE_SEPARATOR, self::WORD_SEPARATOR]),
            static fn (string $chunk): bool => $chunk !== '',
        ));

        return $this->numberChunks($chunks);
    }

    /**
     * Packs pieces separated by `$separators[0]` into chunks, falling back to the next
     * separator for any single piece that does not fit on its own.
     *
     * @param  list<non-empty-string>  $separators
     * @return list<string>
     */
    private function splitBy(string $text, array $separators): array
    {
        if (mb_strlen($text) <= self::MAX_CHUNK_LENGTH) {
            return [$text];
        }

        $separator = array_shift($separators);

        if ($separator === null) {
            return $this->splitHard($text);
        }

        $chunks = [];
        $current = null;

        foreach (explode($separator, $text) as $piece) {
            $candidate = $current === null ? $piece : $current . $separator . $piece;

            if (mb_strlen($candidate) <= self::MAX_CHUNK_LENGTH) {
                $current = $candidate;

                continue;
            }

            if ($current !== null) {
                $chunks[] = $current;
                $current = null;
            }

            if (mb_strlen($piece) <= self::MAX_CHUNK_LENGTH) {
                $current = $piece;

                continue;
            }

            foreach ($this->splitBy($piece, $separators) as $subChunk) {
                $chunks[] = $subChunk;
            }
        }

        if ($current !== null) {
            $chunks[] = $current;
        }

        return $chunks;
    }

    /**
     * Last resort for text with no usable boundary left: cut at a fixed length.
     *
     * @return list<string>
     */
    private function splitHard(string $text): array
    {
        $chunks = [];

        while (mb_strlen($text) > self::MAX_CHUNK_LENGTH) {
            $length = $this->safeCutLength($text);

            $chunks[] = mb_substr($text, 0, $length);
            $text = mb_substr($text, $length);
        }

        if ($text !== '') {
            $chunks[] = $text;
        }

        return $chunks;
    }

    /**
     * Pulls the cut back before an HTML entity that the fixed-length cut would bisect,
     * because a half-written `&amp;` is a parse error for Telegram just like a raw `&`.
     */
    private function safeCutLength(string $text): int
    {
        $lookbehind = mb_substr($text, self::MAX_CHUNK_LENGTH - self::MAX_ENTITY_LENGTH, self::MAX_ENTITY_LENGTH);
        $ampersand = mb_strrpos($lookbehind, '&');

        if ($ampersand === false || str_contains(mb_substr($lookbehind, $ampersand), ';')) {
            return self::MAX_CHUNK_LENGTH;
        }

        $shortened = self::MAX_CHUNK_LENGTH - (self::MAX_ENTITY_LENGTH - $ampersand);

        // Never return 0, or splitHard() would loop forever on an unshrinking string.
        return $shortened > 0 ? $shortened : self::MAX_CHUNK_LENGTH;
    }

    /**
     * @param  list<string>  $chunks
     * @return list<string>
     */
    private function numberChunks(array $chunks): array
    {
        $total = count($chunks);

        if ($total <= 1) {
            return $chunks;
        }

        $numbered = [];

        foreach ($chunks as $index => $chunk) {
            $numbered[] = '[' . ($index + 1) . '/' . $total . ']' . self::LINE_SEPARATOR . $chunk;
        }

        return $numbered;
    }
}
