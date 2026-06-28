# Libellés — mode médical / prestations

**EN:** [medical-labels.md](../en/medical-labels.md)

Profil `companies.terminology_profile = medical` et locales `*_medical`.

## Correspondance des termes

| Technique (DB / code) | Formation (`education`) | Médical (`medical`) |
|------------------------|-------------------------|---------------------|
| `school` | École | **Structure** |
| `program` | Programme | **Prestation** |
| `course` | Cours | **Séance** |
| `group` | Groupe | **Patient** |
| `semester` (champ) | Semestre | Période |

## Navigation

| Clé i18n | `fr_medical` |
|----------|--------------|
| `schools` | Structures |
| `programs` | Prestations |
| `course` | Séance |
| `groups` | Patients |
| `workload_plan` | Pilotage d'activité |

## Fichiers

```text
resources/lang/fr_medical/messages.php   # merge fr + overrides.php
resources/lang/en_medical/...
resources/lang/it_medical/...
```

## Modifier un libellé

1. Clé dans `resources/lang/fr/messages.php`
2. Surcharge dans `fr_medical/overrides.php` (+ `en_medical`, `it_medical`)
3. `php artisan config:clear` en production si besoin

## Liens

- [Phase 1 — terminologie](phase-1-terminologie.md)
- [Configuration](configuration.md)
- [Libellés consulting](libelles-consulting.md)
