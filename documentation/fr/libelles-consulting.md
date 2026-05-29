# Libellés — mode consulting

**EN:** [consulting-labels.md](../en/consulting-labels.md)

Profil `companies.terminology_profile = consulting` et locales `*_consulting`.

## Navigation & tableau de bord

| Clé i18n | Formation (`fr`) | Consulting (`fr_consulting`) |
|----------|------------------|------------------------------|
| `workload_plan` | Plan de charge | **Pilotage d'activité** |
| `schools` | Écoles | Clients |
| `programs` | Programmes | Projets |
| `course` | Cours | Phase |
| `groups` | Groupes | Équipes |

| Clé | `en_consulting` | `it_consulting` |
|-----|-----------------|-----------------|
| `workload_plan` | Activity overview | Panoramica attività |

## Où s’affiche `workload_plan`

- `layouts/navigation.blade.php`
- `dashboard.blade.php`
- Fil d’Ariane (écoles, groupes, programmes)

Via `__('messages.workload_plan')` uniquement.

## Surcharges (FR)

Fichier : `resources/lang/fr_consulting/overrides.php`

## Modifier un libellé

1. Clé dans `resources/lang/fr/messages.php`
2. Surcharge dans `fr_consulting/overrides.php` (+ `en_consulting`, `it_consulting`)
3. `php artisan config:clear` en production si besoin

## Liens

- [Contexte métier](contexte-metier-clients-projets.md)
- [Configuration](configuration.md)
