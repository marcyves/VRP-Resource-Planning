# Roadmap — PWA & offline mode

**FR:** [roadmap-pwa-offline.md](../fr/roadmap-pwa-offline.md)

> **Status:** idea captured, **not scheduled** in the short term (high complexity vs immediate benefit).

## User context

A trainer or contractor may want to:

- view their **schedule** without connectivity (commute, room with no Wi‑Fi);
- **add or edit** a session on the go, then sync with the online site;
- use VRP as an **app on the phone home screen**.

Today, VRP is a **central web application**: all data lives on the server. There is **no** sync between a local instance and an online site, and no offline mode.

## Current state

| Capability | Available |
|------------|-----------|
| Browser access (desktop / mobile) | Yes |
| PWA install (manifest, service worker) | No |
| Offline read | No |
| Offline write + sync | No |
| External calendar import (`.ics` / URL) | Yes — **external → VRP** only, not bidirectional |

Calendar import is a useful complement (prepare in Google/Apple Calendar, import into VRP) but does not replace real offline sync.

## Why it is deferred

- The current UI is **Blade pages** served by Laravel: every action needs the network.
- Real offline requires a **JSON API**, **local storage** (IndexedDB), and a **sync strategy** (conflicts, multi-user, invoiced sessions).
- Full business scope (PDF invoices, bank reconciliation, documents) is hard to support offline.

## Technical direction (when the time comes)

**PWA first** (reuse the existing site), not a native iOS/Android app in the first step.

```
Browser
  ├─ Service Worker  → asset cache (CSS, JS, icons)
  ├─ IndexedDB       → local copy (plannings, schools, groups)
  └─ Sync queue      → offline actions replayed when back online

Laravel server
  └─ REST API (Sanctum) → pull changes / push queue
```

### Envisioned phases

| Phase | Scope | Business priority |
|-------|--------|-------------------|
| **0 — PWA shell** | `manifest.webmanifest`, icons, service worker, “Add to home screen” | Low effort, limited value alone |
| **1 — Offline read** | Cached schedule (week / month), “Offline — last sync at …” banner | **Recommended MVP** |
| **2 — Offline write** | Create / edit / delete / duplicate a session; sync queue | High value, sync complexity |
| **3 — Out of offline scope** | Billing, `.ics` import, treasury, document upload | Online only |

### Watch points

- **Conflicts**: same session edited offline and online, or by two users.
- **Billing**: keep existing guards (`invoice_id`, blocked deletion).
- **Security**: clear local cache on logout; sensitive data on device.
- **iOS**: offline PWA works but with limits (evicted storage, notifications).

## Rough effort (indicative)

| Phase | Effort |
|-------|--------|
| PWA shell | 1–2 days |
| Schedule JSON API | 3–5 days |
| Offline read | 1–2 weeks |
| Write + sync | 2–4 weeks |

## Current alternatives for users

1. Work **directly on the online site** (same account, multiple devices).
2. Prepare the schedule in an **external calendar**, then **import** the `.ics` into VRP.
3. Do not treat a **local VRP instance** as a draft that syncs with production.

## Links

- [Repository README — e-invoicing roadmap](../../README.md#roadmap--facturation-électronique)
- [V2 — navigation & modules](v2-navigation-modules.md) (Schedule module)
- Calendar import: routes `admin/calendars/*`, `CalendarFileController`
