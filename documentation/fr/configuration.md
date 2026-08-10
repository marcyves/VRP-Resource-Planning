# Configuration — terminologie

**EN:** [configuration.md](../en/configuration.md)

## Variables d’environnement

| Variable | Défaut | Rôle |
|----------|--------|------|
| `APP_LOCALE` | `fr` | Langue de base (`fr`, `en`, `it`) |
| `TERMINOLOGY_PROFILE` | `education` | Profil pour invités / sans entreprise |
| `VRP_ALLOW_REGISTRATION` | `false` | Inscription publique `/register` |
| `E_INVOICE_PLATFORM` | _(absent)_ | `superpdp` active l’adaptateur SuperPDP ; sinon Null |
| `SUPERPDP_ENV` | `production` | Choisit credentials OAuth production ou sandbox |
| `SUPERPDP_CLIENT_ID` / `SUPERPDP_CLIENT_SECRET` | — | Application OAuth production |
| `SUPERPDP_SANDBOX_CLIENT_ID` / `SUPERPDP_SANDBOX_CLIENT_SECRET` | — | Application OAuth sandbox |
| `SUPERPDP_ACCESS_TOKEN` | — | Bearer optionnel (sans OAuth) |
| `SUPERPDP_WEBHOOK_SECRET` | — | Secret HMAC pour `/webhooks/e-invoice/superpdp` |

Exemple `.env.example` :

```env
APP_LOCALE=fr
TERMINOLOGY_PROFILE=consulting
VRP_ALLOW_REGISTRATION=false
# E_INVOICE_PLATFORM=superpdp
```

> Connecté : `companies.terminology_profile` **prime** sur `TERMINOLOGY_PROFILE`.

## Super administrateur plateforme

1. `php artisan migrate`
2. `php artisan vrp:create-super-admin admin@example.com "Super Admin"`
3. Connexion → `/super-admin/companies` — création entreprise + administrateur

Le super admin n'a pas d'entreprise rattachée ; les utilisateurs métier ont un `company_id` obligatoire.

Runbook détaillé : [Administration plateforme](administration-plateforme.md).

## Fiche entreprise (UI)

1. Admin ou éditeur
2. **Mon entreprise** → **Modifier**
3. **Contexte métier** : Formation ou Clients & projets
4. Enregistrer

## Instance mono-métier

| Besoin | Réglage |
|--------|---------|
| Consulting FR | `APP_LOCALE=fr` + profil `consulting` |
| Consulting EN | `APP_LOCALE=en` + profil `consulting` |
| Formation | profil `education` |

## Fichiers

- `config/terminology.php`, `config/app.php`, `config/vrp.php`, `config/electronic-invoicing.php`, `.env.example`

## Liens

- [Phase 1 — terminologie](phase-1-terminologie.md)
- [Administration plateforme](administration-plateforme.md)
- [Facturation électronique (ops)](facturation-electronique.md)
- [Libellés consulting](libelles-consulting.md)
