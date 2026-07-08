# Configuration — terminologie

**EN:** [configuration.md](../en/configuration.md)

## Variables d’environnement

| Variable | Défaut | Rôle |
|----------|--------|------|
| `APP_LOCALE` | `fr` | Langue de base (`fr`, `en`, `it`) |
| `TERMINOLOGY_PROFILE` | `education` | Profil pour invités / sans entreprise |
| `VRP_ALLOW_REGISTRATION` | `false` | Inscription publique `/register` |

Exemple `.env.example` :

```env
APP_LOCALE=fr
TERMINOLOGY_PROFILE=consulting
VRP_ALLOW_REGISTRATION=false
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

- `config/terminology.php`, `config/app.php`, `config/vrp.php`, `.env.example`

## Liens

- [Phase 1 — terminologie](phase-1-terminologie.md)
- [Administration plateforme](administration-plateforme.md)
- [Libellés consulting](libelles-consulting.md)
- [Administration plateforme](administration-plateforme.md)
