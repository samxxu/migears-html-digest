# migears-html-digest — Known Issues

> Summary of this module's issues. The items themselves are in [`issues/`](issues/README.md), one file
> per item: a front-matter header and a thread. This file is generated from them and can be rewritten at
> any time; edit an item, never this file.
>
> From the miGears Full-Module Code Review Report (5th round, 2026-09-28).

| | |
|---|---|
| Status | **Best state** |
| Size | src 119 lines (net) · 63 tests · 1 src file |

Legend — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs

## At a glance

| | |
|---|---|
| Unsettled | P0 0 · P1 0 · P2 1 · P3 1 · other 0 |
| Settled | 4 of 6 |
| Waiting on the owner | `P3-3` |
| Waiting on the coordinator | _nothing_ |
| Waiting on the reviewer | _nothing_ |
| Deferred, owing nobody | `P2-1` |

| id | level | status | title |
|---|---|---|---|
| [`P1-1`](issues/P1-1.md) | P1 | **verified** | Entity-encoded non-hidden tags survive into the output: … |
| [`P2-1`](issues/P2-1.md) | P2 | **deferred** | An entity-encoded declaration or processing instruction re-forms and … |
| [`P3-1`](issues/P3-1.md) | P3 | **verified** | The README contains 20 `---` rules; the bilingual divider is the same … |
| [`P3-2`](issues/P3-2.md) | P3 | **verified** | `VERSION` still has zero references, and the README says 'under 150 … |
| [`P3-3`](issues/P3-3.md) | P3 | **open** | Second-pass tag-stripping regex breaks on > characters inside attribute … |
| [`G2`](issues/G2.md) | - | **verified** | Strict flags: `phpunit.xml.dist` currently sets none of the five. The … |

## Unclosed

What is left to do here: every item whose `status` is not `verified` or `closed`,
highest severity first. `waiting on` is the party who acts next, read from that status.

| | |
|---|---|
| Unclosed | **2** of 6 |
| By status | `open` 1 · `deferred` 1 |
| Waiting on | owner 1 · - 1 |

| level | item | status | waiting on | title |
|---|---|---|---|---|
| **P2** | [`P2-1`](issues/P2-1.md) | `deferred` | - | An entity-encoded declaration or processing instruction re-forms and … |
| **P3** | [`P3-3`](issues/P3-3.md) | `open` | owner | Second-pass tag-stripping regex breaks on > characters inside attribute … |

## Verdict

A tight HTML-to-text extractor with smart truncation, CJK awareness, and defense-in-depth against entity-encoded tags; nothing functional or security-level remains.

## Fixed since the last round

P1-1 confirmed fixed — second strip pass after html_entity_decode catches entity-encoded tags in both toText() and truncate() paths; G2 strict flags complete.

## Test gaps

No test for truncate with HTML containing only tags (no text); no test for malformed HTML with nested mismatched tags; no test for stripTags() second pass with > inside attribute values.

## Verification protocol

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: all four on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.


---

# migears-html-digest — 已知问题

> 本模块问题的概览。条目本体在 [`issues/`](issues/README.md)，一条目一文件：前置字段加讨论串。
> 本文件由条目生成，随时可以整段重写；请改条目，不要改本文件。
>
> 出自 miGears 全模块代码评审报告（5th round，2026-09-28）。

| | |
|---|---|
| 状态 | **状态最好** |
| 体量 | src 119 行（净）· 63 个用例 · 1 个源文件 |

级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## 状态一览

| | |
|---|---|
| 未了结 | P0 0 · P1 0 · P2 1 · P3 1 · 其他 0 |
| 已了结 | 4 / 6 |
| 等模块主 | `P3-3` |
| 等协调人 | _无_ |
| 等评审方 | _无_ |
| 已暂缓，不欠谁 | `P2-1` |

| id | 级别 | 状态 | 标题 |
|---|---|---|---|
| [`P1-1`](issues/P1-1.md) | P1 | **verified** | 实体编码的非隐藏标签会存活到输出：toText("<p>&lt;b&gt;bold&lt;/b&gt;</p>") → … |
| [`P2-1`](issues/P2-1.md) | P2 | **deferred** | 被实体编码的声明或处理指令会重新成形并通过再剥离：`&lt;!DOCTYPE html&gt;`、`&lt;?php … … |
| [`P3-1`](issues/P3-1.md) | P3 | **verified** | README 有 20 处 --- 分隔线，中英分界与小节横线同形，边界不显眼（内容本身中英对应是完整的）。 |
| [`P3-2`](issues/P3-2.md) | P3 | **verified** | VERSION 仍零引用；README 称「不足 150 行」「52 个测试」，而源码 160 行、套件 54 个测试方法。 |
| [`P3-3`](issues/P3-3.md) | P3 | **open** | 第二轮标签剥离正则在属性值包含 > 字符时（如 onclick="x > … |
| [`G2`](issues/G2.md) | - | **verified** | 严格开关：`phpunit.xml.dist` … |

## 未关闭

本模块还剩什么要做：所有 `status` 不是 `verified` 或 `closed` 的条目，按严重度从高到低。
`waiting on` 是下一步该动手的一方，由其状态读出。

| | |
|---|---|
| 未关闭 | **2** / 6 |
| 按状态 | `open` 1 · `deferred` 1 |
| 等在谁 | 模块主 1 · - 1 |

| 级别 | 条目 | 状态 | 等在谁 | 标题 |
|---|---|---|---|---|
| **P2** | [`P2-1`](issues/P2-1.md) | `deferred` | - | 被实体编码的声明或处理指令会重新成形并通过再剥离：`&lt;!DOCTYPE html&gt;`、`&lt;?php … … |
| **P3** | [`P3-3`](issues/P3-3.md) | `open` | 模块主 | 第二轮标签剥离正则在属性值包含 > 字符时（如 onclick="x > … |

## 结论

一个紧凑的 HTML 文本提取器，智能截断、CJK 感知、对实体编码标签有深度防御；已无功能性或安全级问题。

## 本轮已修复确认

P1-1 confirmed fixed — second strip pass after html_entity_decode catches entity-encoded tags in both toText() and truncate() paths; G2 strict flags complete.

## 测试盲区

无纯标签 HTML 的截断测试；无嵌套不匹配标签的畸形 HTML 测试；无属性内含 > 时第二轮 stripTags() 的测试。

## 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- `phpunit.xml.dist` 中的 warning/notice/deprecation/risky 开关：四个全开
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。
