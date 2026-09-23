# migears/html-digest

![Version](https://img.shields.io/badge/version-2.0.0-blue)

A lightweight HTML text summary (digest) extractor for PHP 8.1+. It turns HTML into clean plain text and applies smart, multibyte-safe truncation — with **zero DOM extension dependencies** (built on `strip_tags` + regex + `mbstring`).

Designed for the miGears framework philosophy: **minimal, dependency-free, readable in minutes**.

---

> **Background**: miGears is the open-source successor of **TinyGears**, a
> self-developed PHP framework. It was renamed and open-sourced recently because
> the name *TinyGears* is already taken in the open-source community.

## Features

- **PHP 8.1+**, using modern syntax (type declarations, `match`, `readonly`, arrow fns)
- **PSR-4** autoloading, namespace `MiGears\HtmlDigest`
- **Zero required dependencies** — only `ext-mbstring`
- **Core class under 150 lines**, a one-line static API (`HtmlDigest::extract()`)
- **Smart truncation** that does not cut words in half
- **Two truncation modes**: by character count (`MODE_CHAR`) / by word count (`MODE_WORD`)
- **CJK-aware word mode** — treats each CJK character as one token, so pure-Chinese text can be truncated
- **Unicode whitespace awareness** — `&nbsp;` and full-width spaces (`　`) are treated as whitespace and normalized
- **Customizable ellipsis** (default `...`)
- **No DOM extension** — safe in environments where `dom`/`libxml` is unavailable or too heavy

---

## Installation

```bash
composer require migears/html-digest
```

---

## Quick Start

```php
use MiGears\HtmlDigest\HtmlDigest;

$html = '<p>Hello <strong>World</strong>. This is a <em>sample</em> paragraph.</p>';

// Summary, default 200 chars, "..." ellipsis
echo HtmlDigest::extract($html);

// Custom length
echo HtmlDigest::extract($html, length: 50);

// Summary by word count (keeps first 5 words)
echo HtmlDigest::extract($html, length: 5, mode: HtmlDigest::MODE_WORD);

// Custom ellipsis (multibyte-safe)
echo HtmlDigest::extract($html, length: 20, ellipsis: '…');

// Plain text only, no truncation
echo HtmlDigest::toText($html);
```

---

## Worked example: input → output

To see the real value, compare the *original HTML* with what each method returns. The sample below is realistic: it contains hidden document chrome (`head`/`title`/`script`/`style`), a heading, paragraphs with inline formatting, a list, HTML entities, `&nbsp;`, an image and a link.

**① The original HTML**

```html
<head><title>How to Brew Coffee</title>
<script>var ads = "hidden tracking pixel";</script>
<style>.alert { color: red; }</style></head>
<body>
<h1>A Better Morning: How to Brew Coffee</h1>
<p>Brewing great coffee at home is easier than you think. Start with <strong>fresh beans</strong>, grind them just before brewing, and use <em>filtered water</em>.</p>
<p>Follow these steps&nbsp;&nbsp;(no, really &amp; it works):</p>
<ul>
  <li>Weigh 18g of beans.</li>
  <li>Heat water to 93&deg;C.</li>
  <li>Brew for 3 minutes.</li>
</ul>
<p>Visit our <a href="/guide">full guide</a> for details or <img src="beans.jpg" alt="roasted beans"> enjoy a cup.</p>
</body>
```

**② `toText()` — everything invisible is gone**

The `<head>`, `<title>`, `<script>`, `<style>` nodes are dropped *entirely*; tags are stripped; entities are decoded (`&amp;` → `&`, `&deg;` → `°`); `&nbsp;` is collapsed to a space; each block lands on its own line:

```
A Better Morning: How to Brew Coffee
Brewing great coffee at home is easier than you think. Start with fresh beans, grind them just before brewing, and use filtered water.
Follow these steps (no, really & it works):
Weigh 18g of beans.
Heat water to 93°C.
Brew for 3 minutes.
Visit our full guide for details or enjoy a cup.
```

No more `script`, no `style`, no `&amp;`, no `&nbsp;`, no `<img>` tag noise — just the words a human would read.

**③ `extract()` — summaries at any length, never a cut word**

| Call | Output |
|------|--------|
| `extract($html)` (default `200`) | the full clean text from ② |
| `extract($html, 30)` | `A Better Morning: How to...` |
| `extract($html, 20)` | `A Better...` |
| `extract($html, 10)` | `A...` |
| `extract($html, 6, '...', MODE_WORD)` | `A Better Morning: How to Brew...` |

Watch the word boundary: `extract($html, 30)` **never** returns `"A Better Morning: How to Co…"` — it steps back to the last whitespace, so words are never sliced in half.

**④ CJK sample — Chinese is handled too**

```html
<h2>中文冲泡指南</h2><p>水温、粉量与时长的平衡，是手冲咖啡的关键。&nbsp; 多试几次，你会找到自己的偏好。</p><ul><li>水温 92°C</li><li>粉量 18 克</li></ul>
```

| Call | Output |
|------|--------|
| `toText()` | `中文冲泡指南` / `水温、粉量与时长的平衡，是手冲咖啡的关键。 多试几次，你会找到自己的偏好。` / `水温 92°C` / `粉量 18 克` (line-separated) |
| `extract($zh, 20)` | `中文冲泡指南...` |
| `extract($zh, 8, '…', MODE_WORD)` | `中文冲泡指南水温、粉量与时长的平衡，是手冲咖啡的关键。多试几次，你会找到自己的偏好。…` |

The `&nbsp;` inside the sentence is normalized to a space, the `°C` survives, and **word mode works on space-less Chinese** by counting each CJK character as a token.

---

## Real-world scenarios

### Blog / article excerpt

```php
// Pull the first ~120 chars of any rich-text post into a readable excerpt
$excerpt = HtmlDigest::extract($post->body_html, length: 120);
```

`body_html` may be a full WYSIWYG blob (headings, images, embedded scripts), but the excerpt stays clean and never mid-word.

### SEO meta description

Google typically shows ~155 characters:

```php
$metaDescription = HtmlDigest::extract($pageHtml, length: 155, ellipsis: '…');
```

Entities and stray markup are already resolved, so the meta tag won't contain raw `&amp;` or `<strong>`.

### RSS / Atom / feed teaser

```php
// Feed entries want a short, uniform teaser regardless of h1/h2/p length
$teaser = HtmlDigest::extract($entryHtml, length: 100);
```

### Chat / email preview (by words)

```php
// "Unread (3): John says..." — count words, not characters
$preview = HtmlDigest::extract($messageHtml, length: 6, ellipsis: '…', mode: HtmlDigest::MODE_WORD);
```

Word mode is ideal when the display width is flexible but you want a fixed *word* count, e.g. notification titles in IM apps.

### Search indexing (full clean text)

```php
// No truncation — feed a normalized, tag-free string to your tokenizer
$plain = HtmlDigest::toText($articleHtml);
```

Useful before building an FTS index, embeddings, or a simple keyword search. Removes heavy markup overhead while keeping paragraph breaks for readability.

### Multilingual share card / `og:description`

```php
// Chinese copy with a custom ellipsis, truncated to a card's line budget
$ogDescription = HtmlDigest::extract($shareHtml, length: 40, ellipsis: '…');
```

Because truncation is multibyte-safe, CJK characters are never split.

### Comment / review sanitized preview

```php
// Render a safe, uniform preview instead of dumping raw user HTML
$safePreview = HtmlDigest::extract($commentHtml, length: 80);
```

The output contains no tags and no scripts, so it can be placed into a text node without any risk of injected markup.

---

## API

### `HtmlDigest::extract()`

Extracts plain text from HTML and applies smart truncation. Truncation happens *after* normalization, so the result is clean and never mid-word.

```php
public static function extract(
    string $html,
    int $length = 200,
    string $ellipsis = '...',
    string $mode = self::MODE_CHAR,
): string
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$html`    | `string` | — | Input HTML. Malformed/partial HTML is tolerated (best-effort). |
| `$length`  | `int`    | `200` | Truncation limit. Meaning depends on `$mode` (chars or words). Must be `> 0`. |
| `$ellipsis`| `string` | `'...'` | Appended only **when truncation actually occurs**. Multibyte-safe. |
| `$mode`    | `string` | `MODE_CHAR` | `MODE_CHAR` (by characters) or `MODE_WORD` (by words). |

**Constants**

| Constant | Value  | Meaning |
|----------|--------|---------|
| `HtmlDigest::MODE_CHAR` | `'char'` | Truncate by character count. |
| `HtmlDigest::MODE_WORD` | `'word'` | Truncate by word count. |

**Returns** — the truncated plain-text summary as a `string`.

- If the normalized text is shorter than or equal to `$length`, the **full text is returned unchanged** (no ellipsis added).
- Truncated text is **guaranteed to be at most `$length` characters** (in `MODE_WORD`, at most `$length` words).

**Throws `HtmlDigestException`** (extends `\RuntimeException`):

| Condition | Example |
|-----------|---------|
| `$length` / word count `<= 0` | `HtmlDigest::extract($html, 0)` |
| Unknown `$mode` | `HtmlDigest::extract($html, 10, '...', 'bad_mode')` |
| `$length` smaller than / equal to the ellipsis length | *(see "Length vs ellipsis" below — not an error, handled gracefully)* |

### `HtmlDigest::toText()`

Converts HTML to clean plain text — **no truncation**. This is what `extract()` uses under the hood.

```php
public static function toText(string $html): string
```

**Returns** — normalized plain text with reasonable line breaks.

**Processing pipeline:**

1. **Removes hidden elements and their content**: `<script>`, `<style>`, `<head>`, `<noscript>`.
2. **Block-level closing tags → newline**: `</p>`, `</div>`, `</li>`, `</h1>`–`</h6>`, `</tr>`, `</blockquote>`, `</pre>`, `</dd>`, `</dt>`; also `<br>` → newline.
3. **Strips all remaining tags** (`strip_tags`) and **decodes HTML entities** (`&amp;` → `&`, `&lt;` → `<`, `&nbsp;` → non-breaking space, etc.).
4. **Normalizes each line**: trims leading/trailing whitespace, collapses runs of whitespace (including `&nbsp;` and full-width spaces) into a single space.
5. **Drops blank lines** (lines containing only whitespace).
6. **Trims Unicode whitespace** at the boundaries.

---

## Truncation behavior in detail

### Word-boundary rollback (no half-words)

In `MODE_CHAR`, if the cut point lands in the middle of a word, the result steps back to the **last whitespace** (space, tab, newline, `&nbsp;`, or full-width space) so it never shows a broken token:

```php
#> extract('<p>Hello World Foo Bar</p>', 10)   // maxLen 7 → "Hello Wo" → rollback
#=> 'Hello...'
```

### `&nbsp;` and full-width spaces are real whitespace

Both are normalized to a single ASCII space in `toText()`. This means:

- A paragraph containing **only** `&nbsp;` is treated as a blank line and removed (no invisible empty lines).
- Word-boundary rollback and `MODE_WORD` splitting recognize them as separators.

```php
#> HtmlDigest::toText('<p>a</p><p>&nbsp;</p><p>b</p>')      // "a\nb"  (no phantom line)
#> HtmlDigest::toText('<p>你好　世界</p>')                     // "你好 世界"  (full-width → space)
#> HtmlDigest::extract('<p>hello&nbsp;world&nbsp;foo</p>', 9)  // 'hello...'  (breaks at nbsp)
```

### CJK (Chinese / Japanese) in word mode

`MODE_WORD` splits on whitespace, but CJK text has no spaces, so each **CJK character is treated as one word-token**. CJK tokens are also re-joined without spaces in the output.

```php
#> HtmlDigest::extract('<p>这是一个中文长句测试</p>', 4, '...', HtmlDigest::MODE_WORD)
#=> '这是一个...'
```

Mixed text (e.g. `Hello世界`) is kept as a single token and counted as one word.

### Length vs ellipsis edge cases

- **`length` > ellipsis length**: `maxLen = length - strlen(ellipsis)`; content is truncated to `maxLen`, then the ellipsis is appended → total `≤ length`.
- **`length` == ellipsis length**: returns exactly the ellipsis. `extract('...anything', 3)` → `'...'`.
- **`length` < ellipsis length**: returns the first `length` characters of the ellipsis. `extract($html, 2)` → `'..'` (2 chars total).

### Multiline text

Text joined from multiple paragraphs (`toText` joins with `\n`) rolls back to the last whitespace **across newlines**, so trailing content is not dropped:

```php
#> HtmlDigest::extract('<p>alpha beta gamma</p><p>delta epsilon foo</p>', 26)
#=> "alpha beta gamma\ndelta..."
```

---

## Limitations & notes

- **Best-effort HTML parsing.** Because it deliberately avoids a DOM extension, it cannot reliably parse deeply malformed markup. In particular, an **unclosed `<script>`/`<style>`** tag may leak its content into the output — this is an accepted trade-off for zero DOM dependencies. Feed it well-formed HTML.
- **HTML comments are stripped** by `strip_tags`.
- **Whitespace normalization collapses runs** of spaces; if you need to preserve code indentation inside `<pre>`, `toText()` is not the right tool.
- **`MODE_WORD`** counts CJK characters individually; English word counting is space-based.

---

## Testing

```bash
composer install
./vendor/bin/phpunit --coverage-text
```

52 tests cover `toText()` normalization, both truncation modes, CJK handling, Unicode whitespace, ellipsis edge cases, and exception paths.

---

## License

MIT

---

# migears/html-digest

![Version](https://img.shields.io/badge/version-2.0.0-blue)

面向 PHP 8.1+ 的轻量级 HTML 文本摘要（digest）提取器。将 HTML 转化为干净的纯文本，并执行多字节安全的智能截断——**零 DOM 扩展依赖**（基于 `strip_tags` + 正则 + `mbstring`）。

沿袭 miGears 框架理念：**极简、零依赖、几分钟内读完**。

---

## 特性

- **PHP 8.1+**，使用现代语法（类型声明、`match`、`readonly`、箭头函数）
- 遵循 **PSR-4** 自动加载，命名空间 `MiGears\HtmlDigest`
- **零强制依赖**——仅需 `ext-mbstring`
- **核心类不足 150 行**，极简静态 API，一行调用（`HtmlDigest::extract()`）
- **智能截断**，不会把单词/文字从中间切断
- **两种截断模式**：按字符数（`MODE_CHAR`）/ 按词数（`MODE_WORD`）
- **中文（CJK）友好的词数模式**——把每个汉字视作一个词，纯中文文本也能截断
- **Unicode 空白感知**——`&nbsp;` 和全角空格（`　`）都被视作空白并归一化
- **自定义省略号**（默认 `...`）
- **无需 DOM 扩展**——在无法使用 `dom`/`libxml` 或嫌其过重的环境中依然安全可用

---

## 安装

```bash
composer require migears/html-digest
```

---

## 快速开始

```php
use MiGears\HtmlDigest\HtmlDigest;

$html = '<p>Hello <strong>World</strong>. This is a <em>sample</em> paragraph.</p>';

// 提取摘要，默认 200 字符，省略号 ...
echo HtmlDigest::extract($html);

// 自定义长度
echo HtmlDigest::extract($html, length: 50);

// 按词数截断（保留前 5 个词）
echo HtmlDigest::extract($html, length: 5, mode: HtmlDigest::MODE_WORD);

// 自定义省略号（多字节安全）
echo HtmlDigest::extract($html, length: 20, ellipsis: '…');

// 仅转纯文本，不截断
echo HtmlDigest::toText($html);
```

---

## 完整示例：原始 HTML → 提取结果

要直观看到它的价值，请把**原始 HTML** 与各方法的返回值对照。下面的样本是真实场景：包含隐藏的文档外壳（`head`/`title`/`script`/`style`）、标题、带内联格式的段落、列表、HTML 实体、`&nbsp;`、图片与链接。

**① 原始 HTML**

```html
<head><title>How to Brew Coffee</title>
<script>var ads = "hidden tracking pixel";</script>
<style>.alert { color: red; }</style></head>
<body>
<h1>A Better Morning: How to Brew Coffee</h1>
<p>Brewing great coffee at home is easier than you think. Start with <strong>fresh beans</strong>, grind them just before brewing, and use <em>filtered water</em>.</p>
<p>Follow these steps&nbsp;&nbsp;(no, really &amp; it works):</p>
<ul>
  <li>Weigh 18g of beans.</li>
  <li>Heat water to 93&deg;C.</li>
  <li>Brew for 3 minutes.</li>
</ul>
<p>Visit our <a href="/guide">full guide</a> for details or <img src="beans.jpg" alt="roasted beans"> enjoy a cup.</p>
</body>
```

**② `toText()` —— 所有不可见内容都被清除了**

`<head>`、`<title>`、`<script>`、`<style>` 节点被**整体移除**；标签被剥除；实体被解码（`&amp;` → `&`、`&deg;` → `°`）；`&nbsp;` 折叠为空格；每个块独占一行：

```
A Better Morning: How to Brew Coffee
Brewing great coffee at home is easier than you think. Start with fresh beans, grind them just before brewing, and use filtered water.
Follow these steps (no, really & it works):
Weigh 18g of beans.
Heat water to 93°C.
Brew for 3 minutes.
Visit our full guide for details or enjoy a cup.
```

没有 `script`、没有 `style`、没有 `&amp;`、没有 `&nbsp;`、没有 `<img>` 标签噪音——只剩读者会看到的文字。

**③ `extract()` —— 任意长度的摘要，绝不切断单词**

| 调用 | 输出 |
|------|------|
| `extract($html)`（默认 `200`）| ② 中的完整干净文本 |
| `extract($html, 30)` | `A Better Morning: How to...` |
| `extract($html, 20)` | `A Better...` |
| `extract($html, 10)` | `A...` |
| `extract($html, 6, '...', MODE_WORD)` | `A Better Morning: How to Brew...` |

注意词边界：`extract($html, 30)` **绝不会**返回 `"A Better Morning: How to Co…"`——它会回退到最后一个空白，单词永远不会被切半。

**④ 中文样本 —— 中文同样处理良好**

```html
<h2>中文冲泡指南</h2><p>水温、粉量与时长的平衡，是手冲咖啡的关键。&nbsp; 多试几次，你会找到自己的偏好。</p><ul><li>水温 92°C</li><li>粉量 18 克</li></ul>
```

| 调用 | 输出 |
|------|------|
| `toText()` | `中文冲泡指南` / `水温、粉量与时长的平衡，是手冲咖啡的关键。 多试几次，你会找到自己的偏好。` / `水温 92°C` / `粉量 18 克`（每行一条）|
| `extract($zh, 20)` | `中文冲泡指南...` |
| `extract($zh, 8, '…', MODE_WORD)` | `中文冲泡指南水温、粉量与时长的平衡，是手冲咖啡的关键。多试几次，你会找到自己的偏好。…` |

句中的 `&nbsp;` 被归一化为空格，`°C` 得以保留，**中文在无空格的词数模式下也能截断**（每个汉字按一个词计数）。

---

## 实际使用场景

### 博客 / 文章摘要

```php
// 从任意富文本正文提取前 ~120 字符的可读摘录
$excerpt = HtmlDigest::extract($post->body_html, length: 120);
```

`body_html` 可能是一整段 WYSIWYG 内容（标题、图片、内嵌脚本），但摘要始终干净、绝不会从单词中间断开。

### SEO meta description

Google 通常展示约 155 个字符：

```php
$metaDescription = HtmlDigest::extract($pageHtml, length: 155, ellipsis: '…');
```

实体与残留标记已预先处理，meta 标签里不会出现裸的 `&amp;` 或 `<strong>`。

### RSS / Atom / Feed 摘要

```php
// Feed 条目需要一个与 h1/h2/p 长度无关的、长度统一的摘要
$teaser = HtmlDigest::extract($entryHtml, length: 100);
```

### 聊天 / 邮件预览（按词数）

```php
// “未读(3)：John 说…” —— 按词数而非字符数
$preview = HtmlDigest::extract($messageHtml, length: 6, ellipsis: '…', mode: HtmlDigest::MODE_WORD);
```

当显示宽度不固定、但想要固定**词数**时（例如 IM 应用的通知标题），词数模式最合适。

### 搜索索引（完整纯文本）

```php
// 不截断——为分词器/向量化喂入规范、无标签的字符串
$plain = HtmlDigest::toText($articleHtml);
```

在构建全文索引、向量 embedding 或简单关键词搜索前很实用：去掉多余的标记开销，同时保留段落分隔以维持可读性。

### 多语言分享卡片（`og:description`）

```php
// 中文文案 + 自定义省略号，按卡片的行数预算截断
$ogDescription = HtmlDigest::extract($shareHtml, length: 40, ellipsis: '…');
```

因为截断是多字节安全的，CJK 字符永远不会被切成两半。

### 评论 / 评价的安全预览

```php
// 渲染一个安全、统一的预览，而不是直接输出用户的原始 HTML
$safePreview = HtmlDigest::extract($commentHtml, length: 80);
```

输出不含任何标签与脚本，可直接放入文本节点，绝无注入标记的风险。

---

## API

### `HtmlDigest::extract()`

从 HTML 中提取纯文本并执行智能截断。截断发生在归一化**之后**，因此结果干净、绝不会从词中间切断。

```php
public static function extract(
    string $html,
    int $length = 200,
    string $ellipsis = '...',
    string $mode = self::MODE_CHAR,
): string
```

| 参数 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| `$html`     | `string`   | — | 输入 HTML。对畸形 / 残缺 HTML 尽力容忍。 |
| `$length`   | `int`      | `200` | 截断上限。含义取决于 `$mode`（字符数或词数）。必须 `> 0`。 |
| `$ellipsis` | `string`   | `'...'` | 仅在实际发生截断时追加。多字节安全。 |
| `$mode`     | `string`   | `MODE_CHAR` | 截断模式：`MODE_CHAR`（按字符）或 `MODE_WORD`（按词数）。 |

**常量**

| 常量 | 取值 | 含义 |
|------|------|------|
| `HtmlDigest::MODE_CHAR` | `'char'` | 按字符数截断。 |
| `HtmlDigest::MODE_WORD` | `'word'` | 按词数截断。 |

**返回值**——截断后的纯文本摘要（`string`）。

- 若归一化后文本长度 ≤ `$length`，则**原样返回全文（不追加省略号）**。
- 截断结果**保证不超过 `$length` 字符**（`MODE_WORD` 下不超过 `$length` 个词）。

**抛出 `HtmlDigestException`**（继承 `\RuntimeException`）：

| 场景 | 示例 |
|------|------|
| `$length` / 词数 `<= 0` | `HtmlDigest::extract($html, 0)` |
| 未知的 `$mode` | `HtmlDigest::extract($html, 10, '...', 'bad_mode')` |
| `$length` 小于 / 等于省略号长度 | *（见下方「length 与省略号」小节——非错误，会被优雅处理）* |

### `HtmlDigest::toText()`

将 HTML 转化为干净的纯文本——**不截断**。`extract()` 内部即调用它。

```php
public static function toText(string $html): string
```

**返回值**——带合理换行的归一化纯文本。

**处理管线：**

1. **移除隐藏元素及其内容**：`<script>`、`<style>`、`<head>`、`<noscript>`。
2. **块级闭合标签 → 换行**：`</p>`、`</div>`、`</li>`、`</h1>`–`</h6>`、`</tr>`、`</blockquote>`、`</pre>`、`</dd>`、`</dt>`；`<br>` 也转成换行。
3. **剥除其余标签**（`strip_tags`）并**解码 HTML 实体**（`&amp;` → `&`、`&lt;` → `<`、`&nbsp;` → 不间断空格等）。
4. **逐行归一化**：去除行首行尾空白，将连续空白（含 `&nbsp;` 与全角空格）折叠为一个空格。
5. **过滤空行**（仅含空白的行）。
6. **修剪边界的 Unicode 空白**。

---

## 截断行为详解

### 词边界回退（不截断半个词）

在 `MODE_CHAR` 下，若切断点落在一个词的中间，结果会**回退到最后一个空白**（空格、tab、换行、`&nbsp;`、全角空格），从而不会出现破碎的单词：

```php
#> HtmlDigest::extract('<p>Hello World Foo Bar</p>', 10)   // maxLen 7 → "Hello Wo" → 回退
#=> 'Hello...'
```

### `&nbsp;` 与全角空格都是真正的空白

二者都会在 `toText()` 中被归一化为一个普通空格。这意味着：

- 仅含 `&nbsp;` 的段落会被判定为空行并移除（不会产生“隐形空行”）。
- 词边界回退与 `MODE_WORD` 分词都会把二者当作分隔符。

```php
#> HtmlDigest::toText('<p>a</p><p>&nbsp;</p><p>b</p>')      // "a\nb"  无幻影空行
#> HtmlDigest::toText('<p>你好　世界</p>')                     // "你好 世界"  全角空格 → 空格
#> HtmlDigest::extract('<p>hello&nbsp;world&nbsp;foo</p>', 9)  // 'hello...'  在 nbsp 处断开
```

### 词数模式下的中文 / 日文（CJK）

`MODE_WORD` 依赖空格分词，但中文没有空格，因此每个**汉字被视作一个词**。输出时 CJK 字之间**不会**插入额外空格。

```php
#> HtmlDigest::extract('<p>这是一个中文长句测试</p>', 4, '...', HtmlDigest::MODE_WORD)
#=> '这是一个...'
```

混合文本（如 `Hello世界`）整体作为一个词计数，不拆解。

### length 与省略号的边界情况

- **`length` > 省略号长度**：`maxLen = length - 省略号长度`；内容截断到 `maxLen`，再追加省略号 → 总长 `≤ length`。
- **`length` == 省略号长度**：恰好返回省略号本身。`extract('...任意文本', 3)` → `'...'`。
- **`length` < 省略号长度**：返回省略号的前 `length` 个字符。`extract($html, 2)` → `'..'`（共 2 字符）。

### 多段文本

由多段落拼接的文本（`toText` 用 `\n` 拼接）会回退到**跨换行的最后一个空白**，不会丢失后段内容：

```php
#> HtmlDigest::extract('<p>alpha beta gamma</p><p>delta epsilon foo</p>', 26)
#=> "alpha beta gamma\ndelta..."
```

---

## 限制与注意事项

- **尽力而为的 HTML 解析。** 为有意避免 DOM 扩展，它无法可靠解析深层畸形标记。尤其是**未闭合的 `<script>`/`<style>`** 可能把内部内容泄漏到输出——这是零 DOM 依赖下的取舍。请传入规范的 HTML。
- **HTML 注释会被 `strip_tags` 一并剥除。**
- **空白归一化会折叠连续空格**；若需保留 `<pre>` 内的代码缩进，`toText()` 并不适用。
- **`MODE_WORD`** 将 CJK 字符逐字计数；英文则按空格分词。

---

## 测试

```bash
composer install
./vendor/bin/phpunit --coverage-text
```

52 个测试覆盖 `toText()` 归一化、两种截断模式、CJK 处理、Unicode 空白、省略号边界与异常路径。

---

## License

MIT