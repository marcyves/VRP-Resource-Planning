# Configuration — terminologie

**EN:** [configuration.md](../en/configuration.md)

## Variables d’environnement

| Variable | Défaut | Rôle |
|----------|--------|------|
| `APP_LOCALE` | `fr` | Langue de base (`fr`, `en`, `it`) |
| `TERMINOLOGY_PROFILE` | `education` | Profil pour invités / sans entreprise |

Exemple `.env.example` :

```env
APP_LOCALE=fr
TERMINOLOGY_PROFILE=consulting
```

> Connecté : `companies.terminology_profile` **prime** sur `TERMINOLOGY_PROFILE`.

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

- `config/terminology.php`, `config/app.php`, `.env.example`

## Liens

- [Phase 1 — terminologie](phase-1-terminologie.md)
- [Libellés consulting](libelles-consulting.md)
