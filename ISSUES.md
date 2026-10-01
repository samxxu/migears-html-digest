# migears-html-digest — Known Issues

> Summary of this module's issues. The items themselves are in [`issues/`](issues/README.md), one file
> per item: a front-matter header and a thread. This file is generated from them and can be rewritten at
> any time; edit an item, never this file.
>
> From the miGears Full-Module Code Review Report (6th round, 2026-10-01).

| | |
|---|---|
| Status | **Best state** |
| Size | src 119 lines (net) · 68 tests · 2 src files |

Legend — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs

## At a glance

| | |
|---|---|
| Unsettled | P0 0 · P1 0 · P2 1 · P3 0 · other 0 |
| Settled | 5 of 6 |
| Waiting on the owner | _nothing_ |
| Waiting on the coordinator | _nothing_ |
| Waiting on the reviewer | _nothing_ |
| Deferred, owing nobody | `P2-1` |

| id | level | status | title |
|---|---|---|---|
| [`P1-1`](issues/P1-1.md) | P1 | **verified** | Entity-encoded non-hidden tags survive into the output: … |
| [`P2-1`](issues/P2-1.md) | P2 | **deferred** | An entity-encoded declaration or processing instruction re-forms and … |
| [`P3-1`](issues/P3-1.md) | P3 | **verified** | The README contains 20 `---` rules; the bilingual divider is the same … |
| [`P3-2`](issues/P3-2.md) | P3 | **verified** | `VERSION` still has zero references, and the README says 'under 150 … |
| [`P3-3`](issues/P3-3.md) | P3 | **verified** | Second-pass tag-stripping regex breaks on > characters inside attribute … |
| [`G2`](issues/G2.md) | - | **verified** | Strict flags: `phpunit.xml.dist` currently sets none of the five. The … |

## Unclosed

What is left to do here: every item whose `status` is not `verified` or `closed`,
highest severity first. `waiting on` is the party who acts next, read from that status.

| | |
|---|---|
| Unclosed | **1** of 6 |
| By status | `deferred` 1 |
| Waiting on | - 1 |

| level | item | status | waiting on | title |
|---|---|---|---|---|
| **P2** | [`P2-1`](issues/P2-1.md) | `deferred` | - | An entity-encoded declaration or processing instruction re-forms and … |

## Verdict

The tag-free guarantee now holds through the second strip as well, and the declared-limitation edge is reproduced and written down in both halves.

## Fixed since the last round

P3-3 verified by mutation: the second-pass strip now consumes quoted runs, so an attribute value containing > no longer ends it early. Restoring the [^>]* form turns the module’s own test red.

## Test gaps

No stress case for very long attributes or deeply nested unclosed tags; the MODE_WORD fallback under mixed whitespace is thinly covered; an unterminated <!-- comment has no test.

## Verification protocol

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: all four on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.


---

# migears-html-digest — 已知问题

> 本模块问题的概览。条目本体在 [`issues/`](issues/README.md)，一条目一文件：前置字段加讨论串。
> 本文件由条目生成，随时可以整段重写；请改条目，不要改本文件。
>
> 出自 miGears 全模块代码评审报告（6th round，2026-10-01）。

| | |
|---|---|
| 状态 | **状态最好** |
| 体量 | src 119 行（净）· 68 个用例 · 2 个源文件 |

级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## 状态一览

| | |
|---|---|
| 未了结 | P0 0 · P1 0 · P2 1 · P3 0 · 其他 0 |
| 已了结 | 5 / 6 |
| 等模块主 | _无_ |
| 等协调人 | _无_ |
| 等评审方 | _无_ |
| 已暂缓，不欠谁 | `P2-1` |

| id | 级别 | 状态 | 标题 |
|---|---|---|---|
| [`P1-1`](issues/P1-1.md) | P1 | **verified** | 实体编码的非隐藏标签会存活到输出：toText("<p>&lt;b&gt;bold&lt;/b&gt;</p>") → … |
| [`P2-1`](issues/P2-1.md) | P2 | **deferred** | 被实体编码的声明或处理指令会重新成形并通过再剥离：`&lt;!DOCTYPE html&gt;`、`&lt;?php … … |
| [`P3-1`](issues/P3-1.md) | P3 | **verified** | README 有 20 处 --- 分隔线，中英分界与小节横线同形，边界不显眼（内容本身中英对应是完整的）。 |
| [`P3-2`](issues/P3-2.md) | P3 | **verified** | VERSION 仍零引用；README 称「不足 150 行」「52 个测试」，而源码 160 行、套件 54 个测试方法。 |
| [`P3-3`](issues/P3-3.md) | P3 | **verified** | 第二轮标签剥离正则在属性值包含 > 字符时（如 onclick="x > … |
| [`G2`](issues/G2.md) | - | **verified** | 严格开关：`phpunit.xml.dist` … |

## 未关闭

本模块还剩什么要做：所有 `status` 不是 `verified` 或 `closed` 的条目，按严重度从高到低。
`waiting on` 是下一步该动手的一方，由其状态读出。

| | |
|---|---|
| 未关闭 | **1** / 6 |
| 按状态 | `deferred` 1 |
| 等在谁 | - 1 |

| 级别 | 条目 | 状态 | 等在谁 | 标题 |
|---|---|---|---|---|
| **P2** | [`P2-1`](issues/P2-1.md) | `deferred` | - | 被实体编码的声明或处理指令会重新成形并通过再剥离：`&lt;!DOCTYPE html&gt;`、`&lt;?php … … |

## 结论

二次剥除之后「无标签」的保证同样成立，被声明为限制的那处边界已复现且写进两半文档。

## 本轮已修复确认

P3-3 verified by mutation: the second-pass strip now consumes quoted runs, so an attribute value containing > no longer ends it early. Restoring the [^>]* form turns the module’s own test red.

## 测试盲区

无超长属性或深层未闭合标签的压力用例；MODE_WORD 在空白混排下的回退覆盖很薄；未闭合的 <!-- 注释无用例。

## 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- `phpunit.xml.dist` 中的 warning/notice/deprecation/risky 开关：四个全开
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。
