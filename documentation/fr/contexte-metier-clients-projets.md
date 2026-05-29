# Contexte métier — clients & projets

**EN:** [business-context-clients-projects.md](../en/business-context-clients-projects.md)

## Objectif

Réutiliser **le même système** (tables, routes, logique) pour la formation **ou** pour une activité type consulting / prestation : **clients**, **projets**, **phases**, **équipes**.

## Principe retenu

| Couche | Approche |
|--------|----------|
| **UI (libellés)** | Fichiers de traduction + profil `consulting` |
| **Données** | Inchangées (`schools`, `courses`, …) |
| **URLs / code** | Inchangés (`/school`, modèles `School`, …) |

Les traductions **suffisent pour l’écran** ; un **profil par entreprise** est nécessaire si formation et consulting coexistent sur une même instance.

## Correspondance des termes

| Technique (DB / code) | Formation (`education`) | Consulting (`consulting`) |
|------------------------|-------------------------|---------------------------|
| `school` | École | Client |
| `program` | Programme | Projet |
| `course` | Cours | Phase |
| `group` | Groupe | Équipe |
| `semester` (champ) | Semestre | Période |
| `students` (libellé effectif) | étudiants | collaborateurs |

## Faisabilité (synthèse)

| Objectif | Faisabilité |
|----------|-------------|
| Adapter les libellés | **Élevée** |
| Deux métiers sur une instance | **Moyenne** (profil `companies.terminology_profile`) |
| Renommer tables / URLs | **Faible** (coût élevé, peu de gain) |

## Exclu de la phase 1

- Renommage des modèles ou migrations SQL
- Alias de routes cosmétiques (`/client` → `/school`)
- Masquage conditionnel du champ semestre (phase 2 possible)

## Liens

- [Phase 1 — terminologie](phase-1-terminologie.md)
- [Libellés consulting](libelles-consulting.md)
