<?php

declare(strict_types=1);

namespace MiGears\HtmlDigest;

use MiGears\HtmlDigest\Exception\HtmlDigestException;

class HtmlDigest
{
    public const VERSION = '2.0.0';

    public const MODE_CHAR = 'char';
    public const MODE_WORD = 'word';

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

        // Normalize each line: trim + collapse horizontal whitespace
        $lines = explode("\n", $text);
        $lines = array_map(
            static fn (string $line): string => preg_replace('/[ \t]+/', ' ', trim($line)),
            $lines,
        );
        // Remove blank lines
        $lines = array_filter($lines, static fn (string $line): bool => $line !== '');

        return trim(implode("\n", $lines));
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

        // Try not to cut in the middle of a word
        $lastSpace = mb_strrpos($truncated, ' ');
        $lastNewline = mb_strrpos($truncated, "\n");
        $breakpoint = max($lastSpace ?: -1, $lastNewline ?: -1);

        if ($breakpoint > 0) {
            $truncated = mb_substr($text, 0, $breakpoint, 'UTF-8');
        }

        return rtrim($truncated, " \n\t") . $ellipsis;
    }

    private static function truncateByWords(string $text, int $wordCount, string $ellipsis): string
    {
        if ($wordCount <= 0) {
            throw new HtmlDigestException('Word count must be greater than zero.');
        }

        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        if (\count($words) <= $wordCount) {
            return $text;
        }

        return implode(' ', array_slice($words, 0, $wordCount)) . $ellipsis;
    }
}
