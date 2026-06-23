# V2 - Trésorerie et rapprochement bancaire

**EN:** [v2-treasury-bank-reconciliation.md](../en/v2-treasury-bank-reconciliation.md)

## Intention

Le module **Trésorerie** regroupe le suivi financier global :

- factures émises et payées ;
- création de facture depuis le contexte école courant ;
- banques, comptes bancaires et imports de relevés ;
- rapprochement des factures, dépenses et notes de frais ;
- notes de frais et dépenses autonomes.

Le parcours bancaire relie les opérations réelles aux enregistrements VRP sans modifier la préparation de facturation située sur chaque fiche école.

## Entrées principales

| Écran | Route | Contrôleur / vue |
|-------|-------|------------------|
| Synthèse trésorerie | `/treasury` | `TreasuryController@index`, `resources/views/treasury/index.blade.php` |
| Liste factures | `/treasury/invoices` | `TreasuryController@invoices`, `resources/views/treasury/invoices.blade.php` |
| Banques, comptes et imports | `/treasury/bank` | `BankController@index`, `resources/views/treasury/bank/index.blade.php` |
| Détail d'un relevé importé | `/treasury/bank/imports/{import}` | `BankController@show`, `resources/views/treasury/bank/show.blade.php` |

Les anciennes URLs `/treasury/reconciliation*` redirigent vers les écrans banque.

## Parcours d'import bancaire

1. Créer une banque, puis un compte bancaire rattaché.
2. Marquer éventuellement un compte comme **compte bancaire de facturation** de l'entreprise.
3. Importer un relevé depuis `/treasury/bank`.
4. Vérifier les lignes analysées sur le détail d'import.
5. Rapprocher chaque ligne non lettrée avec une facture, un groupe de factures, une dépense ou une note de frais suggérée.

### Contraintes d'import

| Contrainte | Détail |
|------------|--------|
| Type fichier | `.xlsx` uniquement (`statement_file`, max 10 Mo) |
| Parser | `App\Services\BankStatement\CaBankStatementParser` |
| Colonnes attendues | Date, libellé, débit euros, crédit euros |
| Périmètre | Chaque banque, compte, import, ligne et rapprochement est filtré par `company_id` |

Le parser crée un `BankStatementImport` et une `BankStatementLine` par opération. Les crédits sont positifs ; les débits sont négatifs.

## Règles de rapprochement

`App\Services\BankStatement\BankReconciliationService` crée des lignes polymorphes `BankReconciliation`.

| Ligne bancaire | Cible | Règles |
|----------------|-------|--------|
| Crédit | Une facture | Facture de la même entreprise, sans rapprochement bancaire existant |
| Crédit | Groupe de factures | Au moins deux factures, même école, total TTC à 0,02 EUR près de la ligne bancaire |
| Débit | Dépense | Dépense de la même entreprise, sans rapprochement bancaire existant |
| Débit | Note de frais | Les notes suggérées appartiennent à la même entreprise, sans rapprochement existant, avec statut `validated` ou `paid` |

Les suggestions automatiques privilégient les montants à **0,02 EUR** près et les dates dans une fenêtre de **45 jours**. Si aucune suggestion stricte n'existe, l'écran peut afficher d'autres enregistrements non rapprochés : il faut donc vérifier montant, date et libellé avant validation.

Une ligne bancaire est considérée comme totalement rapprochée quand la somme des `matched_amount` est à **0,02 EUR** près du montant absolu de la ligne (`BankStatementLine::isReconciled()`).

## Effets de bord

Le rapprochement ne crée pas seulement un lien :

- une facture rapprochée reçoit `Invoice::paid_at` à la date de l'opération bancaire si le champ était vide ;
- une dépense autonome reçoit `Expense::payment_date` à la date de l'opération bancaire si le champ était vide ;
- une note de frais rapprochée passe en `status = paid`, reçoit `reimbursed_at` à la date bancaire et complète `submitted_at` si nécessaire.

Le dérapprochement supprime les lignes de rapprochement de la ligne bancaire. Il ne remet **pas** automatiquement à zéro `paid_at`, `payment_date` ni le statut de note de frais.

## Soldes et KPIs de trésorerie

`InvoiceDashboardService` construit les KPIs mensuels de l'écran synthèse :

- factures émises par `bill_date` ;
- factures payées par `paid_at` ;
- travail planifié non facturé depuis la préparation facturation des écoles ;
- dernier solde bancaire via `BankBalanceService`.

`BankBalanceService` utilise le compte bancaire de facturation actif quand il est configuré. Sinon, il revient au solde d'ouverture annuel `TreasuryBalance` et aux lignes bancaires dédupliquées de l'entreprise.

La déduplication des lignes de relevé utilise le compte, la date d'opération, le libellé, le débit et le crédit. L'index de ligne n'entre pas dans la clé de déduplication du solde.

## Checklist opérationnelle

- Renseigner la date et le montant d'ouverture du compte avant de s'appuyer sur les KPIs de solde.
- Sélectionner le compte bancaire de facturation lorsqu'un seul compte doit alimenter les soldes du tableau de bord factures.
- Utiliser le rapprochement multi-factures seulement quand le paiement couvre plusieurs factures d'une même école.
- Traiter le dérapprochement comme une suppression de lien : corriger manuellement les champs de paiement si un mauvais rapprochement avait marqué un enregistrement comme payé.

## Dépannage

| Symptôme | Vérification |
|----------|--------------|
| Import refusé | Le fichier est `.xlsx`, contient les colonnes Date / libellé / débit / crédit et des lignes d'opération |
| Import bancaire invisible | L'import et le compte sélectionné appartiennent à l'entreprise de l'utilisateur connecté |
| Ligne encore non rapprochée | La somme rapprochée diffère du montant absolu de plus de 0,02 EUR |
| Facture absente des suggestions | Elle peut déjà avoir un rapprochement bancaire ou appartenir à une autre entreprise |
| Note de frais absente des suggestions | Les suggestions incluent seulement les notes `validated` ou `paid`, non rapprochées |

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `routes/web.php` | Routes trésorerie, import bancaire, match/unmatch et redirections legacy |
| `app/Http/Controllers/BankController.php` | CRUD banque/compte, import de relevé, filtrage, match/unmatch |
| `app/Services/BankStatement/CaBankStatementParser.php` | Analyse des relevés XLSX |
| `app/Services/BankStatement/BankReconciliationService.php` | Suggestions, règles de rapprochement, effets de bord |
| `app/Services/BankBalanceService.php` | Résolution des soldes et déduplication des lignes |
| `app/Services/InvoiceDashboardService.php` | Données mensuelles factures, planifié et banque |
| `app/Models/BankReconciliation.php` | Lien polymorphe vers factures, dépenses et notes de frais |
| `resources/views/components/treasury-module-tabs.blade.php` | Onglets du module trésorerie |

## Voir aussi

- [V2 - navigation et modules](v2-navigation-modules.md)
- [V2 - facturation par école](v2-facturation-par-ecole.md)
