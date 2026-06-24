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
| **Hub écoles** | Page d’accueil = liste des écoles avec indicateurs facturé / non facturé |
| **Facturation contextualisée** | Préparation sur `school/show`, plus dans l’agenda |

## Fiches détaillées

| Sujet | Fiche |
|-------|--------|
| Navigation, accueil, modules | [v2-navigation-modules.md](v2-navigation-modules.md) |
| Facturation par école | [v2-facturation-par-ecole.md](v2-facturation-par-ecole.md) |
| Trésorerie et rapprochement bancaire | [v2-tresorerie-rapprochement-bancaire.md](v2-tresorerie-rapprochement-bancaire.md) |
| Design system CSS | [v2-design-system-css.md](v2-design-system-css.md) |
| Revue de code & refactor listes | [v2-revue-code-refactoring-listes.md](v2-revue-code-refactoring-listes.md) |

## Parcours utilisateur type

1. **Connexion** → `/home` (liste des écoles)
2. Consulter **facturé TTC** et **non facturé** par établissement
3. Ouvrir une **école** → cours, adresse, factures, documents, **préparation facturation**
4. Naviguer mois par mois ou **sauter aux sessions non facturées** (retour arrière)
5. **Agenda** (menu latéral) pour saisir / modifier les sessions
6. **Trésorerie** (menu latéral) pour les factures, le rapprochement bancaire, les dépenses et le suivi financier global

## Compatibilité

- Anciennes URLs `/billing*` : redirection vers l’école en session ou la liste des écoles
- Route `/dashboard` : alias vers `/home`
- Profils terminologiques (`education` / `consulting`) : inchangés — voir [phase-1-terminologie.md](phase-1-terminologie.md)

## Voir aussi

- [README du dépôt](../../README.md#v2--interface-utilisateur)
- [Parcours de création](parcours-creation-ecole.md)
