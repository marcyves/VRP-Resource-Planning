# Roadmap — PWA & mode hors ligne

**EN:** [roadmap-pwa-offline.md](../en/roadmap-pwa-offline.md)

> **Statut :** idée retenue, **non planifiée** à court terme (complexité élevée par rapport au bénéfice immédiat).

## Contexte utilisateur

Un formateur ou vacataire peut vouloir :

- consulter son **agenda** sans connexion (métro, salle sans Wi‑Fi) ;
- **ajouter ou modifier** une séance en déplacement, puis synchroniser avec le site en ligne ;
- utiliser VRP comme une **app sur l’écran d’accueil** du téléphone.

Aujourd’hui, VRP est une application **web centrale** : toutes les données vivent sur le serveur. Il n’existe **pas** de synchronisation entre une instance locale et un site en ligne, ni de mode hors ligne.

## État actuel

| Capacité | Disponible |
|----------|------------|
| Accès navigateur (desktop / mobile) | Oui |
| Installation PWA (manifest, service worker) | Non |
| Consultation offline | Non |
| Saisie offline + synchro | Non |
| Import calendrier externe (`.ics` / URL) | Oui — flux **externe → VRP**, pas bidirectionnel |

L’import calendrier reste un complément utile (préparer dans Google/Apple Calendar, importer dans VRP), mais ne remplace pas une vraie synchro offline.

## Pourquoi c’est reporté

- L’UI actuelle repose sur des **pages Blade** servies par Laravel : chaque action nécessite le réseau.
- Un vrai offline implique une **API JSON**, un **stockage local** (IndexedDB) et une **stratégie de sync** (conflits, multi-utilisateur, séances déjà facturées).
- Le périmètre métier complet (factures PDF, rapprochement bancaire, documents) reste difficilement offline.

## Piste technique retenue (quand le moment viendra)

**PWA d’abord** (réutilisation du site), pas d’app native iOS/Android en première étape.

```
Navigateur
  ├─ Service Worker  → cache assets (CSS, JS, icônes)
  ├─ IndexedDB       → copie locale (plannings, écoles, groupes)
  └─ File de sync    → actions offline rejouées au retour du réseau

Serveur Laravel
  └─ API REST (Sanctum) → pull des modifs / push de la file d’attente
```

### Phases envisagées

| Phase | Contenu | Priorité métier |
|-------|---------|-----------------|
| **0 — Shell PWA** | `manifest.webmanifest`, icônes, service worker, « Ajouter à l’écran d’accueil » | Faible effort, valeur limitée seule |
| **1 — Lecture offline** | Cache de l’agenda (semaine / mois), bandeau « Hors ligne — dernière synchro à … » | **MVP recommandé** |
| **2 — Écriture offline** | Créer / modifier / supprimer / dupliquer une séance ; file d’attente | Valeur forte, complexité sync |
| **3 — Hors périmètre offline** | Facturation, import `.ics`, trésorerie, upload documents | En ligne uniquement |

### Points d’attention

- **Conflits** : deux modifications de la même séance (offline + en ligne, ou deux utilisateurs).
- **Facturation** : respecter les garde-fous existants (`invoice_id`, suppression bloquée).
- **Sécurité** : purge du cache local à la déconnexion ; données sensibles sur l’appareil.
- **iOS** : PWA offline fonctionnelle mais avec limites (stockage évincé, notifications).

## Ordre de grandeur (indicatif)

| Phase | Effort |
|-------|--------|
| Shell PWA | 1–2 jours |
| API agenda JSON | 3–5 jours |
| Lecture offline | 1–2 semaines |
| Écriture + sync | 2–4 semaines |

## Alternatives actuelles pour l’utilisateur

1. Travailler **directement sur le site en ligne** (même compte, plusieurs appareils).
2. Préparer l’agenda dans un **calendrier externe**, puis **importer** le `.ics` dans VRP.
3. Ne pas compter sur une **instance locale** VRP comme brouillon synchronisable avec la prod.

## Liens

- [README du dépôt — roadmap facturation électronique](../../README.md#roadmap--facturation-électronique)
- [V2 — navigation & modules](v2-navigation-modules.md) (module Agenda)
- Import calendrier : routes `admin/calendars/*`, `CalendarFileController`
