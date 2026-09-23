# migears/html-digest

![Version](https://img.shields.io/badge/version-2.0.0-blue)

A lightweight HTML text summary extractor, based on `strip_tags` + multibyte string processing, with zero DOM extension dependencies.

## Features

- PHP 8.1+, using modern syntax features (type declarations, readonly, match expressions, etc.)
- Follows PSR-4 autoloading standard, namespace `MiGears\HtmlDigest`
- Zero required dependencies (only `ext-mbstring` needed)
- Core class under 150 lines
- Minimalist static API, one-line call
- Smart truncation (does not break words)
- Supports two truncation modes: by character count / by word count
- CJK-aware word mode (treats each CJK character as a token)
- Normalizes `&nbsp;` and full-width spaces as whitespace
- Customizable ellipsis

## Installation

```bash
composer require migears/html-digest
```

## Quick Start

```php
use MiGears\HtmlDigest\HtmlDigest;

$html = '<p>Hello <strong>World</strong>. This is a <em>sample</em> paragraph.</p>';

// Extract and truncate (default 200 characters, ... ellipsis)
echo HtmlDigest::extract($html);

// Custom length
echo HtmlDigest::extract($html, length: 50);

// Truncate by word count
echo HtmlDigest::extract($html, length: 5, mode: HtmlDigest::MODE_WORD);

// Custom ellipsis
echo HtmlDigest::extract($html, length: 20, ellipsis: '…');

// Convert to plain text only, no truncation
echo HtmlDigest::toText($html);
```

## API

### `HtmlDigest::extract()`

Extracts a plain text summary from HTML and applies smart truncation.

```php
public static function extract(
    string $html,
    int $length = 200,
    string $ellipsis = '...',
    string $mode = self::MODE_CHAR,
): string
```

**Parameters:**
- `html` — Input HTML string
- `length` — Truncation length (character count or word count, depending on mode)
- `ellipsis` — Ellipsis appended when truncated
- `mode` — Truncation mode: `MODE_CHAR` (by character) / `MODE_WORD` (by word)

**Exceptions:**
- `HtmlDigestException` — Invalid length or unknown mode

### `HtmlDigest::toText()`

Converts HTML to plain text, preserving reasonable line break formatting.

```php
public static function toText(string $html): string
```

## Testing

```bash
composer install
./vendor/bin/phpunit --coverage-text
```

## License

MIT

---

# migears/html-digest

![Version](https://img.shields.io/badge/version-2.0.0-blue)

轻量级 HTML 文本摘要提取器，基于 `strip_tags` + 多字节字符串处理，零 DOM 扩展依赖。

## 特性

- PHP 8.1+，使用现代语法特性（类型声明、readonly、match 表达式等）
- 遵循 PSR-4 自动加载规范，命名空间 `MiGears\HtmlDigest`
- 零强制依赖（仅需 `ext-mbstring`）
- 核心类不足 150 行
- 极简静态 API，一行调用
- 智能截断（不截断单词）
- 支持按字符数 / 按词数两种截断模式
- 词数模式支持中文（CJK 感知，逐字分词）
- 将 `&nbsp;` / 全角空格视作空白并归一化
- 自定义省略号

## 安装

```bash
composer require migears/html-digest
```

## 快速开始

```php
use MiGears\HtmlDigest\HtmlDigest;

$html = '<p>Hello <strong>World</strong>. This is a <em>sample</em> paragraph.</p>';

// 提取并截断（默认 200 字符，... 省略号）
echo HtmlDigest::extract($html);

// 自定义长度
echo HtmlDigest::extract($html, length: 50);

// 按词数截断
echo HtmlDigest::extract($html, length: 5, mode: HtmlDigest::MODE_WORD);

// 自定义省略号
echo HtmlDigest::extract($html, length: 20, ellipsis: '…');

// 仅转纯文本，不截断
echo HtmlDigest::toText($html);
```

## API

### `HtmlDigest::extract()`

从 HTML 中提取纯文本摘要并智能截断。

```php
public static function extract(
    string $html,
    int $length = 200,
    string $ellipsis = '...',
    string $mode = self::MODE_CHAR,
): string
```

**参数：**
- `html` — 输入 HTML 字符串
- `length` — 截断长度（字符数或词数，取决于 mode）
- `ellipsis` — 截断时追加的省略号
- `mode` — 截断模式：`MODE_CHAR`（按字符）/ `MODE_WORD`（按词数）

**异常：**
- `HtmlDigestException` — 无效长度或未知模式

### `HtmlDigest::toText()`

将 HTML 转换为纯文本，保留合理的换行格式。

```php
public static function toText(string $html): string
```

## 测试

```bash
composer install
./vendor/bin/phpunit --coverage-text
```

## License

MIT
