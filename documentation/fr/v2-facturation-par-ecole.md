# V2 — Facturation par école

**EN:** [v2-billing-per-school.md](../en/v2-billing-per-school.md)

## Changement majeur

La **préparation de la facturation** n’est plus dans le module **Agenda** (`/billing`). Elle se trouve sur la **fiche école** (`school/show`), section ancrée `#billing`.

### Avant (v1)

- Onglets Agenda : Planning · Calendrier · **Préparation facturation** · Par date
- Vue globale toutes écoles confondues

### Après (v2)

- Agenda : Planning · Calendrier uniquement
- Préparation : **une école à la fois**, avec navigation mensuelle propre

## Écran fiche école

Composant : `resources/views/components/school-billing-section.blade.php`

| Bloc | Description |
|------|-------------|
| Sélecteur de période | Mois précédent / suivant, liste déroulante des mois |
| Bascule **Par date** | Regroupement chronologique vs par cours (session `school_billing_by_date`) |
| Tableaux sessions | Groupe, horaire, heures, n° facture |
| Totaux | Heures et montants HT / TTC par cours et par école |
| Actions (mode Edit) | Assigner une facture existante, créer une facture |

## Variables de session billing

| Clé | Rôle |
|-----|------|
| `billing_year` | Année **calendaire** de la période facturation (distincte de `current_year` académique) |
| `current_month` | Mois affiché |
| `school_billing_by_date` | Vue par date activée ou non |

Initialisation : `billing_year` = année courante si absent (`Tools::getBillingYear`).

## Routes

| Route | Action |
|-------|--------|
| `school/{school}/billing/previous` | Mois précédent |
| `school/{school}/billing/next` | Mois suivant |
| `school/{school}/billing/by-date` | Bascule vue par date |
| `school/{school}/billing/jump-unbilled` | Sauter au dernier mois antérieur avec sessions non facturées |
| `school/{school}/billing/set-bill` | Rattacher une facture aux sessions du mois |

Legacy : `/billing*` redirige vers `school.show#billing` si une école est en session.

## Calcul des montants

Centralisé dans `App\Http\Utility\Tools` :

- `sessionDurationHours()` — durée réelle de la session
- `billableMultiplier()` — normalise le taux facturable (correction import calendrier)
- `planningGain()` — montant HT session

Utilisé par la préparation facturation **et** les stats « non facturé » de la liste écoles.

## Bouton « Sauter aux sessions non facturées »

- Recherche en arrière mois par mois la dernière période avec au moins une session sans facture
- **Désactivé** s’il n’existe aucun mois antérieur non facturé
- Méthodes modèle : `School::findPreviousUnbilledPeriod()`, `hasPreviousUnbilledPeriod()`

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `app/Http/Controllers/BillingController.php` | Navigation période, setBill, jump unbilled |
| `app/Http/Controllers/SchoolController.php` | Chargement données billing sur `show` |
| `app/Models/School.php` | `getBillingPlanning()`, recherche non facturé |
| `app/Models/User.php` | `getUnbilledStatsBySchool()` pour la liste |
| `resources/css/bills.css` | Styles section facturation |

## Voir aussi

- [V2 — navigation](v2-navigation-modules.md)
- [Modèle de données](modele-donnees-formation.md)
