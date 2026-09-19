# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

**Primary (confirmed):** the unauthenticated prospect on the public home (`/`) who decides whether to request an account. Design work is for this person: landing, hero, login, and “Demander un compte”.

The signed-in practitioner (trainer, consultant, care practice) and the XDM super-admin **exist in the product** but are **out of scope for design priority**. Do not treat `/home`, agenda, or treasury as the surfaces to optimize unless the user explicitly reopens this.

## Product Purpose

VRP Plan is a multi-tenant web app for **planning**, **billing**, and **treasury**. It exists so a small professional structure can run its activity in one place: clients (schools / structures), programmes, courses, groups, agenda, PDF invoices, bank reconciliation, and per-client documents.

Success (design): the prospect understands the offer and sends an account request (or signs in if they already have one). Operational success for signed-in users remains a product fact, not a design-priority surface.

## Positioning

A neighbouring planner or invoicing tool cannot truthfully claim this combination: **isolated companies**, **business vocabulary that switches** (education / consulting / medical) without changing data, and **billing prepared on the client record** (not a generic invoice module detached from the school). Public self-registration is off by default; XDM opens the tenant.

## Operating Context

- Local: Laravel Sail (Docker); production: SFTP IONOS.
- Default locale `fr`; also `en` and `it`.
- Guest terminology comes from `TERMINOLOGY_PROFILE`; a signed-in company uses `companies.terminology_profile`.
- Daily loop: home school list (billed / unbilled TTC) → school sheet (courses, groups, billing, documents) → agenda → treasury.
- Account opening: `/demande-acces` (mail), not `/register`.
- Evidence of work: PDF invoices (TCPDF), ICS import, bank XLSX imports, SuperPDP / Factur-X when configured.

## Capabilities and Constraints

- Auth, company-scoped data, super-admin provisioning (`/super-admin/companies`).
- Schools/clients, programmes, courses (volumes, HT rates), groups, planning, calendar import.
- Invoices (PDF, payment tracking), treasury, bank reconciliation, expenses.
- Electronic invoicing via SuperPDP is optional (`E_INVOICE_PLATFORM`); not a claim that every tenant is live on Factur-X.
- UI v2: sidebar + topbar, modular CSS (no Tailwind npm), Blade + Alpine, light/dark.
- `VRP_ALLOW_REGISTRATION=false` by default.
- Stack in repo: PHP ≥ 8.2 / Laravel 11, Vite 4, MySQL. Dev PHP in Sail is 8.4.

Undecided: no product-specific accessibility standard (e.g. RGAA) was confirmed.

## Brand Commitments

- Product name: **VRP Plan** (`APP_NAME`).
- Editor: **XDM Consulting** — Marc Augier.
- Licence: GPL-3.0.
- Public chrome: **do not use** `public/images/VRP.jpeg` as the header mark. Wordmark / product name is enough unless a replacement mark is supplied.
- Landing illustration still on hand: `public/images/VRP-login.jpg` (not a mandatory lock-in for every hero treatment).
- Voice: professional French (and EN/IT strings); no invented slogans beyond existing `messages.landing_*` copy.

## Evidence on Hand

- Public copy: `resources/views/welcome.blade.php`, `resources/lang/fr/messages.php` (`landing_*`).
- Product wiki: `documentation/fr/`, `documentation/en/`.
- User manuals (LaTeX): `documentation/manuel-utilisateur/`, `documentation/manuel-prise-en-main-medical/`.
- Demo mentioned in README; do not fabricate testimonials, customer logos, conversion metrics, or pricing.

## Product Principles

1. **Design serves the public prospect.** Signed-in workflows stay functional but are not the visual brief unless reopened.
2. **One company, one vocabulary** — labels follow the tenant profile; data stays the same.
3. **Billing lives on the client**, not as a detached afterthought.
4. **Tenants are provisioned, not self-served** unless registration is explicitly turned on.
5. **Do not invent social proof** — only real copy, manuals, and assets in the repo.
