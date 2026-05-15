# VRP Resource Planning

Application web de **planification**, **budgétisation** et **suivi facturation** pour formateurs, vacataires et petites structures : écoles, cours, groupes, agenda, factures PDF, documents par établissement, import calendrier.

**Dépôt :** [github.com/marcyves/VRP-Resource-Planning](https://github.com/marcyves/VRP-Resource-Planning)

[![Issues](https://img.shields.io/github/issues/marcyves/VRP-Resource-Planning?style=flat-square)](https://github.com/marcyves/VRP-Resource-Planning/issues)
[![License: GPL-3.0](https://img.shields.io/badge/License-GPL%20v3-blue.svg?style=flat-square)](./LICENSE)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-Marc%20Augier-0A66C2?style=flat-square&logo=linkedin)](https://linkedin.com/in/marcaugier)

---

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [Stack technique](#stack-technique)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Développement](#développement)
- [Internationalisation](#internationalisation)
- [Qualité & tests](#qualité--tests)
- [Démo](#démo)
- [Contribution](#contribution)
- [Licence](#licence)
- [Contact](#contact)

---

## Fonctionnalités

- Gestion des **écoles** et des **cours** (programmes, volumes, tarifs)
- **Groupes** et vue **planning** / facturation
- **Factures** (PDF, suivi paiement)
- **Documents** rattachés à une école
- **Import calendrier** (mapping, gestion des évènements)
- Authentification, rôles utilisateur liés à l’entreprise (mode lecture / édition)

---

## Stack technique

| Couche        | Détail |
|---------------|--------|
| Backend       | **PHP 8.2+**, **Laravel 11** |
| Frontend      | **Vite 4**, **Alpine.js**, CSS modulaire (`resources/css/`), **Blade** |
| PDF           | **TCPDF** (factures) |
| iCal          | **ics-parser** |
| Qualité       | **Laravel Pint**, **PHPStan** (Larastan), **PHPUnit** |

> Le dépôt n’embarque pas Tailwind en dépendance npm : l’UI repose sur des feuilles CSS dédiées et des composants Blade.

---

## Prérequis

- **PHP** 8.2 ou supérieur (extensions habituelles Laravel : `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, etc.)
- **Composer** 2.x  
- **Node.js** + **npm** (pour Vite)  
- **Base de données** : MySQL / MariaDB (ou SQLite pour un essai rapide, en adaptant `.env`)

---

## Installation

```bash
git clone https://github.com/marcyves/VRP-Resource-Planning.git
cd VRP-Resource-Planning

composer install
cp .env.example .env
php artisan key:generate
```

1. Éditer **`.env`** : `APP_URL`, connexion base (`DB_*` ou `DB_DATABASE` pour SQLite), mail si besoin.  
2. Créer les tables :

   ```bash
   php artisan migrate
   ```

3. **Lien symbolique** du stockage (si vous servez des fichiers publics / documents) :

   ```bash
   php artisan storage:link
   ```

4. Assets front :

   ```bash
   npm install
   npm run build
   ```

En local, vous pouvez utiliser `npm run dev` en parallèle d’un serveur PHP (`php artisan serve` ou votre vhost).

---

## Développement

| Commande | Rôle |
|----------|------|
| `php artisan serve` | Serveur de développement Laravel |
| `npm run dev` | Vite en mode watch (HMR) |
| `npm run build` | Build de production des assets |

Penser à régénérer le cache des routes si besoin : `php artisan route:cache` (production uniquement, en général).

---

## Internationalisation

Fichiers de traduction sous `resources/lang/` (français, anglais, italien, variante métier `en_proj`, etc.).  
Le paquet `joedixon/laravel-translation` est présent pour faciliter la gestion des chaînes.

---

## Qualité & tests

```bash
./vendor/bin/pint          # formatage PHP (Laravel Pint)
./vendor/bin/phpstan analyse   # analyse statique (selon config du projet)
php artisan test           # PHPUnit
```

---

## Démo

Une démo peut être accessible (ex. **vrp.xdm-consulting.fr**) ; les identifiants de test ne doivent **pas** figurer en clair dans le dépôt — utilisez un canal privé ou des secrets d’environnement.

---

## Contribution

Les suggestions et *pull requests* sont les bienvenues :

1. Forkez le dépôt  
2. Créez une branche (`feature/...` ou `fix/...`)  
3. Commits clairs, PR ciblée avec description courte  
4. Vérifiez Pint / tests quand c’est pertinent  

---

## Licence

Distribué sous **GNU GPLv3** — voir le fichier [`LICENSE`](./LICENSE).

---

## Contact

**Marc Augier** — [@marcyves](https://github.com/marcyves) · [LinkedIn](https://linkedin.com/in/marcaugier)

Si le projet vous est utile, vous pouvez soutenir le travail :  
[![Buy Me A Coffee](https://cdn.buymeacoffee.com/buttons/v2/default-blue.png)](https://www.buymeacoffee.com/marcyves)

---

*README mis à jour pour refléter le dépôt **VRP-Resource-Planning** et la stack Laravel 11 / Vite.*
