<?php

declare(strict_types=1);

namespace MiGears\HtmlDigest;

use MiGears\HtmlDigest\Exception\HtmlDigestException;

class HtmlDigest
{
    public const VERSION = '2.0.0';

    public const MODE_CHAR = 'char';
    public const MODE_WORD = 'word';

    // CJK ranges: Han ideographs, extensions, kana, CJK punctuation, compatibility
    private const CJK = '\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}\x{3000}-\x{303F}\x{F900}-\x{FAFF}';

    public static function extract(
        string $html,
        int $length = 200,
        string $ellipsis = '...',
        string $mode = self::MODE_CHAR,
    ): string {
        $text = self::toText($html);

        return match ($mode) {
            self::MODE_CHAR => self::truncateByChars($text, $length, $ellipsis),
            self::MODE_WORD => self::truncateByWords($text, $length, $ellipsis),
            default => throw new HtmlDigestException("Unknown truncation mode: {$mode}"),
        };
    }

    public static function toText(string $html): string
    {
        // Remove script, style, head, noscript elements with their content
        $html = preg_replace(
            '/<(script|style|head|noscript)\b[^>]*>.*?<\/\1>/is',
            ' ',
            $html
        );

        // Block-level tags → newlines
        $html = preg_replace('/<\/(p|div|li|h[1-6]|tr|blockquote|pre|dd|dt)>/i', "\n", $html);
        // <br> → newline
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize each line: trim + collapse whitespace (incl. nbsp / full-width space)
        $lines = explode("\n", $text);
        $lines = array_map(
            static fn (string $line): string => preg_replace('/[\s\x{00A0}\x{3000}]+/u', ' ', trim($line)),
            $lines,
        );
        // Remove blank lines (treat any Unicode whitespace as blank)
        $lines = array_filter(
            $lines,
            static fn (string $line): bool => preg_match('/\S/u', $line) === 1
        );

        // Strip any remaining Unicode whitespace at the boundaries
        return preg_replace('/^[\s\x{00A0}\x{3000}]+|[\s\x{00A0}\x{3000}]+$/u', '', implode("\n", $lines));
    }

    private static function truncateByChars(string $text, int $length, string $ellipsis): string
    {
        if ($length <= 0) {
            throw new HtmlDigestException('Length must be greater than zero.');
        }

        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }

        $ellipsisLen = mb_strlen($ellipsis, 'UTF-8');
        $maxLen = $length - $ellipsisLen;

        if ($maxLen <= 0) {
            return mb_substr($ellipsis, 0, $length, 'UTF-8');
        }

        $truncated = mb_substr($text, 0, $maxLen, 'UTF-8');

        // Try not to cut in the middle of a word: step back to the last whitespace
        $breakpoint = self::lastWhitespacePos($truncated);
        if ($breakpoint !== null) {
            $truncated = mb_substr($text, 0, $breakpoint, 'UTF-8');
        }

        return $truncated . $ellipsis;
    }

    private static function lastWhitespacePos(string $text): ?int
    {
        if (preg_match('/.*[\s\x{00A0}\x{3000}]/u', $text, $m)) {
            return mb_strlen($m[0], 'UTF-8') - 1;
        }

        return null;
    }

    private static function truncateByWords(string $text, int $wordCount, string $ellipsis): string
    {
        if ($wordCount <= 0) {
            throw new HtmlDigestException('Word count must be greater than zero.');
        }

        $words = self::expandCjkWords(
            preg_split('/[\s\x{00A0}\x{3000}]+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY)
        );

        if (\count($words) <= $wordCount) {
            return $text;
        }

        $digest = implode(' ', array_slice($words, 0, $wordCount));
        // CJK tokens join without spaces ("这是一" not "这 是 一")
        $digest = preg_replace(
            '/(?<=[' . self::CJK . ']) (?=[' . self::CJK . '])/u',
            '',
            $digest
        );

        return $digest . $ellipsis;
    }

    // CJK text has no word separators, so treat each CJK character as one token
    private static function expandCjkWords(array $words): array
    {
        $result = [];
        foreach ($words as $word) {
            if (preg_match('/^[' . self::CJK . ']+$/u', $word)) {
                foreach (mb_str_split($word) as $char) {
                    $result[] = $char;
                }
            } else {
                $result[] = $word;
            }
        }

        return $result;
    }
}
