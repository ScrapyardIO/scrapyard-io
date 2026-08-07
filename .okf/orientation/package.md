---
type: Orientation
title: Package (0.7)
description: scrapyard-io/scrapyard-io — application skeleton for ScrapyardIO framework 0.7.x.
resource: .
tags: [orientation, skeleton, scrapyard-io, 0.7]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-07T07:50:00Z" }
verified: { by: "human:Angel Gonzalez (projectsaturnstudios)", at: "2026-08-07T07:50:00Z" }
status: stable
sources:
  - id: composer
    resource: composer.json
    title: Project name, require, scripts, path repositories
  - id: readme
    resource: README.md
    title: Skeleton README (docs redirect + OKF deploy note)
  - id: gitattributes
    resource: .gitattributes
    title: export-ignore list (no /.okf by default)
---

# What it is

Composer project `scrapyard-io/scrapyard-io` — the application skeleton for ScrapyardIO framework **0.7.x**.[^composer]

| Field | Value |
|-------|-------|
| Name | `scrapyard-io/scrapyard-io` |
| Type | `project` |
| PHP | `^8.4\|^8.5\|^8.6` |
| Framework | `scrapyard-io/framework` `^0.7.0` |
| REPL | `scrapyard-io/wrench` `^0.7.0` |
| App namespace | `App\` → `app/` |

This tree is for building apps — not the Fabricate core. Core code lives in [`scrapyard-io/framework`](https://github.com/ScrapyardIO/framework); docs live on the [ScrapyardIO website](https://scrapyard-io.projectsaturnstudios.com/docs).[^readme]

# `.okf` in create-project vs deploy

Unlike library packages (`framework`, `wrench`), this skeleton does **not** mark `/.okf` as `export-ignore`.[^gitattributes] Agents and humans keep the knowledge bundle when creating a project.

When deploying the app to a target (for example an SBC), add:

```gitattributes
/.okf export-ignore
```

so deploy archives omit the knowledge bundle. Details are in the project README.[^readme]

# What belongs elsewhere

- Fabricate components, dependency direction, MagicAliases → `scrapyard-io/framework` OKF
- Wrench REPL provider / casters → `scrapyard-io/wrench` OKF
- Chip/GPIO drivers → companion packages

[^composer]: See source `composer`.
[^readme]: See source `readme`.
[^gitattributes]: See source `gitattributes`.
