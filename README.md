# VRP Resource Planning

**Language:** **English** · [Français](README.fr.md)

Web app for **scheduling**, **budgeting**, and **invoice tracking** for trainers, freelancers, and small practices: schools/clients, courses, groups, calendar, PDF invoices, per-site documents, and calendar import.

**Repository:** [github.com/marcyves/VRP-Resource-Planning](https://github.com/marcyves/VRP-Resource-Planning)

![Issues](https://img.shields.io/github/issues/marcyves/VRP-Resource-Planning?style=flat-square)
![License: GPL-3.0](https://img.shields.io/badge/License-GPL%20v3-blue.svg?style=flat-square)
![LinkedIn](https://img.shields.io/badge/LinkedIn-Marc%20Augier-0A66C2?style=flat-square&logo=linkedin)

---



## Contents

- [Features](#features)
- [V2 — user interface](#v2--user-interface)
- [Tech stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Development](#development)
- [Platform super admin](#platform-super-admin)
- [Internationalisation](#internationalisation)
- [Quality & tests](#quality--tests)
- [Demo](#demo)
- [Roadmap — e-invoicing](#roadmap--e-invoicing)
- [Roadmap — PWA & offline](#roadmap--pwa--offline)
- [Contributing](#contributing)
- [Licence](#licence)
- [Contact](#contact)

---



## Features

- **Schools** (or clients / care structures) and **courses** (programmes, volumes, rates)
- **Groups** and **planning** / calendar views
- **Billing preparation** on each school record (sessions, assign or create invoice)
- **School list** with billed and unbilled amounts
- **Invoices** (PDF, payment tracking) and **treasury**
- **Bank reconciliation** (XLSX imports, match invoices / expenses)
- **Documents** attached to a school
- **Calendar import** (mapping, event handling)
- Authentication and company-scoped roles (browse / edit modes)

---



## V2 — user interface

**v2** is a UI refresh (2025–2026): sidebar + topbar shell, modular CSS design system, factorised Blade components, dark mode.


| Change      | Detail                                                               |
| ----------- | -------------------------------------------------------------------- |
| **Home**    | `/home` — school list (billed TTC, unbilled TTC + hours)             |
| **Logo**    | Returns to home                                                      |
| **Billing** | Preparation moved from Agenda into **each school page** (`#billing`) |
| **Agenda**  | Planning + calendar only                                             |
| **CSS**     | Tokens in `theme.css`, `.data-table`, `.nice-form`                   |


**Detailed docs (wiki sheets):**


| Topic                          | English                                                                                   | Français                                                                                            |
| ------------------------------ | ----------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| V2 overview                    | [v2-user-interface.md](documentation/en/v2-user-interface.md)                             | [v2-interface-utilisateur.md](documentation/fr/v2-interface-utilisateur.md)                         |
| Navigation & modules           | [v2-navigation-modules.md](documentation/en/v2-navigation-modules.md)                     | [v2-navigation-modules.md](documentation/fr/v2-navigation-modules.md)                               |
| Billing per school             | [v2-billing-per-school.md](documentation/en/v2-billing-per-school.md)                     | [v2-facturation-par-ecole.md](documentation/fr/v2-facturation-par-ecole.md)                         |
| Treasury & bank reconciliation | [v2-treasury-bank-reconciliation.md](documentation/en/v2-treasury-bank-reconciliation.md) | [v2-tresorerie-rapprochement-bancaire.md](documentation/fr/v2-tresorerie-rapprochement-bancaire.md) |
| Platform administration        | [platform-administration.md](documentation/en/platform-administration.md)                 | [administration-plateforme.md](documentation/fr/administration-plateforme.md)                       |
| CSS design system              | [v2-design-system-css.md](documentation/en/v2-design-system-css.md)                       | [v2-design-system-css.md](documentation/fr/v2-design-system-css.md)                                 |


Full index: [documentation/README.md](documentation/README.md).

User manuals (LaTeX PDF): [user manual](documentation/manuel-utilisateur/README.md) · [medical quick start](documentation/manuel-prise-en-main-medical/README.md).

---



## Tech stack


| Layer    | Detail                                                               |
| -------- | -------------------------------------------------------------------- |
| Backend  | **PHP 8.2+**, **Laravel 11**                                         |
| Frontend | **Vite 4**, **Alpine.js**, modular CSS (`resources/css/`), **Blade** |
| PDF      | **TCPDF** (invoices)                                                 |
| iCal     | **ics-parser**                                                       |
| Quality  | **Laravel Pint**, **PHPStan** (Larastan), **PHPUnit**                |


> Tailwind is not an npm dependency: the UI uses dedicated CSS sheets and Blade components.

---



## Requirements

- **PHP** 8.2+ (usual Laravel extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, …)
- **Composer** 2.x
- **Node.js** + **npm** (for Vite)
- **Database**: MySQL / MariaDB (or SQLite for a quick try, with `.env` adjusted)

---



## Installation

```bash
git clone https://github.com/marcyves/VRP-Resource-Planning.git
cd VRP-Resource-Planning

composer install
cp .env.example .env
php artisan key:generate
```

1. Edit `.env`: `APP_URL`, database (`DB_*` or SQLite `DB_DATABASE`), mail if needed.
2. Create tables:
  ```bash
   php artisan migrate
  ```
3. **Storage symlink** (public files / documents):
  ```bash
   php artisan storage:link
  ```
4. Front-end assets:
  ```bash
   npm install
   npm run build
  ```

Locally you can run `npm run dev` alongside `php artisan serve` (or your vhost).

---



## Development


| Command             | Role                       |
| ------------------- | -------------------------- |
| `php artisan serve` | Laravel development server |
| `npm run dev`       | Vite watch (HMR)           |
| `npm run build`     | Production asset build     |


Route cache when needed: `php artisan route:cache` (typically production only).

---



## Platform super admin

VRP is **multi-tenant**: each customer company has its own users and data. A **super admin** account (no `company_id`) provisions companies from `/super-admin/companies`.


| Step                   | Command / action                                                             |
| ---------------------- | ---------------------------------------------------------------------------- |
| **Migrate**            | `php artisan migrate` (`super admin` status, nullable `company_id`)          |
| **Create super admin** | `php artisan vrp:create-super-admin you@example.com "Your Name"`             |
| **Sign in**            | `/login` → company list                                                      |
| **Create a tenant**    | **Create company**: name, invoice prefix, terminology profile, admin account |


Public `/register` is **disabled by default** (`VRP_ALLOW_REGISTRATION=false`). Company accounts are created by the super admin or by an existing admin in the classic VRP UI.

Runbook: [platform-administration.md](documentation/en/platform-administration.md) · [administration-plateforme.md](documentation/fr/administration-plateforme.md).

---



## Internationalisation

Translation files under `resources/lang/` (French, English, Italian).

**Business context:** each company picks a profile on the company page (`education`, `consulting`, or `medical`). Labels use dedicated locales (`fr_consulting`, `fr_medical`, …). Tables and routes (`school`, `course`, …) stay the same.

Optional env: `TERMINOLOGY_PROFILE=education` (default for guests / no company). See `.env.example`.

Wiki docs (FR/EN): [documentation/](documentation/README.md) — [en/](documentation/en/README.md) · [fr/](documentation/fr/README.md)

**V2 UI:** [en/v2-user-interface.md](documentation/en/v2-user-interface.md) · [fr/v2-interface-utilisateur.md](documentation/fr/v2-interface-utilisateur.md).

`joedixon/laravel-translation` is available to help manage strings.

---



## Quality & tests

```bash
./vendor/bin/pint              # PHP formatting (Laravel Pint)
./vendor/bin/phpstan analyse   # static analysis (per project config)
php artisan test               # PHPUnit
```

---



## Demo

A demo is available at https://**vrp.xdm-consulting.fr**.

Credential information is povided there.

---



## Roadmap — e-invoicing

**Current priority.** VRP prepares invoices (planning, PDF, legal IDs); an external **accredited platform (PA)** handles structured submission, routing, and archiving.


| Deadline           | Who                                              |
| ------------------ | ------------------------------------------------ |
| Receive e-invoices | **1 Sep 2026** — VAT-liable entities             |
| Issue              | **1 Sep 2026** (large/mid-size) · **2027** (SME) |


**Already in place:** PDF, e-invoice statuses (`draft` → `ready` → `transmitted` → `accepted` / `rejected`), SIREN/SIRET on company and clients.

**POC:** **[SuperPDP](https://www.superpdp.tech/)** adapter (PDF send). Set `E_INVOICE_PLATFORM=superpdp` and `SUPERPDP_ACCESS_TOKEN` in `.env`. See [roadmap-electronic-invoicing.md](documentation/en/roadmap-electronic-invoicing.md).

Full docs (integration spec, phases, code layout):

- [documentation/en/roadmap-electronic-invoicing.md](documentation/en/roadmap-electronic-invoicing.md)
- [documentation/fr/roadmap-facturation-electronique.md](documentation/fr/roadmap-facturation-electronique.md)

---



## Roadmap — PWA & offline

Later idea: let users **browse the agenda** (then maybe **enter sessions**) **offline**, sync when back online — via an installable **PWA**, not a native app.

**Not short-term** (API + local storage + conflict handling). Today: online only; `.ics` calendar import is the only external → VRP flow.


| Phase | Goal                                       |
| ----- | ------------------------------------------ |
| 0     | PWA shell (manifest, icons, install)       |
| 1     | Offline agenda browse (**MVP**)            |
| 2     | Offline entry + sync queue                 |
| 3     | Billing, treasury, documents → online only |


Docs: [roadmap-pwa-offline.md](documentation/en/roadmap-pwa-offline.md) · [fr](documentation/fr/roadmap-pwa-offline.md)

---



## Contributing

Suggestions and pull requests are welcome:

1. Fork the repo
2. Create a branch (`feature/...` or `fix/...`)
3. Clear commits, focused PR with a short description
4. Run Pint / tests when relevant

---



## Licence

Distributed under **GNU GPLv3** — see `[LICENSE](./LICENSE)`.

---



## Contact

**Marc Augier** — [@marcyves](https://github.com/marcyves) · [LinkedIn](https://linkedin.com/in/marcaugier)

If this project helps you, you can support the work:
![Buy Me A Coffee](https://cdn.buymeacoffee.com/buttons/v2/default-blue.png)

---

*README for **VRP-Resource-Planning** — Laravel 11 / Vite, **v2 UI**. French translation: [README.fr.md](README.fr.md).*