# Mise en production SFTP (IONOS / xdm VRP)

**EN:** [production-sftp-deploy.md](../en/production-sftp-deploy.md)

## Commande

```bash
cd ~/Projects/VRP-Resource-Planning
./scripts/deploy-xdm-vrp.sh --dry-run   # liste les fichiers
./scripts/deploy-xdm-vrp.sh --upload    # build + route:cache + SFTP
```

Credentials : variables `VRP_SFTP_*` ou site FileZilla **`xdm VRP`**.

## Ce que le script envoie

| Toujours | Diff git depuis `.deploy-base-ref` |
|----------|-------------------------------------|
| `public/build/**` | `app/`, `resources/`, `routes/`, `config/` |
| `bootstrap/cache/routes-v7.php` | `database/migrations/`, `public/` (hors build) |

Après un `--upload` réussi, `.deploy-base-ref` est mis à jour sur le HEAD local.

## Interdit — ne jamais faire

Ces erreurs ont déjà cassé la prod :

| Action | Effet |
|--------|--------|
| **Supprimer** `bootstrap/cache/config.php` sur le serveur | Laravel retombe sur un `.env` incomplet → **BDD HS** |
| Uploader `bootstrap/cache/config.php` depuis le Mac | Écrase les credentials prod avec un cache local |
| Uploader `services.php` / `packages.php` / `events.php` | Cache providers local ≠ prod |
| `rsync --delete` sur `VRP/` | Peut effacer storage, cache, fichiers serveur |
| Contourner le script avec un rsync « à la main » sans checklist | Oublie les garde-fous |

Le script **refuse** d’uploader ces chemins et n’utilise **jamais** `--delete`.

### Fichiers protégés

- `bootstrap/cache/config.php` — cache config **prod** (MySQL, APP_KEY, etc.)
- `bootstrap/cache/prod.config.php` — **copie de secours locale** du cache prod (repo / poste de deploy)
- `bootstrap/cache/services.php`, `packages.php`, `events.php`
- `.env` / `.env.*`

Seul fichier autorisé sous `bootstrap/cache/` à l’upload : **`routes-v7.php`**.

## Restauration d’urgence (config.php effacé)

Sur le poste de deploy :

```bash
cd ~/Projects/VRP-Resource-Planning
cp bootstrap/cache/prod.config.php bootstrap/cache/config.php
# rsync UNIQUEMENT ce fichier vers VRP/bootstrap/cache/config.php
rm bootstrap/cache/config.php   # ne pas laisser le cache prod trainer en local
```

Ne pas régénérer `config:cache` en local pour l’envoyer en prod (ce serait la config **locale**).

## Base de données

1. Préparer / exécuter les **ALTER / migrations** sur la BDD prod **avant ou en même temps** que le code.
2. Ce script **ne lance pas** `php artisan migrate` sur le serveur.
3. Uploader le fichier de migration sert de doc ; l’appliquer à la main (phpMyAdmin / client SQL).

## Checklist agent / humain

1. `./scripts/deploy-xdm-vrp.sh --dry-run` — relire la liste
2. Confirmer qu’aucun chemin `bootstrap/cache/` sauf `routes-v7.php` n’apparaît
3. Migrations BDD déjà faites (ou planifiées)
4. `./scripts/deploy-xdm-vrp.sh --upload`
5. Smoke test : login, liste factures, une fiche école

## Erreurs fréquentes et correctifs

| Symptôme | Cause probable | Correctif |
|----------|----------------|-----------|
| Connexion BDD impossible après FTP | `config.php` cache effacé / écrasé | Restaurer depuis `prod.config.php` |
| `Class … does not exist` | Code jamais dans le diff (base trop récente) / pas uploadé | Inclure les fichiers manquants via le script (étendre la base ou commit) |
| `Target [Contract] is not instantiable` | Provider pas dans le cache `services.php` prod | Ne pas uploader un `services.php` local ; enregistrer le provider dans un provider déjà chargé (`AppServiceProvider`) **ou** mettre à jour `services.php` **sur le serveur** avec `artisan package:discover` (si SSH artisan dispo) |
| Vue OK, ancienne logique | OPcache / navigateur | Soft refresh ; attendre quelques secondes |

## Voir aussi

- [AGENTS.md](../../AGENTS.md) — rappel court pour les agents
- Script : [`scripts/deploy-xdm-vrp.sh`](../../scripts/deploy-xdm-vrp.sh)
