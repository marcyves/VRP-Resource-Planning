# VRP Resource Planning

Application web de **planification**, **budgétisation** et **suivi facturation** pour formateurs, vacataires et petites structures : écoles, cours, groupes, agenda, factures PDF, documents par établissement, import calendrier.

**Dépôt :** [github.com/marcyves/VRP-Resource-Planning](https://github.com/marcyves/VRP-Resource-Planning)

[![Issues](https://img.shields.io/github/issues/marcyves/VRP-Resource-Planning?style=flat-square)](https://github.com/marcyves/VRP-Resource-Planning/issues)
[![License: GPL-3.0](https://img.shields.io/badge/License-GPL%20v3-blue.svg?style=flat-square)](./LICENSE)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-Marc%20Augier-0A66C2?style=flat-square&logo=linkedin)](https://linkedin.com/in/marcaugier)

---

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [V2 — interface utilisateur](#v2--interface-utilisateur)
- [Stack technique](#stack-technique)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Développement](#développement)
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

| Changement | Détail |
|------------|--------|
| **Accueil** | `/home` — liste des écoles (facturé TTC, non facturé HT + heures) |
| **Logo** | Retour à l’accueil (`home`) |
| **Facturation** | Préparation déplacée du module Agenda vers **chaque fiche école** |
| **Agenda** | Planning + calendrier uniquement |
| **CSS** | Tokens `theme.css`, tableaux `.data-table`, formulaires `.nice-form` |

**Documentation détaillée (fiches wiki) :**

| Sujet | Français | English |
|-------|----------|---------|
| Vue d’ensemble v2 | [v2-interface-utilisateur.md](documentation/fr/v2-interface-utilisateur.md) | [v2-user-interface.md](documentation/en/v2-user-interface.md) |
| Navigation & modules | [v2-navigation-modules.md](documentation/fr/v2-navigation-modules.md) | [v2-navigation-modules.md](documentation/en/v2-navigation-modules.md) |
| Facturation par école | [v2-facturation-par-ecole.md](documentation/fr/v2-facturation-par-ecole.md) | [v2-billing-per-school.md](documentation/en/v2-billing-per-school.md) |
| Trésorerie & rapprochement bancaire | [v2-tresorerie-rapprochement-bancaire.md](documentation/fr/v2-tresorerie-rapprochement-bancaire.md) | [v2-treasury-bank-reconciliation.md](documentation/en/v2-treasury-bank-reconciliation.md) |
| Administration plateforme | [administration-plateforme.md](documentation/fr/administration-plateforme.md) | [platform-administration.md](documentation/en/platform-administration.md) |
| Design system CSS | [v2-design-system-css.md](documentation/fr/v2-design-system-css.md) | [v2-design-system-css.md](documentation/en/v2-design-system-css.md) |

Index complet : [documentation/README.md](documentation/README.md).

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

## Super administrateur plateforme

VRP est **multi-tenant** : chaque entreprise cliente a ses utilisateurs et ses données. Un compte **super admin** (sans `company_id`) gère le provisionnement des entreprises depuis `/super-admin/companies`.

| Étape | Commande / action |
|-------|---------------------|
| **Migration** | `php artisan migrate` (statut `super admin`, `company_id` nullable) |
| **Créer le super admin** | `php artisan vrp:create-super-admin vous@example.com "Votre Nom"` |
| **Connexion** | `/login` → redirection vers la liste des entreprises |
| **Créer un client** | **Créer une entreprise** : nom, préfixe facture, profil terminologique, compte admin |

L’inscription publique `/register` est **désactivée par défaut** (`VRP_ALLOW_REGISTRATION=false`). Les comptes entreprise sont créés par le super admin ou par un admin existant dans l’UI VRP classique.

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
./vendor/bin/pint          # formatage PHP (Laravel Pint)
./vendor/bin/phpstan analyse   # analyse statique (selon config du projet)
php artisan test           # PHPUnit
```

---

## Démo

Une démo peut être accessible (ex. **vrp.xdm-consulting.fr**) ; les identifiants de test ne doivent **pas** figurer en clair dans le dépôt — utilisez un canal privé ou des secrets d’environnement.

---

## Roadmap — facturation électronique

**Priorité actuelle.** VRP prépare les factures (planning, PDF, identifiants légaux) ; une **Plateforme agréée (PA)** externe gère émission structurée, routage et archivage.

| Échéance | Qui |
|----------|-----|
| Réception e-factures | **1ᵉʳ sept. 2026** — assujettis TVA |
| Émission | **1ᵉʳ sept. 2026** (GE/ETI) · **2027** (PME/TPE) |

**Déjà en place :** PDF, statuts e-facture (`draft` → `ready` → `transmitted` → `accepted` / `rejected`), SIREN/SIRET sur société et clients.

**POC :** adaptateur **[SuperPDP](https://www.superpdp.tech/)** implémenté (envoi PDF). Configurer `E_INVOICE_PLATFORM=superpdp` et `SUPERPDP_ACCESS_TOKEN` dans `.env`. Voir [documentation/fr/roadmap-facturation-electronique.md](documentation/fr/roadmap-facturation-electronique.md#configuration-superpdp-poc).

Documentation complète (spec d’intégration, phases, structure de code) :

- [documentation/fr/roadmap-facturation-electronique.md](documentation/fr/roadmap-facturation-electronique.md)
- [documentation/en/roadmap-electronic-invoicing.md](documentation/en/roadmap-electronic-invoicing.md)

---

## Roadmap — PWA & mode hors ligne

Idée retenue pour plus tard : permettre à l’utilisateur de **consulter l’agenda** (puis éventuellement **saisir des séances**) **sans connexion**, avec synchronisation au retour du réseau — via une **PWA** installable sur mobile, plutôt qu’une app native.

**Non planifié à court terme** (API + stockage local + gestion des conflits). Aujourd’hui : site en ligne uniquement ; l’import calendrier `.ics` reste le seul flux externe → VRP.

| Phase | Objectif |
|-------|----------|
| 0 | Shell PWA (manifest, icônes, installation) |
| 1 | Agenda consultable offline (**MVP**) |
| 2 | Saisie offline + file de synchro |
| 3 | Facturation, trésorerie, documents → en ligne seulement |

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

Distribué sous **GNU GPLv3** — voir le fichier [`LICENSE`](./LICENSE).

---

## Contact

**Marc Augier** — [@marcyves](https://github.com/marcyves) · [LinkedIn](https://linkedin.com/in/marcaugier)

Si le projet vous est utile, vous pouvez soutenir le travail :  
[![Buy Me A Coffee](https://cdn.buymeacoffee.com/buttons/v2/default-blue.png)](https://www.buymeacoffee.com/marcyves)

---

*README mis à jour pour refléter le dépôt **VRP-Resource-Planning**, la stack Laravel 11 / Vite, et la **v2 interface utilisateur**.*
