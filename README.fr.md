# VRP Resource Planning

**Langue :** [English](README.md) · **Français**

Application web de **planification**, **budgétisation** et **suivi facturation** pour formateurs, vacataires et petites structures : écoles, cours, groupes, agenda, factures PDF, documents par établissement, import calendrier.

**Dépôt :** [github.com/marcyves/VRP-Resource-Planning](https://github.com/marcyves/VRP-Resource-Planning)

![Issues](https://img.shields.io/github/issues/marcyves/VRP-Resource-Planning?style=flat-square)
![License: GPL-3.0](https://img.shields.io/badge/License-GPL%20v3-blue.svg?style=flat-square)
![LinkedIn](https://img.shields.io/badge/LinkedIn-Marc%20Augier-0A66C2?style=flat-square&logo=linkedin)

---



## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [V2 — interface utilisateur](#v2--interface-utilisateur)
- [Stack technique](#stack-technique)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Développement](#développement)
- [Super administrateur plateforme](#super-administrateur-plateforme)
- [Internationalisation](#internationalisation)
- [Qualité & tests](#qualité--tests)
- [Démo](#démo)
- [Roadmap — facturation électronique](#roadmap--facturation-électronique)
- [Roadmap — PWA & mode hors ligne](#roadmap--pwa--mode-hors-ligne)
- [Contribution](#contribution)
- [Licence](#licence)
- [Contact](#contact)

---



## Fonctionnalités

- Gestion des **écoles** et des **cours** (programmes, volumes, tarifs)
- **Groupes** et vue **planning** / calendrier
- **Préparation facturation** sur la fiche de chaque école (sessions, assignation, création facture)
- **Liste écoles** avec montants facturés et non facturés
- **Factures** (PDF, suivi paiement) et **trésorerie**
- **Rapprochement bancaire** (imports XLSX, rapprochements factures / dépenses)
- **Documents** rattachés à une école
- **Import calendrier** (mapping, gestion des évènements)
- Authentification, rôles utilisateur liés à l’entreprise (mode lecture / édition)

---



## V2 — interface utilisateur

La **v2** apporte une refonte de l’interface (2025–2026) : coque sidebar + topbar, design system CSS modulaire, composants Blade factorisés, mode sombre.


| Changement      | Détail                                                               |
| --------------- | -------------------------------------------------------------------- |
| **Accueil**     | `/home` — liste des écoles (facturé TTC, non facturé TTC + heures)   |
| **Logo**        | Retour à l’accueil (`home`)                                          |
| **Facturation** | Préparation déplacée du module Agenda vers **chaque fiche école**    |
| **Agenda**      | Planning + calendrier uniquement                                     |
| **CSS**         | Tokens `theme.css`, tableaux `.data-table`, formulaires `.nice-form` |


**Documentation détaillée (fiches wiki) :**


| Sujet                               | Français                                                                                            | English                                                                                   |
| ----------------------------------- | --------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------- |
| Vue d’ensemble v2                   | [v2-interface-utilisateur.md](documentation/fr/v2-interface-utilisateur.md)                         | [v2-user-interface.md](documentation/en/v2-user-interface.md)                             |
| Navigation & modules                | [v2-navigation-modules.md](documentation/fr/v2-navigation-modules.md)                               | [v2-navigation-modules.md](documentation/en/v2-navigation-modules.md)                     |
| Import calendrier (ICS)             | [import-calendrier.md](documentation/fr/import-calendrier.md)                                       | [calendar-import.md](documentation/en/calendar-import.md)                                 |
| Facturation par école               | [v2-facturation-par-ecole.md](documentation/fr/v2-facturation-par-ecole.md)                         | [v2-billing-per-school.md](documentation/en/v2-billing-per-school.md)                     |
| Trésorerie & rapprochement bancaire | [v2-tresorerie-rapprochement-bancaire.md](documentation/fr/v2-tresorerie-rapprochement-bancaire.md) | [v2-treasury-bank-reconciliation.md](documentation/en/v2-treasury-bank-reconciliation.md) |
| Administration plateforme           | [administration-plateforme.md](documentation/fr/administration-plateforme.md)                       | [platform-administration.md](documentation/en/platform-administration.md)                 |
| Design system CSS                   | [v2-design-system-css.md](documentation/fr/v2-design-system-css.md)                                 | [v2-design-system-css.md](documentation/en/v2-design-system-css.md)                       |


Index complet : [documentation/README.md](documentation/README.md).

Manuels utilisateur (PDF LaTeX) : [manuel-utilisateur](documentation/manuel-utilisateur/README.md) · [prise en main médical](documentation/manuel-prise-en-main-medical/README.md).

---



## Stack technique


| Couche   | Détail                                                                 |
| -------- | ---------------------------------------------------------------------- |
| Backend  | **PHP 8.2+**, **Laravel 11** (image Sail locale : **PHP 8.4**)         |
| Frontend | **Vite 4**, **Alpine.js**, CSS modulaire (`resources/css/`), **Blade** |
| PDF      | **TCPDF** (factures)                                                   |
| iCal     | **ics-parser**                                                         |
| Qualité  | **Laravel Pint**, **PHPStan** (Larastan), **PHPUnit**                  |


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

1. Éditer `.env` : `APP_URL`, connexion base (`DB_*` ou `DB_DATABASE` pour SQLite), mail si besoin.
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

**Laravel Sail (Docker) :** `docker-compose.yml` construit `vendor/laravel/sail/runtimes/8.4` (`image: sail-8.4/app`). Le code applicatif reste compatible PHP 8.2+. Après `composer install` :

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run dev
```

Préférer `./vendor/bin/sail test` pour que les tests Feature ciblent la base MySQL `testing` créée par `create-testing-database.sh`.

---



## Développement


| Commande                  | Rôle                             |
| ------------------------- | -------------------------------- |
| `php artisan serve`       | Serveur de développement Laravel |
| `./vendor/bin/sail up -d` | Stack Docker (image PHP **8.4**) |
| `npm run dev`             | Vite en mode watch (HMR)         |
| `npm run build`           | Build de production des assets   |


Penser à régénérer le cache des routes si besoin : `php artisan route:cache` (production uniquement, en général).

---



## Super administrateur plateforme

VRP est **multi-tenant** : chaque entreprise cliente a ses utilisateurs et ses données. Un compte **super admin** (sans `company_id`) gère le provisionnement des entreprises depuis `/super-admin/companies`.


| Étape                    | Commande / action                                                                    |
| ------------------------ | ------------------------------------------------------------------------------------ |
| **Migration**            | `php artisan migrate` (statut `super admin`, `company_id` nullable)                  |
| **Créer le super admin** | `php artisan vrp:create-super-admin vous@example.com "Votre Nom"`                    |
| **Connexion**            | `/login` → redirection vers la liste des entreprises                                 |
| **Créer un client**      | **Créer une entreprise** : nom, préfixe facture, profil terminologique, compte admin |


L’inscription publique `/register` est **désactivée par défaut** (`VRP_ALLOW_REGISTRATION=false`). Les comptes entreprise sont créés par le super admin ou par un admin existant dans l’UI VRP classique. Les invités peuvent **demander** un compte sur `/demande-acces` (e-mail seulement — renseigner `VRP_ACCOUNT_REQUEST_EMAIL`).

Runbook détaillé : [documentation/fr/administration-plateforme.md](documentation/fr/administration-plateforme.md) · [documentation/en/platform-administration.md](documentation/en/platform-administration.md).

---



## Internationalisation

Fichiers de traduction sous `resources/lang/` (français, anglais, italien).

**Contexte métier** : chaque entreprise choisit un profil sur la fiche société (`education`, `consulting` ou `medical`). Les libellés passent par les locales dédiées (`fr_consulting`, `fr_medical`, …). Les tables et routes (`school`, `course`, …) restent inchangées.

Variable d’environnement optionnelle : `TERMINOLOGY_PROFILE=education` (défaut pour les invités / sans entreprise). Voir `.env.example`.

Documentation détaillée (fiches wiki, FR/EN) : [documentation/](documentation/README.md) — [fr/](documentation/fr/README.md) · [en/](documentation/en/README.md)

**V2 interface :** [fr/v2-interface-utilisateur.md](documentation/fr/v2-interface-utilisateur.md) · [en/v2-user-interface.md](documentation/en/v2-user-interface.md).

Le paquet `joedixon/laravel-translation` est présent pour faciliter la gestion des chaînes.

---



## Qualité & tests

```bash
./vendor/bin/pint              # formatage PHP (Laravel Pint)
./vendor/bin/phpstan analyse   # analyse statique (selon config du projet)
php artisan test               # PHPUnit
php artisan test --testsuite=Unit   # sans base
```

`phpunit.xml` fixe `DB_DATABASE=testing` mais **hérite `DB_CONNECTION` du `.env`**. La migration `2026_06_02_120000_add_short_description_to_programs_table` est idempotente (garde `Schema::hasColumn`). Préférer `./vendor/bin/sail test`.

Mail de demande de compte : `tests/Feature/LandingPageTest.php` (nécessite une DB migrable).

---



## Démo

Une démo est accessible à [https://**vrp.xdm-consulting.fr](https://vrp.xdm-consulting.fr)**

Les informations de connexion sont disponibles en ligne.

---

## Roadmap — facturation électronique

**Priorité actuelle.** VRP prépare les factures (planning, PDF, identifiants légaux) ; une **Plateforme agréée (PA)** externe gère émission structurée, routage et archivage.


| Échéance             | Qui                                              |
| -------------------- | ------------------------------------------------ |
| Réception e-factures | **1ᵉʳ sept. 2026** — assujettis TVA              |
| Émission             | **1ᵉʳ sept. 2026** (GE/ETI) · **2027** (PME/TPE) |


**Déjà en place :** PDF, statuts e-facture (`draft` → `ready` → `transmitted` → `accepted` / `rejected`), SIREN/SIRET sur société et clients.

**POC :** adaptateur **[SuperPDP](https://www.superpdp.tech/)** — **CII → Factur-X** structuré (pas le PDF TCPDF). Configurer `E_INVOICE_PLATFORM=superpdp` et les credentials OAuth (`SUPERPDP_CLIENT_ID` / `SUPERPDP_CLIENT_SECRET`, ou équivalents sandbox). `SUPERPDP_ACCESS_TOKEN` optionnel (sans OAuth).

Runbook opérationnel : [facturation-electronique.md](documentation/fr/facturation-electronique.md) · [electronic-invoicing.md](documentation/en/electronic-invoicing.md).

Documentation complète (spec d’intégration, phases, structure de code) :

- [documentation/fr/roadmap-facturation-electronique.md](documentation/fr/roadmap-facturation-electronique.md)
- [documentation/en/roadmap-electronic-invoicing.md](documentation/en/roadmap-electronic-invoicing.md)

---



## Roadmap — PWA & mode hors ligne

Idée retenue pour plus tard : permettre à l’utilisateur de **consulter l’agenda** (puis éventuellement **saisir des séances**) **sans connexion**, avec synchronisation au retour du réseau — via une **PWA** installable sur mobile, plutôt qu’une app native.

**Non planifié à court terme** (API + stockage local + gestion des conflits). Aujourd’hui : site en ligne uniquement ; l’import calendrier `.ics` reste le seul flux externe → VRP.


| Phase | Objectif                                                |
| ----- | ------------------------------------------------------- |
| 0     | Shell PWA (manifest, icônes, installation)              |
| 1     | Agenda consultable offline (**MVP**)                    |
| 2     | Saisie offline + file de synchro                        |
| 3     | Facturation, trésorerie, documents → en ligne seulement |


Documentation détaillée : [documentation/fr/roadmap-pwa-offline.md](documentation/fr/roadmap-pwa-offline.md) · [documentation/en/roadmap-pwa-offline.md](documentation/en/roadmap-pwa-offline.md)

---



## Contribution

Les suggestions et *pull requests* sont les bienvenues :

1. Forkez le dépôt
2. Créez une branche (`feature/...` ou `fix/...`)
3. Commits clairs, PR ciblée avec description courte
4. Vérifiez Pint / tests quand c’est pertinent

---



## Licence

Distribué sous **GNU GPLv3** — voir le fichier `[LICENSE](./LICENSE)`.

---



## Contact

**Marc Augier** — [@marcyves](https://github.com/marcyves) · [LinkedIn](https://linkedin.com/in/marcaugier)

Si le projet vous est utile, vous pouvez soutenir le travail :
![Buy Me A Coffee](https://cdn.buymeacoffee.com/buttons/v2/default-blue.png)

---

*Version française du [README](README.md) — dépôt **VRP-Resource-Planning**, stack Laravel 11 / Vite, **v2 interface utilisateur**.*