---
okf_version: "0.2"
---

# scrapyard-io/scrapyard-io Knowledge Bundle

Package knowledge for the ScrapyardIO application skeleton (`scrapyard-io/scrapyard-io`, framework **0.7.x**).
Read this index first; open only the concepts needed for the task.

**Trust rule:** Prefer `status: stable`. Treat `deprecated` as historical only. Concepts below are human-verified `stable` unless marked `deprecated`.
**Placement:** Package-root `.okf/` only — not under `app/` or `vendor/`.
**Scope:** This skeleton application only. Fabricate domain rules live in `scrapyard-io/framework` OKF; Wrench rules live in `scrapyard-io/wrench` OKF.
**Deploy note:** `.okf` is intentionally **not** `export-ignore` so `composer create-project` keeps it. Add `/.okf export-ignore` to `.gitattributes` before deploying to a target if you want it omitted from deploy archives. See the project README.

# Orientation

* [Package (0.7)](orientation/package.md) - Skeleton identity, Composer deps, role vs framework. (`stable`)
