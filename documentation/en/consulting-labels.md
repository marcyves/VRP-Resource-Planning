# Labels — consulting mode

**FR:** [libelles-consulting.md](../fr/libelles-consulting.md)

Profile `companies.terminology_profile = consulting` and `*_consulting` locales.

## Navigation & dashboard

| i18n key | Training (`fr` base) | Consulting (`fr_consulting`) |
|----------|----------------------|------------------------------|
| `workload_plan` | Plan de charge | **Pilotage d'activité** (FR UI) |
| `schools` | Écoles / Schools | Clients |
| `programs` | Programmes / Programs | Projects |
| `course` | Cours / Course | Phase |
| `groups` | Groupes / Groups | Teams |

| Key | `en_consulting` | `it_consulting` |
|-----|-----------------|-----------------|
| `workload_plan` | Activity overview | Panoramica attività |

## Where `workload_plan` appears

- `layouts/navigation.blade.php`
- `dashboard.blade.php`
- Breadcrumbs (schools, groups, programs)

Always via `__('messages.workload_plan')`.

## Overrides (EN)

File: `resources/lang/en_consulting/overrides.php` (merged over `en/messages.php`)

## Change a label

1. Key in `resources/lang/{locale}/messages.php`
2. Override in `{locale}_consulting/overrides.php`
3. `php artisan config:clear` in production if needed

## Flash message examples

| Key | Training | Consulting |
|-----|----------|------------|
| `school_saved_success` | School :name saved… | Client :name saved… |
| `course_saved_success` | Course :name… | Phase :name… |
| `program_saved_success` | Program saved… | Project saved… |
| `group_saved_success` | Group saved… | Team saved… |

## Links

- [Business context](business-context-clients-projects.md)
- [Configuration](configuration.md)
