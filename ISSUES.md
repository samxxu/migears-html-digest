# migears-html-digest — Known Issues / 已知问题

> Summary of this module's issues. The items themselves are in [`issues/`](issues/README.md), one file
> per item: a front-matter header and a thread. This file is generated from them and can be rewritten at
> any time; edit an item, never this file.
>
> 本模块问题的概览。条目本体在 [`issues/`](issues/README.md)，一条目一文件：前置字段加讨论串。
> 本文件由条目生成，随时可以整段重写；请改条目，不要改本文件。
>
> From the miGears Full-Module Code Review Report (4th round, 2026-09-27).

| | |
|---|---|
| Status / 状态 | **P1 open / P1 待修** |
| Size / 体量 | src 169 lines (117 net) · 54 tests · 2 src files |

Legend / 图例 — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs
级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## At a glance / 状态一览

| | |
|---|---|
| Items / 条目 | P0 0 · P1 1 · P2 0 · P3 2 · other 1 |
| Answered / 已回复 | 2 of 4 |
| Waiting / 等待回复 | `P3-1`, `P3-2` |

| id | level | status | title |
|---|---|---|---|
| [`P1-1`](issues/P1-1.md) | P1 | **new-evidence** | Entity-encoded non-hidden tags survive into the output: … |
| [`P3-1`](issues/P3-1.md) | P3 | **open** | The README contains 20 `---` rules; the bilingual divider is the same … |
| [`P3-2`](issues/P3-2.md) | P3 | **open** | `VERSION` still has zero references, and the README says 'under 150 … |
| [`G2`](issues/G2.md) | - | **fixed** | Strict flags: `phpunit.xml.dist` currently sets none of the five. The … |

## Verdict / 结论

The hidden-element leak is closed, but the general promise is not: the second strip only covers script/style/head/noscript, so entity-encoded *other* tags survive the decode and reach the output. The README's tag-free guarantee therefore still does not hold, and this one has security weight.

隐藏元素的泄漏已堵住，但通用承诺仍不成立：二次剥除只覆盖 script/style/head/noscript，实体编码的**其它**标签会在解码后存活并进入输出。README 的「无标签」保证因此仍不成立，且这一条有安全含义。

## Fixed since the last round / 本轮已修复确认

上一轮 P1-2（全角标点使 CJK 逐字计数退化）已修：CJK 字符类含全角形式区并有专门用例；P3-1（测试把「解码出 script」当期望）也已改成与实现一致的断言；隐藏元素（script/style/head/noscript）的二次剥除已加。 

## Test gaps / 测试盲区

No test asserts tag-freeness in general (only the script case), which is exactly how this survived; no check that the README line and test counts stay true.

没有任何用例断言「整体无标签」（只测了 script 一例），这正是它存活至今的原因；也无对 README 行数与测试数的元数据校验。

## Verification protocol / 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: all four on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。
