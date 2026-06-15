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

## Internationalisation

Fichiers de traduction sous `resources/lang/` (français, anglais, italien).

**Contexte métier (formation vs clients/projets)** : chaque entreprise choisit un profil sur la fiche société (`education` ou `consulting`). Les libellés à l’écran passent alors par les locales `fr_consulting`, `en_consulting`, etc. (écoles → clients, programmes → projets, cours → phases). Les tables et routes (`school`, `course`, …) restent inchangées.

Variable d’environnement optionnelle : `TERMINOLOGY_PROFILE=education` (défaut pour les invités / sans entreprise). Voir `.env.example`.

Documentation détaillée (fiches wiki, FR/EN) : [documentation/](documentation/README.md) — [fr/](documentation/fr/README.md) · [en/](documentation/en/README.md).

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

La réforme de la **facturation électronique B2B** en France impose des factures **structurées** (pas seulement un PDF) et leur circulation via une **plateforme de dématérialisation partenaire (PDP)** ou, pour le secteur public, **Chorus Pro**.

Aujourd’hui, VRP couvre la **préparation métier** et l’**émission PDF** (TCPDF). La conformité au nouveau modèle passera par une couche e-facture et, en pratique, par une **PDP** pour l’émission, la réception et l’archivage probant.

### Échéances réglementaires (indicatives)

| Obligation | Date |
|------------|------|
| Réception des factures électroniques (toutes entreprises assujetties à la TVA) | **1ᵉʳ septembre 2026** |
| Émission — grandes entreprises et ETI | **1ᵉʳ septembre 2026** |
| Émission — PME, TPE, micro-entreprises | **1ᵉʳ septembre 2027** |

### État actuel dans l’application

- Génération et stockage de **factures PDF** (`InvoiceService`, `InvoiceGenerator`)
- Lien **planning ↔ facture** (préparation facturation, rattachement des sessions)
- Données facture : identifiant, date, montant, école, suivi du paiement
- Identifiants légaux (TVA, SIREN) encore **codés en dur** dans le générateur PDF — à externaliser vers les fiches entreprise / client
- Pas encore : SIREN/SIRET en base, formats structurés (Factur-X, UBL, CII), intégration PDP, statuts de cycle de vie e-facture

### Options d’architecture cible

| Option | Rôle de VRP | PDP |
|--------|-------------|-----|
| **A** (recommandée pour TPE/PME) | Préparation métier + PDF lisible | Émission, transmission, archivage légal |
| **B** | Génération Factur-X / UBL + envoi API | Réception des flux et conformité |
| **C** | Suivi uniquement (numéro, montant, statut) | Facturation complète hors VRP |

Schéma cible (option A ou B) :

```
Planning / billing → Préparation facture → PDF (visualisation)
                              ↓
                    Factur-X ou API PDP → Archivage légal
                              ↓
                    Statuts (déposée, acceptée, rejetée) → VRP
```

### Phases de développement prévues

**Phase 1 — Fondations (sans rupture)** ✅ *en place*  
- Porter TVA, SIREN, SIRET dans `companies` et les clients (`schools` pour le B2B)  
- Retirer les valeurs légales en dur du générateur PDF  
- Statut e-facture sur `invoices` (brouillon → prête → transmise → acceptée / rejetée)  
- Écran **Mon entreprise → Identifiants légaux** pour renseigner l’émetteur

**Phase 2 — Réception (échéance 2026)**  
- Connexion PDP en réception (webhooks ou polling)  
- Affichage des statuts légaux dans l’écran factures, en complément du suivi « payée »

**Phase 3 — Émission (échéance 2027)**  
- Export **Factur-X** (PDF + XML CII) ou UBL à partir des lignes de facture existantes  
- Envoi vers la PDP après création de facture  
- Archivage probant principalement côté PDP

**Phase 4 — Qualité**  
- Jeux de tests (TVA 20 %, avoirs, numérotation)  
- Alignement de la numérotation sur les exigences d’inaltérabilité et de suite chronologique (souvent gérées par la PDP en production)

### Données à prévoir

| Zone | Champs types |
|------|----------------|
| Émetteur (`companies`) | SIREN, SIRET siège, TVA intracommunautaire, adresse légale |
| Client (`schools`) | SIREN/SIRET si B2B, adresse, TVA le cas échéant |
| Facture | Numéro unique, date d’émission, lignes HT / TVA / TTC, conditions de paiement |
| Cycle de vie | Statut e-facture, identifiant PDP, horodatage, motif de rejet |

### Actions immédiates (hors code)

1. Qualifier le profil entreprise (TVA, taille, clients publics vs privés).  
2. Choisir une **PDP** et tester une facture pilote.  
3. Distinguer les clients : **Chorus Pro** (public), **PDP B2B** (privé avec SIREN), hors périmètre structuré (particuliers).  
4. Migrer les données légales (SIREN, TVA) avant septembre 2026.

> Le seul PDF généré par VRP ne suffit pas, à lui seul, pour la conformité B2B du nouveau modèle ; la PDP (ou un outil certifié qui s’y connecte) reste le pivot de la transition.

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
