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
| Note de frais | `/treasury/reports/{expenseReport}` | `TreasuryController@showReport`, `resources/views/treasury/report.blade.php` |
| Créer / modifier une dépense | `/treasury/expenses/create`, `/treasury/expenses/{expense}/edit` | `TreasuryController`, `resources/views/treasury/expense-form.blade.php` |

Les anciennes URLs `/treasury/reconciliation*` redirigent vers les écrans banque.

## Dépenses et notes de frais

Les sorties sont limitées à l'entreprise (`expenses.company_id`, `expense_reports.company_id`). Il n'y a pas d'écran « créer une note » : la première dépense qui rejoint une note crée la ligne du mois. Une seule note par entreprise, année et mois (`unique` sur ces trois colonnes).

| Type | Stockage | Écran |
|------|----------|-------|
| Ligne de note | `expenses.expense_report_id` renseigné | `/treasury` `#expense-reports` |
| Dépense hors note | `expense_report_id` null | `#standalone-expenses` |

L'année de la synthèse est `current_year` en session. La valeur `all` utilise quand même l'année civile courante pour la carte de solde, la liste des notes et les dépenses hors note.

### Création et édition

Les boutons (créer, modifier, supprimer, valider, payer) ne s'affichent qu'en mode `Edit` (`User::getMode()`). `TreasuryController` ne vérifie **pas** ce mode. Tout utilisateur du tenant qui peut appeler la route peut écrire. Un enregistrement d'une autre entreprise renvoie **403**.

`GET /treasury/expenses/create` (bouton **Créer une dépense**) ne passe pas `standalone=1` : **Inclure dans une note de frais mensuelle** est donc coché au départ. Le décocher pour une dépense hors note ; la date de paiement n'apparaît que dans ce cas. `?standalone=1` part décoché. `?report_id=` préremplit cette note et renvoie **403** si le statut n'est pas `draft`.

| Champ | Contrainte |
|-------|------------|
| `expense_date`, `label` | Obligatoires. Libellé max 255 |
| `amount` | Numérique ≥ 0, obligatoire. C'est le montant **TTC** des totaux, du PDF et du rapprochement |
| `tax_amount` | Numérique optionnel **0–100**. C'est un **pourcentage de TVA**, pas un montant (libellé « TVA % »). Vide → **20** en base. HT affiché = `amount / (1 + pourcentage/100)` |
| `payment_date` | Hors note seulement. Forcé à `null` dès que la ligne est sur une note |
| `vendor`, `category`, `payment_method` | Optionnels, max 255. Les listes fournisseur et catégorie reprennent les valeurs déjà saisies pour l'entreprise |
| `notes` | Validé et fillable, mais le formulaire **n'a pas de champ notes** |
| Lien note | Case `include_in_expense_report` **ou** `expense_report_id` posté. Le mois vient de `expense_date` via `firstOrCreate` sur `(company_id, year, month)`, sauf un envoi non récurrent qui fournit `expense_report_id` (cet id est utilisé tel quel) |

**Récurrence** (`is_recurring`, fréquence `monthly` ou `yearly`, `recurring_until` à partir de `expense_date`) : `store` insère une ligne par période, de la date de début jusqu'à `recurring_until` inclus (`addMonthNoOverflow` / `addYearNoOverflow`). Chaque ligne résout la note de son propre mois si la case est cochée. `update` réécrit **cette ligne seulement** et ne génère pas le reste de la série.

Une ligne déjà rattachée à une note ne se modifie ou ne se supprime que tant que la note est `draft` (sinon **403**). Le formulaire d'édition reposte un `expense_report_id` caché, et le serveur traite tout id renseigné comme un rattachement : décocher **Inclure dans une note de frais mensuelle** ne détache **pas** la ligne.

`store` n'exige **pas** que la note cible soit `draft`. `firstOrCreate` rattache une nouvelle ligne à une note déjà validée ou payée pour ce mois. Le parcours `?report_id=` est celui qui contrôle le brouillon.

### Statut de la note

`expense_reports.status` vaut `draft` par défaut. Valeurs utilisées : `draft`, `validated`, `paid`. Il n'existe pas de statut `submitted`. Le libellé **Soumises** de la carte de solde est le total des notes **`draft`**. `submitted_at` est une date renseignée à la validation.

| Action | Depuis | Vers | Effet |
|--------|--------|------|-------|
| Valider | `draft` | `validated` | Renseigne `submitted_at` s'il est vide |
| Annuler la validation | `validated` | `draft` | Efface `submitted_at` |
| Marquer payée | `validated` | `paid` | `payment_date` obligatoire ; renseigne `reimbursed_at` ; complète `submitted_at` si vide |
| Annuler le paiement | `paid` | `validated` | Efface `reimbursed_at` |

Valider une note payée renvoie **403**. Payer une note qui n'est ni `validated` ni `paid` renvoie **403**. Le bénéficiaire affiché à l'écran et sur le PDF est le **nom de l'utilisateur connecté**, pas un champ stocké. PDF : `GET /treasury/reports/{expenseReport}/pdf`, fichier `note-de-frais-{AAAA}-{MM}.pdf`.

### Carte de solde

`#treasury-balance` n'est pas `BankBalanceService` :

- montant d'ouverture du compte `companies.billing_bank_account_id` s'il existe (**le flag actif n'est pas testé**), sinon le `TreasuryBalance` de l'année (créé à 0 au 1er janvier s'il manque) ;
- plus la somme de `invoices.amount` pour les factures avec `paid_at` renseigné et `bill_date` dans l'année (les parcours actuels stockent le TTC ; cette carte n'appelle **pas** `amountTtc()`, une ancienne ligne HT est donc additionnée telle quelle) ;
- moins les totaux brouillon, validé et payé (tous les statuts) ;
- moins tous les `amount` hors note de l'année, avec ou sans `payment_date`.

`InvoiceDashboardService` et `BankBalanceService` préfèrent toujours le compte de facturation **actif**. Ne pas lire cette carte comme ce solde bancaire.

## Montants de facture et fichiers PDF

Les factures stockent leur `amount` principal comme montant TTC dans les parcours actuels de création et d'édition. `Invoice::amountHt()` et `Invoice::amountTtc()` normalisent l'édition et le rapprochement bancaire, y compris pour d'anciennes factures planning dont le montant stocké peut correspondre au total HT.

L'action PDF (`GET /invoice/{invoice}`) résiste aux fichiers manquants : `InvoiceController@show` appelle `InvoiceService::ensurePdfOnDisk()` avant de retourner `Storage::download()`. Si le fichier attendu (`invoices/{préfixe facture entreprise}{id facture}.pdf`) est absent, le service le reconstruit depuis la base :

1. lignes de planning déjà rattachées au numéro de facture ;
2. détails de planning du mois de `bill_date` et de l'école de la facture ;
3. ligne manuelle de secours avec la description et le montant HT de la facture.

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
| Crédit | Une facture | Facture de la même entreprise, sans rapprochement bancaire existant, comparée au crédit via `Invoice::amountTtc()` |
| Crédit | Groupe de factures | Au moins deux factures, même école, total TTC à 0,02 EUR près de la ligne bancaire |
| Débit | Dépense | Dépense de la même entreprise, sans rapprochement bancaire existant |
| Débit | Note de frais | Les notes suggérées appartiennent à la même entreprise, sans rapprochement existant, avec statut `validated` ou `paid` |

Les suggestions automatiques privilégient les montants à **0,02 EUR** près et les dates dans une fenêtre de **45 jours**. Pour les crédits de facture, le montant comparé est TTC, même lorsqu'une ligne ancienne nécessite une normalisation HT/TTC. Si aucune suggestion stricte n'existe, l'écran peut afficher d'autres enregistrements non rapprochés : il faut donc vérifier montant, date et libellé avant validation.

Une ligne bancaire est considérée comme totalement rapprochée quand la somme des `matched_amount` est à **0,02 EUR** près du montant absolu de la ligne (`BankStatementLine::isReconciled()`).

## Effets de bord

Le rapprochement ne crée pas seulement un lien :

- une facture rapprochée reçoit `Invoice::paid_at` à la date de l'opération bancaire si le champ était vide ;
- une dépense autonome reçoit `Expense::payment_date` à la date de l'opération bancaire si le champ était vide ;
- une note de frais rapprochée passe en `status = paid`, reçoit `reimbursed_at` à la date bancaire et complète `submitted_at` si nécessaire.

Le dérapprochement supprime les lignes de rapprochement de la ligne bancaire. Il ne remet **pas** automatiquement à zéro `paid_at`, `payment_date` ni le statut de note de frais.

## Soldes et KPIs de trésorerie

`InvoiceDashboardService` construit les KPIs mensuels de l'écran synthèse :

- factures émises par `bill_date`, additionnées en TTC avec HT dérivé (`amount / 1.2`) ;
- factures payées par `paid_at`, additionnées en TTC avec HT dérivé ;
- travail planifié non facturé depuis la préparation facturation des écoles, calculé HT puis projeté en TTC pour le graphique ;
- dernier solde bancaire via `BankBalanceService`.

La formule de la carte de solde (notes `draft` libellées **Soumises**, compte de facturation utilisé même s'il est inactif, `amount` facture stocké) est dans [Dépenses et notes de frais](#carte-de-solde).

`BankBalanceService` utilise le compte bancaire de facturation actif quand il est configuré. Sinon, il revient au solde d'ouverture annuel `TreasuryBalance` et aux lignes bancaires dédupliquées de l'entreprise.

La déduplication des lignes de relevé utilise le compte, la date d'opération, le libellé, le débit et le crédit. L'index de ligne n'entre pas dans la clé de déduplication du solde.

## Checklist opérationnelle

- Renseigner la date et le montant d'ouverture du compte avant de s'appuyer sur les KPIs de solde.
- Sélectionner le compte bancaire de facturation lorsqu'un seul compte doit alimenter les soldes du tableau de bord factures.
- Utiliser le rapprochement multi-factures seulement quand le paiement couvre plusieurs factures d'une même école.
- Traiter le dérapprochement comme une suppression de lien : corriger manuellement les champs de paiement si un mauvais rapprochement avait marqué un enregistrement comme payé.
- Décocher **Inclure dans une note de frais mensuelle** pour une charge hors note. Le bouton **Créer une dépense** part avec la case cochée.
- Lire `tax_amount` comme un pourcentage de TVA (défaut 20) et `amount` comme le TTC.

## Dépannage

| Symptôme | Vérification |
|----------|--------------|
| Import refusé | Le fichier est `.xlsx`, contient les colonnes Date / libellé / débit / crédit et des lignes d'opération |
| Import bancaire invisible | L'import et le compte sélectionné appartiennent à l'entreprise de l'utilisateur connecté |
| Ligne encore non rapprochée | La somme rapprochée diffère du montant absolu de plus de 0,02 EUR |
| Facture absente des suggestions | Elle peut déjà avoir un rapprochement bancaire ou appartenir à une autre entreprise |
| Note de frais absente des suggestions | Les suggestions incluent seulement les notes `validated` ou `paid`, non rapprochées |
| Nouvelle dépense sur une note déjà validée | `store` fait un `firstOrCreate` du mois et n'exige pas `draft`. Modifier ou supprimer ces lignes renvoie ensuite **403** |
| Décocher « inclure dans une note » laisse la ligne sur la note | L'édition reposte un `expense_report_id` caché ; un id renseigné rattache encore la ligne |
| Le total **Soumises** semble un quatrième statut | Cette ligne est `status = draft` |

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `routes/web.php` | Routes trésorerie, import bancaire, match/unmatch et redirections legacy |
| `app/Http/Controllers/BankController.php` | CRUD banque/compte, import de relevé, filtrage, match/unmatch |
| `app/Services/BankStatement/CaBankStatementParser.php` | Analyse des relevés XLSX |
| `app/Services/BankStatement/BankReconciliationService.php` | Suggestions, règles de rapprochement, effets de bord |
| `app/Services/InvoiceService.php` | Chemin PDF facture, régénération et reconstruction des lignes planning/manuelles |
| `app/Services/BankBalanceService.php` | Résolution des soldes et déduplication des lignes |
| `app/Services/InvoiceDashboardService.php` | Données mensuelles factures, planifié et banque |
| `app/Http/Controllers/TreasuryController.php` | Synthèse, dépenses, statut des notes, PDF de note |
| `app/Models/Expense.php`, `app/Models/ExpenseReport.php` | Lignes hors note et notes mensuelles |
| `app/Models/BankReconciliation.php` | Lien polymorphe vers factures, dépenses et notes de frais |
| `resources/views/components/treasury-module-tabs.blade.php` | Onglets du module trésorerie |

## Voir aussi

- [V2 - navigation et modules](v2-navigation-modules.md)
- [V2 - facturation par école](v2-facturation-par-ecole.md)
- [Facturation électronique](facturation-electronique.md)
