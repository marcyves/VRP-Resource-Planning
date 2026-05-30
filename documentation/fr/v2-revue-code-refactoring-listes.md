# V2 — Revue de code et refactorisation des listes

**EN:** [v2-code-review-list-refactoring.md](../en/v2-code-review-list-refactoring.md)

Document de synthèse : revue globale du dépôt (focus listes groupes / programmes / écoles), pistes d’amélioration, et **étapes déjà réalisées** dans le cadre de la refonte cartes + grilles.

## Contexte

La refonte des index **groupes** et **programmes** visait à :

- Extraire des cartes Blade réutilisables (`group-card`, `program-card`)
- Aligner la présentation sur le shell `.card-content` (`cards.css`)
- Réduire la duplication CSS, JS et i18n

Les **écoles** utilisaient déjà une grille en cartes ; les changements les intègrent dans le même système `.resource-grid`.

---

## Revue de code — constats principaux

### Points positifs

| Élément | Détail |
|---------|--------|
| Composants Blade | `group-table` → `group-card` → `group-table-sessions` : bon découpage |
| Shell cartes | `.card-content`, `.card-content-text`, `.card-content-end` partagés |
| Modales index | Alpine + confirmation avant suppression (groupes, programmes, planning) |
| i18n | Fichiers `resources/lang/{en,fr}/messages.php` + profils `*_consulting` par fusion |

### Doublons repérés (avant refactor)

| Zone | Problème |
|------|----------|
| Grilles CSS | `.group-grid`, `.program-grid`, `.school-grid` + doublon dans `semantic.css` |
| Stores Alpine | `groupDelete`, `programDelete`, `planningDelete` quasi identiques |
| Modales Blade | Markup de confirmation copié sur 3 index |
| i18n suppression | 6 clés (`*_delete_confirm_title/description`) pour 3 entités |
| Cartes | `group-card` / `program-card` / en-tête école — même coquille |
| Index programmes | Boucle `@foreach` inline vs `<x-group-table>` |
| Pages détail | `group/show` réutilise les classes CSS `program-*` |
| `semantic.css` | Règles larges (`main section > ul > li`) en conflit avec hovers domaine |

### Asymétrie groupes / programmes

| Aspect | Groupes | Programmes |
|--------|---------|------------|
| Wrapper liste | `<x-group-table>` | Boucle inline (corrigé → `program-table`) |
| Inactifs / recherche | Oui | Non |
| Création | `<x-form-group-create>` | Formulaire inline |
| Scope multi-tenant | `User::getGroups()` | `Program::all()` (à valider métier) |
| Destroy show | POST direct | POST direct (index = modal) |

### Complexité / dette (non traitée)

- `Auth::user()->getMode() == 'Edit'` répété dans ~20 vues → candidat `@editMode` Blade
- `GroupController::store()` : branchements difficiles (`course_id == 0`, etc.)
- Deux systèmes de modales (Alpine `<x-modal>` + legacy Bootstrap dans `app.css`)
- Entrées CSS multiples dans `vite.config.js` alors que seul `app.css` est chargé au runtime
- Typo propagée : `occurences` / `getGroupOccurences`
- jQuery modal encore sur `school/show` vs Alpine ailleurs

---

## Étapes réalisées

### 1 — Suppression : store Alpine + modal générique

**Objectif :** une factory JS et un composant Blade pour toutes les confirmations de suppression.

| Fichier | Rôle |
|---------|------|
| `resources/js/delete-store.js` | `createDeleteStore(modalName, fields)` |
| `resources/js/app.js` | Instancie `planningDelete`, `programDelete`, `groupDelete` |
| `resources/views/components/confirm-delete-modal.blade.php` | Modal paramétrable (`store`, `entity`, `hints`) |

**Vues mises à jour :** `group/index`, `program/index`, `planning/index`.

**API inchangée pour les cartes :**

```js
$store.groupDelete.request(url, name)
$store.programDelete.request(url, name)
$store.planningDelete.request(url, label, date)
```

**Hints modal :** champ avec label (`name`, `date`) ou `plain: true` (libellé planning seul).

---

### i18n — Clés unifiées pour les modales de suppression

| Avant | Après |
|-------|--------|
| `group_delete_confirm_title` + `program_*` + `planning_*` (×2 champs) | `delete_confirm_title` (unique) |
| 3 descriptions séparées par entité | `delete_confirm_description_{group\|program\|session}` |

Le composant `<x-confirm-delete-modal>` prend `entity="group|program|session"` et résout les traductions en interne.

Les locales `en_consulting` / `fr_consulting` héritent via `array_merge` de `en` / `fr` — pas de duplication nécessaire.

---

### 2 — Grille CSS partagée `.resource-grid`

**Objectif :** une seule source pour les listes en cartes (écoles, groupes, programmes).

| Fichier | Changement |
|---------|------------|
| `resources/css/cards.css` | Ajout `.resource-grid`, `.resource-grid--inactive`, breakpoints, hover |
| `groups.css`, `programs.css`, `schools.css` | Suppression des règles `*-grid` dupliquées |
| `semantic.css` | Retrait du bloc `school-grid` ; `:not(.resource-grid)` pour les listes flex |

**Vues :**

- `group-table.blade.php` → `resource-grid` / `resource-grid--inactive`
- `program/index.blade.php`
- `school/index.blade.php` (actif + inactif)

**Classes retirées :** `group-grid`, `program-grid`, `school-grid`, `group-grid--inactive`, `school-grid--inactive`.

---

### 3 — Composant `<x-program-table>`

**Objectif :** parité avec `<x-group-table>`.

| Fichier | Rôle |
|---------|------|
| `resources/views/components/program-table.blade.php` | Boucle `programs` + `<x-program-card>` |
| `resources/views/program/index.blade.php` | `<x-program-table :programs="$programs" />` |

Props : `programs`, `active` (défaut `true`) — prêt pour une future section « programmes inactifs » (`:active="false"`).

---

## Fichiers de référence (état actuel)

| Rôle | Chemin |
|------|--------|
| Carte groupe | `resources/views/components/group-card.blade.php` |
| Carte programme | `resources/views/components/program-card.blade.php` |
| Table groupe | `resources/views/components/group-table.blade.php` |
| Table programme | `resources/views/components/program-table.blade.php` |
| Modal suppression | `resources/views/components/confirm-delete-modal.blade.php` |
| Store JS | `resources/js/delete-store.js`, `resources/js/app.js` |
| Grille CSS | `resources/css/cards.css` (`.resource-grid`) |
| Index | `resources/views/{group,program,school}/index.blade.php` |

---

## Pistes restantes (par priorité)

| # | Piste | Impact |
|---|--------|--------|
| 4 | Filtrer `Program` par `company_id` (si requis) | Multi-tenant |
| 5 | `<x-resource-card>` avec slots stats / actions | Moins de duplication cartes |
| 6 | Layout détail neutre (`entity-detail-*`) | `group/show` + `program/show` |
| 7 | `@editMode` Blade | ~20 vues |
| 8 | UX delete homogène (modal vs POST sur show) | Cohérence |
| 9 | `<x-form-program-create>` | 3 formulaires programmes |
| 10 | Nettoyage CSS (modales legacy, stats unifiés, Vite) | Maintenance |
| 11 | Corriger `occurences` → `occurrences` | Nommage |
| 12 | `@formatDateTime` ou accessor pour sessions | Dates |

---

## Voir aussi

- [V2 — design system CSS](v2-design-system-css.md) — tokens, `.resource-grid`, build Vite
- [V2 — interface utilisateur](v2-interface-utilisateur.md) — vue d’ensemble UI
- [Index documentation](../README.md)
