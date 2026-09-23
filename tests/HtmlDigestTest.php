<?php

declare(strict_types=1);

namespace MiGears\HtmlDigest\Tests;

use PHPUnit\Framework\TestCase;
use MiGears\HtmlDigest\HtmlDigest;
use MiGears\HtmlDigest\Exception\HtmlDigestException;

final class HtmlDigestTest extends TestCase
{
    // ==================== toText ====================

    public function testToTextStripsBasicTags(): void
    {
        $html = '<p>Hello <strong>World</strong></p>';
        $this->assertSame('Hello World', HtmlDigest::toText($html));
    }

    public function testToTextEmptyString(): void
    {
        $this->assertSame('', HtmlDigest::toText(''));
    }

    public function testToTextNoTags(): void
    {
        $this->assertSame('Just plain text', HtmlDigest::toText('Just plain text'));
    }

    public function testToTextBlockElementsAddNewlines(): void
    {
        $html = '<p>First</p><p>Second</p>';
        $result = HtmlDigest::toText($html);
        $this->assertSame("First\nSecond", $result);
    }

    public function testToTextDivAndOtherBlocks(): void
    {
        $html = '<div>One</div><li>Two</li><h1>Three</h1><blockquote>Four</blockquote>';
        $result = HtmlDigest::toText($html);
        $this->assertSame("One\nTwo\nThree\nFour", $result);
    }

    public function testToTextBrAddsNewline(): void
    {
        $html = 'Line 1<br>Line 2<br/>Line 3';
        $result = HtmlDigest::toText($html);
        $this->assertSame("Line 1\nLine 2\nLine 3", $result);
    }

    public function testToTextDecodesHtmlEntities(): void
    {
        $html = '<p>Tom &amp; Jerry &lt;script&gt;</p>';
        $this->assertSame('Tom & Jerry <script>', HtmlDigest::toText($html));
    }

    public function testToTextCollapsesWhitespaceWithinLines(): void
    {
        $html = '<p>hello    world   foo</p>';
        $this->assertSame('hello world foo', HtmlDigest::toText($html));
    }

    public function testToTextRemovesBlankLines(): void
    {
        $html = "<p>First</p><p></p><p>&nbsp;</p><p>Last</p>";
        $result = HtmlDigest::toText($html);
        // &nbsp; is decoded to non-breaking space, which is not a regular space
        // So the third paragraph won't be empty after trim
        // Let's test with actual empty paragraphs
        $html2 = '<p>First</p><p></p><p>Last</p>';
        $result2 = HtmlDigest::toText($html2);
        $this->assertSame("First\nLast", $result2);
    }

    public function testToTextNestedTags(): void
    {
        $html = '<div><p><span>Deep</span> text</p></div>';
        $this->assertSame('Deep text', HtmlDigest::toText($html));
    }

    public function testToTextMultibyteCharacters(): void
    {
        $html = '<p>你好，世界！Hello 日本語</p>';
        $this->assertSame('你好，世界！Hello 日本語', HtmlDigest::toText($html));
    }

    public function testToTextTrimsResult(): void
    {
        $html = '   <p>hello</p>   ';
        $this->assertSame('hello', HtmlDigest::toText($html));
    }

    // ==================== extract - MODE_CHAR ====================

    public function testExtractCharModeShorterThanLimit(): void
    {
        $html = '<p>Short text</p>';
        $this->assertSame('Short text', HtmlDigest::extract($html, 100));
    }

    public function testExtractCharModeExactLength(): void
    {
        $html = '<p>12345</p>';
        $this->assertSame('12345', HtmlDigest::extract($html, 5));
    }

    public function testExtractCharModeTruncates(): void
    {
        $html = '<p>Hello World Foo Bar Baz</p>';
        $result = HtmlDigest::extract($html, 10);
        $this->assertSame('Hello...', $result);
        $this->assertLessThanOrEqual(10, mb_strlen($result));
    }

    public function testExtractCharModeDefaultLength(): void
    {
        $longHtml = '<p>' . str_repeat('a', 300) . '</p>';
        $result = HtmlDigest::extract($longHtml);
        $this->assertSame(200, mb_strlen($result));
    }

    public function testExtractCharModeCustomEllipsis(): void
    {
        $html = '<p>Hello World Foo Bar</p>';
        $result = HtmlDigest::extract($html, 10, '…');
        // Word boundary: "Hello Wor" breaks at space → "Hello" + "…" = 6 chars
        $this->assertSame('Hello…', $result);
        $this->assertLessThanOrEqual(10, mb_strlen($result));
    }

    public function testExtractCharModeWordBoundary(): void
    {
        $html = '<p>one two three four five</p>';
        // Length 12 + ellipsis 3 = 15 max content length is 12
        // "one two thre" → last space at position 7, so "one two" + "..."
        $result = HtmlDigest::extract($html, 15);
        $this->assertSame('one two...', $result);
    }

    public function testExtractCharModeMultibyte(): void
    {
        $html = '<p>你好世界这是一段测试文本用于验证多字节截断</p>';
        $result = HtmlDigest::extract($html, 10);
        $this->assertSame(10, mb_strlen($result));
        $this->assertStringEndsWith('...', $result);
    }

    public function testExtractCharModeZeroLengthThrows(): void
    {
        $this->expectException(HtmlDigestException::class);
        HtmlDigest::extract('<p>test</p>', 0);
    }

    public function testExtractCharModeNegativeLengthThrows(): void
    {
        $this->expectException(HtmlDigestException::class);
        HtmlDigest::extract('<p>test</p>', -5);
    }

    public function testExtractCharModeLengthSmallerThanEllipsis(): void
    {
        $html = '<p>Hello World</p>';
        // length 2, ellipsis "..." (3 chars) - maxLen = -1, falls back to substr of ellipsis
        $result = HtmlDigest::extract($html, 2);
        $this->assertSame('..', $result);
        $this->assertSame(2, mb_strlen($result));
    }

    public function testExtractCharModeLengthEqualsEllipsis(): void
    {
        $html = '<p>Hello World</p>';
        $result = HtmlDigest::extract($html, 3);
        $this->assertSame('...', $result);
    }

    public function testExtractCharModeEmptyInput(): void
    {
        $this->assertSame('', HtmlDigest::extract('', 100));
    }

    public function testExtractCharModeWithNewlines(): void
    {
        $html = "<p>First line</p><p>Second line with more content</p>";
        $result = HtmlDigest::extract($html, 15);
        // Should break at newline or space
        $this->assertLessThanOrEqual(15, mb_strlen($result));
        $this->assertStringEndsWith('...', $result);
    }

    public function testExtractCharModeNoSpaceInTruncated(): void
    {
        // Single long word - no space to break at
        $html = '<p>abcdefghijklmnopqrstuvwxyz</p>';
        $result = HtmlDigest::extract($html, 10);
        // No space found, so cuts at maxLen
        $this->assertSame(10, mb_strlen($result));
        $this->assertStringEndsWith('...', $result);
    }

    // ==================== extract - MODE_WORD ====================

    public function testExtractWordModeFewerWordsThanLimit(): void
    {
        $html = '<p>Hello World</p>';
        $this->assertSame('Hello World', HtmlDigest::extract($html, 10, '...', HtmlDigest::MODE_WORD));
    }

    public function testExtractWordModeExactWordCount(): void
    {
        $html = '<p>one two three</p>';
        $this->assertSame('one two three', HtmlDigest::extract($html, 3, '...', HtmlDigest::MODE_WORD));
    }

    public function testExtractWordModeTruncates(): void
    {
        $html = '<p>one two three four five</p>';
        $result = HtmlDigest::extract($html, 3, '...', HtmlDigest::MODE_WORD);
        $this->assertSame('one two three...', $result);
    }

    public function testExtractWordModeSingleWord(): void
    {
        $html = '<p>one two three</p>';
        $result = HtmlDigest::extract($html, 1, '...', HtmlDigest::MODE_WORD);
        $this->assertSame('one...', $result);
    }

    public function testExtractWordModeCustomEllipsis(): void
    {
        $html = '<p>one two three four</p>';
        $result = HtmlDigest::extract($html, 2, ' …', HtmlDigest::MODE_WORD);
        $this->assertSame('one two …', $result);
    }

    public function testExtractWordModeZeroCountThrows(): void
    {
        $this->expectException(HtmlDigestException::class);
        HtmlDigest::extract('<p>test</p>', 0, '...', HtmlDigest::MODE_WORD);
    }

    public function testExtractWordModeNegativeCountThrows(): void
    {
        $this->expectException(HtmlDigestException::class);
        HtmlDigest::extract('<p>test</p>', -3, '...', HtmlDigest::MODE_WORD);
    }

    public function testExtractWordModeEmptyInput(): void
    {
        $this->assertSame('', HtmlDigest::extract('', 10, '...', HtmlDigest::MODE_WORD));
    }

    public function testExtractWordModeMultipleSpaces(): void
    {
        $html = '<p>one   two    three</p>';
        $result = HtmlDigest::extract($html, 2, '...', HtmlDigest::MODE_WORD);
        $this->assertSame('one two...', $result);
    }

    // ==================== Exception: unknown mode ====================

    public function testExtractUnknownModeThrows(): void
    {
        $this->expectException(HtmlDigestException::class);
        HtmlDigest::extract('<p>test</p>', 100, '...', 'invalid_mode');
    }

    // ==================== HtmlDigestException ====================

    public function testExceptionIsRuntimeException(): void
    {
        $e = new HtmlDigestException('test error');
        $this->assertInstanceOf(\RuntimeException::class, $e);
        $this->assertSame('test error', $e->getMessage());
        $this->assertSame(0, $e->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $prev = new \Exception('previous');
        $e = new HtmlDigestException('wrapper', 5, $prev);
        $this->assertSame('wrapper', $e->getMessage());
        $this->assertSame(5, $e->getCode());
        $this->assertSame($prev, $e->getPrevious());
    }

    // ==================== Edge cases ====================

    public function testExtractOnlyWhitespaceHtml(): void
    {
        $html = '<p>   </p>';
        $result = HtmlDigest::toText($html);
        $this->assertSame('', $result);
    }

    public function testExtractHtmlCommentsAreStripped(): void
    {
        $html = '<p>Hello<!-- comment -->World</p>';
        // strip_tags doesn't strip comments!
        // Actually let me check: strip_tags does remove HTML comments
        $this->assertSame('HelloWorld', HtmlDigest::toText($html));
    }

    public function testExtractScriptAndStyleTags(): void
    {
        $html = '<script>alert(1)</script><p>text</p><style>.x{}</style>';
        $result = HtmlDigest::toText($html);
        // Script and style content should be removed entirely
        $this->assertSame('text', $result);
        $this->assertStringNotContainsString('alert', $result);
        $this->assertStringNotContainsString('.x{}', $result);
    }

    public function testToTextRemovesHeadAndNoscript(): void
    {
        $html = '<html><head><title>Page Title</title><script>var x=1</script></head><body><p>Hello</p><noscript>no js</noscript></body></html>';
        $result = HtmlDigest::toText($html);
        $this->assertSame('Hello', $result);
        $this->assertStringNotContainsString('Page Title', $result);
        $this->assertStringNotContainsString('no js', $result);
    }

    // ==================== Regression: Unicode whitespace (nbsp / full-width) ====================

    public function testToTextFiltersOutNbspOnlyParagraph(): void
    {
        $html = '<p>a</p><p>&nbsp;</p><p>b</p>';
        $this->assertSame("a\nb", HtmlDigest::toText($html));
    }

    public function testToTextCollapsesFullWidthSpace(): void
    {
        $html = '<p>你好　世界</p>';
        $this->assertSame('你好 世界', HtmlDigest::toText($html));
    }

    public function testToTextFiltersOutWhitespaceOnlyLine(): void
    {
        $html = "<p>a</p><p>\t   </p><p>b</p>";
        $this->assertSame("a\nb", HtmlDigest::toText($html));
    }

    public function testExtractCharModeBreaksOnNbsp(): void
    {
        $html = '<p>hello&nbsp;world&nbsp;foo</p>';
        $this->assertSame('hello...', HtmlDigest::extract($html, 9));
    }

    public function testExtractCharModeBreaksOnFullWidthSpace(): void
    {
        $html = '<p>你好　世界　更多内容</p>';
        $this->assertSame('你好...', HtmlDigest::extract($html, 6));
    }

    // ==================== Regression: CJK word mode ====================

    public function testExtractWordModeCjk(): void
    {
        $html = '<p>这是一个中文长句测试</p>';
        $this->assertSame('这是一个...', HtmlDigest::extract($html, 4, '...', HtmlDigest::MODE_WORD));
    }

    public function testExtractWordModeCjkWithinLimitKeepsRawText(): void
    {
        $html = '<p>你好世界</p>';
        $this->assertSame('你好世界', HtmlDigest::extract($html, 4, '...', HtmlDigest::MODE_WORD));
    }

    public function testExtractWordModeCjkWithPunctuation(): void
    {
        $html = '<p>这是。你好</p>';
        $this->assertSame('这是...', HtmlDigest::extract($html, 2, '...', HtmlDigest::MODE_WORD));
    }
}
