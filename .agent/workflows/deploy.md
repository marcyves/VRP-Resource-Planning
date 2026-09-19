---
description: Deployment steps for production (SFTP IONOS)
---

# Mise en production VRP

**Procédure SFTP réelle (IONOS)** — source de vérité :

- FR : [documentation/fr/mise-en-production-sftp.md](../../documentation/fr/mise-en-production-sftp.md)
- Script : `./scripts/deploy-xdm-vrp.sh --dry-run` puis `--upload`

## Rappels critiques

- Ne **jamais** toucher `bootstrap/cache/config.php` sur le serveur (BDD).
- Ne **jamais** uploader le cache local `services.php` / `config.php`.
- Migrations SQL à la main sur la BDD prod.
- Pas de `rsync --delete`.

## Checklist rapide

// turbo

```bash
./scripts/deploy-xdm-vrp.sh --dry-run
./scripts/deploy-xdm-vrp.sh --upload
```

Vérifier login + liste factures + une fiche école après upload.
