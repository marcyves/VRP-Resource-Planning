# Labels — medical / services mode

**FR:** [libelles-medical.md](../fr/libelles-medical.md)

Profile `companies.terminology_profile = medical` and `*_medical` locales.

## Term mapping

| Technical (DB / code) | Training (`education`) | Medical (`medical`) |
|-----------------------|------------------------|---------------------|
| `school` | School | **Structure** |
| `program` | Program | **Service** (FR: Prestation) |
| `course` | Course | **Session** (FR: Séance) |
| `group` | Group | **Patient** |
| `semester` (field) | Semester | Period |

## Navigation

| i18n key | `en_medical` | `fr_medical` |
|----------|--------------|--------------|
| `schools` | Structures | Structures |
| `programs` | Services | Prestations |
| `course` | Session | Séance |
| `groups` | Patients | Patients |
| `workload_plan` | Activity overview | Pilotage d'activité |

Company-wide screens (agenda, `/home`, treasury, referential) use these overlays. Per-school **mentoring** labels (`schools.context = mentoring`) are a separate overlay — see [school mode mentoring](v2-school-mode-mentoring.md).

## Files

```text
resources/lang/fr_medical/messages.php   # merge fr + overrides.php
resources/lang/en_medical/overrides.php
resources/lang/it_medical/...
```

## Change a label

1. Key in `resources/lang/{locale}/messages.php`
2. Override in `{locale}_medical/overrides.php`
3. `php artisan config:clear` in production if needed

## Links

- [Phase 1 — terminology](phase-1-terminology.md)
- [Configuration](configuration.md)
- [Consulting labels](consulting-labels.md)
