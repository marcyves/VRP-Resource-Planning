# Import calendrier (ICS)

**EN:** [calendar-import.md](../en/calendar-import.md)

Guide opérationnel du flux **calendrier externe → VRP**. Les vues d’agenda, la duplication et la facturation restent dans [navigation](v2-navigation-modules.md) et [facturation par école](v2-facturation-par-ecole.md).

## Intention

L’opérateur dépose un fichier `.ics` ou une URL ICS sur une **école**. VRP extrait les libellés uniques, l’opérateur associe chaque libellé à un **cours et un groupe**, puis les sessions sont créées dans `plannings`. Les correspondances sont mémorisées **par école** pour les imports suivants.

Flux unidirectionnel : VRP n’exporte pas de flux ICS.

## Où ça vit

Agenda (sidebar) → onglet **Calendrier** (`scheduling-module-tabs`).

| Route | Nom | Action |
|-------|-----|--------|
| `GET /admin/calendars` | `calendar.index` | Formulaire d’upload + historique |
| `POST /admin/calendars/upload` | `calendar.upload` | Analyse fichier/URL, puis écran de mapping |
| `POST /admin/calendars/import` | `calendar.import` | Enregistre les mappings et crée les sessions |
| `POST /admin/calendars/reimport/{source}` | `calendar.reimport` | Relance avec les mappings stockés (champ summary) |
| `DELETE /admin/calendars/delete/{source}` | `calendar.destroy` | Supprime la ligne `calendar_sources` |

Middleware `auth` + `tenant`. Boutons **Réimporter** / **Supprimer** uniquement en mode **Édition**.

```text
Agenda → Calendrier
  POST /admin/calendars/upload
    → CalendarFileController::upload
        → CalendarService::getUniqueLabelsFromIcs (SUMMARY)
        → vue calendar.mapping
  POST /admin/calendars/import
    → CalendarMapping::updateOrCreate (école + libellé)
    → CalendarService::executeFinalImport
        → Planning::create (billable_rate = 1)
```

## Parcours

1. Ouvrir **Agenda → Calendrier**.
2. Choisir l’**école de destination**.
3. Téléverser un `.ics` **ou** coller une URL ICS publique (la validation n’exige pas les deux — en fournir un).
4. **Analyser** ouvre l’écran de mapping avec un événement exemple (titre, horaires, lieu, description).
5. Laisser le champ ICS sur **Titre (Summary)** sauf si la clé de lookup correspond réellement aux lignes de mapping (voir contraintes).
6. Pour chaque libellé détecté, choisir un **cours** (tarification) **et** un **groupe**. Les lignes sans ni l’un ni l’autre sont ignorées.
7. **Valider et importer**. Le flash indique `created` vs `skipped` (chevauchement déjà présent, ou non mappé).
8. Les sessions apparaissent sur les grilles mois/semaine comme des saisies manuelles.

Table d’historique : date, école, nom de fichier ou lien « flux distant », plus réimport / suppression en mode Édition.

## Mapping et persistance

| Élément | Comportement |
|---------|--------------|
| Extraction des libellés | Valeurs uniques du **SUMMARY** ICS (`CalendarService::getUniqueLabelsFromIcs`) |
| Lookup à l’import | `$event[$ics_source_field]` (défaut formulaire : summary ; options : description, location) |
| UI de mapping | Cours = cours de l’école ; groupes = `$user->getGroups()` (groupes de l’entreprise, pas filtrés par école) |
| Mapping stocké | Un morph par `(school_id, ics_label)` : **cours s’il est renseigné, sinon groupe** (`CalendarMapping`) |
| Premier import | Utilise les tableaux du formulaire (`course_id` + `group_id`) donc les deux IDs peuvent s’appliquer ensemble |
| Réimport | Reconstruit les mappings depuis `CalendarMapping` seulement, et force le champ `summary` |

`CalendarMatcher` (heuristique « le nom contient… ») **n’est pas** branché sur cet écran.

## Règles de création (`executeFinalImport`)

Parseur : `u01jmg3/ics-parser` (`ICal`), fuseau **Europe/Paris**, `defaultSpan` **2** (fenêtre d’expansion des récurrences).

| Règle | Détail |
|-------|--------|
| Paire obligatoire | `course_id` **et** `group_id` doivent être résolus. Ligne groupe seul → premier cours lié au groupe. Ligne cours seul → **ignorée**. |
| Collision | Ignore s’il existe déjà une session du même `group_id` qui chevauche `[begin, end)`. |
| Lieu | Copié depuis `LOCATION`. |
| Taux | `billable_rate` stocké à **1** (durée complète). Le tarif horaire du cours n’est **pas** copié dans `billable_rate`. |
| FK source | Le payload de create inclut `calendar_source_id`, mais `Planning::$fillable` **ne le contient pas** : Eloquent l’ignore. Les sessions importées ne sont pas liées à la source pour un cascade delete. |

`Tools::billableMultiplier()` traite encore un `billable_rate` égal au tarif horaire du cours comme **1** (anciens imports qui stockaient le tarif par erreur). Les valeurs `> 1` qui ne sont pas ce tarif sont des pourcentages (`/ 100`).

## Contraintes et pièges

- **Cours et groupe** sont tous les deux requis pour insérer une ligne de planning. L’UI libelle le groupe « optionnel » ; l’importeur ignore pourtant les lignes cours seul.
- **Réimporter après un mapping cours** (le morph stocké privilégie le cours) ignore en pratique tous les événements, faute de `group_id`. Le réimport n’est fiable que si le morph stocké est un **groupe** (le premier cours lié est alors déduit).
- Passer le **champ ICS** sur description/location alors que les lignes de mapping viennent des **SUMMARY** fait rater les clés → tout est ignoré.
- La validation d’upload laisse `ics_file` et `ics_url` `nullable`. Toujours en fournir un.
- Destroy supprime la ligne `calendar_sources`. Il tente `Storage::delete('calendars/'.$filename)`, qui n’est pas le `storage_path` haché écrit à l’upload : le fichier sous `storage/app/calendars/` reste souvent. Les sessions restent (pas de `calendar_source_id` stocké).
- L’URL distante est récupérée côté serveur ; l’hôte doit être joignable (aucun header d’auth dans le code).
- `CalendarSource::latest()` dans `index` n’est pas filtré par `company_id` : l’historique est global à la base.

## Architecture

| Pièce | Chemin |
|-------|--------|
| Routes | `routes/web.php` préfixe `admin/calendars` |
| Contrôleur | `app/Http/Controllers/CalendarFileController.php` |
| Parseur / import | `app/Services/CalendarService.php` |
| Matcher inutilisé | `app/Services/CalendarMatcher.php` |
| Modèles | `CalendarSource`, `CalendarMapping` (morph vers `Course` ou `Group`) |
| Vues | `resources/views/calendar/manage.blade.php`, `mapping.blade.php` |
| Onglets | `resources/views/components/scheduling-module-tabs.blade.php` |
| Tables | `calendar_sources`, `calendar_mappings`, `plannings.calendar_source_id` (FK nullable, cascade si renseigné) |

`CalendarController::readICSFile` (parse JSON) n’est pas utilisé ; sa route est commentée.

## Voir aussi

- [V2 — navigation et modules](v2-navigation-modules.md)
- [Gestion des groupes](gestion-groupes.md)
- [V2 — facturation par école](v2-facturation-par-ecole.md) (`billableMultiplier`)
- [Roadmap — PWA & hors ligne](roadmap-pwa-offline.md) — l’ICS reste le seul flux externe → VRP
