# V2 — Interface utilisateur

**EN:** [v2-user-interface.md](../en/v2-user-interface.md)

## Contexte

La **v2** modernise l’expérience de VRP Resource Planning : coque applicative unifiée, design system CSS, composants Blade factorisés, et réorganisation de la **préparation de facturation** (déplacée du module Agenda vers le détail de chaque école).

Cette refonte est **purement présentationnelle et navigationnelle** : le modèle de données, les routes métier (`school`, `course`, `planning`, …) et la logique facture PDF restent les mêmes.

## Principes directeurs

| Principe | Détail |
|----------|--------|
| **CSS modulaire** | Feuilles dédiées par domaine (`resources/css/`), tokens dans `theme.css` / `global.css` — pas de Tailwind en dépendance npm |
| **Composants Blade** | En-têtes de module, tableaux, formulaires (`nice-form`), KPI, sélecteur de période |
| **Navigation latérale** | Sidebar 260 px, topbar, fil d’Ariane, mode sombre |
| **Hub écoles** | `/home` = liste des écoles avec indicateurs facturé / non facturé |
| **Facturation contextualisée** | Le menu **Facturer** est la liste du mois ; la préparation d’un client reste sur `school/show?panel=billing` |
| **Canvas public** | Accueil, produit, tarifs, à propos et mentions partagent le layout marketing — [design system CSS](v2-design-system-css.md#canvas-marketing-public) |

## Fiches détaillées

| Sujet | Fiche |
|-------|--------|
| Navigation, accueil, modules | [v2-navigation-modules.md](v2-navigation-modules.md) |
| Import calendrier (ICS) | [import-calendrier.md](import-calendrier.md) |
| Facturation par école | [v2-facturation-par-ecole.md](v2-facturation-par-ecole.md) |
| Trésorerie et rapprochement bancaire | [v2-tresorerie-rapprochement-bancaire.md](v2-tresorerie-rapprochement-bancaire.md) |
| Facturation électronique | [facturation-electronique.md](facturation-electronique.md) |
| Design system CSS | [v2-design-system-css.md](v2-design-system-css.md) |
| Revue de code & refactor listes | [v2-revue-code-refactoring-listes.md](v2-revue-code-refactoring-listes.md) |

## Parcours utilisateur type

Les invités commencent sur `/` et peuvent ouvrir `/fonctionnalites`, `/tarifs`, `/a-propos` et `/mentions-legales`, ou changer de langue via `/langue/{fr|en|it}`. L’ouverture de compte est `/demande-acces`. Une visite connectée de `/` va vers `User::homePath()`.

1. **Connexion** → `User::homePath()` (`/home` pour un utilisateur d’entreprise). `RouteServiceProvider::HOME` (`/planning`) est la cible de l’inscription et de la vérification d’e-mail, pas celle du mot de passe — voir [navigation](v2-navigation-modules.md#page-daccueil).
2. Consulter **facturé TTC** et **non facturé** par établissement, ou ouvrir **Agenda** / **Facturer** dans le menu.
3. **Facturer** liste les clients qui ont des séances non facturées pour le mois de l’agenda, puis **Préparer** ouvre le panneau facturation de cette école.
4. Sur la fiche, naviguer mois par mois ou **sauter aux sessions non facturées** (retour arrière).
5. **Agenda** pour saisir / modifier les sessions — vue **mois** ou **semaine**, clic sur un jour ou un créneau (fin préremplie depuis la durée standard du cours), duplication vers demain / la semaine suivante. L’import ICS est **Importer un calendrier** ([import-calendrier.md](import-calendrier.md), [formulaires de séance](v2-navigation-modules.md#formulaire-nouvelle-séance)).
6. **Finance** (factures, dépenses, banque) pour la liste des factures (émission e-facture si SuperPDP est configuré), les dépenses et le rapprochement bancaire.

## Compatibilité

- Anciennes URLs `/billing*` : redirection vers l’école en session ou la liste des écoles
- Route `/dashboard` : alias vers `/home`
- Profils terminologiques (`education` / `consulting`) : inchangés — voir [phase-1-terminologie.md](phase-1-terminologie.md)

## Voir aussi

- [README du dépôt](../../README.md#v2--interface-utilisateur)
- [Parcours de création](parcours-creation-ecole.md)
