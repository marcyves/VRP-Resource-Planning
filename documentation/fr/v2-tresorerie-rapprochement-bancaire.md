# V2 — Trésorerie et rapprochement bancaire

**EN:** [v2-treasury-bank-reconciliation.md](../en/v2-treasury-bank-reconciliation.md)

## Intention

La trésorerie devient le point d'entrée opérationnel pour les factures, la visibilité de caisse et les dépenses. En v2, la liste des factures et les KPI de facturation sont dans le module **Trésorerie**, tandis que la banque ajoute la configuration des comptes, l'import de relevés et le rapprochement manuel avec factures ou dépenses.

## Parcours utilisateur

| Étape | Écran | Rôle |
|-------|-------|------|
| 1 | `/treasury` | Consulter le dashboard annuel, le solde de clôture et les sections dépenses |
| 2 | `/treasury/invoices` | Rechercher, filtrer et trier les factures par description, école/client et statut de paiement |
| 3 | `/treasury/bank` | Créer les banques, ajouter les comptes, saisir les soldes d'ouverture et choisir le compte de facturation |
| 4 | `/treasury/bank` | Importer un relevé bancaire pour un compte (`.xlsx`) |
| 5 | `/treasury/bank/imports/{import}` | Vérifier les lignes, filtrer rapproché/non rapproché et effectuer les rapprochements |

Les mutations sur les écrans banque ne sont affichées qu'en mode **Edit**. Les utilisateurs authentifiés peuvent tout de même consulter le module et les lignes importées.

## Architecture du module

| Zone | Code principal | Notes |
|------|----------------|-------|
| Synthèse trésorerie | `TreasuryController::index`, `resources/views/treasury/index.blade.php` | Utilise `InvoiceDashboardService` pour les KPI factures et les données de graphique |
| Liste factures | `TreasuryController::invoices`, `resources/views/treasury/invoices.blade.php`, `components/table-invoices.blade.php` | Les routes ressource `/invoice` créent/éditent toujours les factures ; l'onglet liste est `/treasury/invoices` |
| Gestion banque/compte | `BankController::index`, `storeBank`, `storeAccount`, `updateBillingAccount` | Les banques et numéros de compte sont uniques par entreprise/banque |
| Import relevé | `BankReconciliationService::import`, `CaBankStatementParser`, `XlsxSheetReader` | L'import écrit `bank_statement_imports` et `bank_statement_lines` dans une transaction |
| Rapprochement | `BankReconciliationService::match*`, `BankStatementLine::isReconciled()` | Une ligne est rapprochée quand la somme des `matched_amount` correspond avec une tolérance de `0.02` |
| KPI soldes | `BankBalanceService`, `InvoiceDashboardService` | Utilise le compte de facturation actif s'il est configuré ; sinon revient au solde d'ouverture trésorerie + lignes importées dédupliquées |

## Routes

| Route | Action |
|-------|--------|
| `GET /treasury` | Dashboard trésorerie et sections dépenses |
| `GET /treasury/invoices` | Liste factures avec filtres |
| `GET /treasury/bank` | Cartes banques, comptes, imports |
| `POST /treasury/bank/banks` | Créer une banque |
| `POST /treasury/bank/accounts` | Créer un compte bancaire |
| `PUT /treasury/bank/billing-account` | Choisir le compte bancaire de facturation de l'entreprise |
| `POST /treasury/bank/import` | Importer un relevé |
| `GET /treasury/bank/imports/{import}` | Consulter les lignes importées |
| `POST /treasury/bank/imports/{import}/lines/{line}/match` | Rapprocher une ligne |
| `DELETE /treasury/bank/imports/{import}/matches/{reconciliation}` | Supprimer le rapprochement d'une ligne |

Les anciennes routes `/treasury/reconciliation` redirigent vers les écrans banque.

## Format d'import relevé

Le parseur actuel est spécifique à l'export bancaire géré par `CaBankStatementParser` :

- Type de fichier : `.xlsx`, taille maximale `10240` Ko.
- La première feuille est lue directement depuis la structure ZIP/XML du XLSX ; aucun moteur tableur n'est utilisé.
- Une ligne d'en-tête est obligatoire avec `Date`, `Libellé`/`Libelle` et `Débit euros`/`Debit euros`.
- Les colonnes sont interprétées ainsi :
  - `A` : date d'opération (`jj/mm/aaaa` ou date série Excel)
  - `B` : libellé
  - `C` : débit
  - `D` : crédit
- Les métadonnées sont détectées avant l'en-tête si elles existent : numéro de compte, libellé de compte, période et solde de relevé.
- Les lignes vides, sans libellé, sans montant ou avec date illisible sont ignorées.

Si aucun en-tête ou aucune opération n'est trouvé, l'import échoue avec un message utilisateur.

## Règles de rapprochement

| Ligne bancaire | Candidats | Effet de bord au rapprochement |
|----------------|-----------|--------------------------------|
| Crédit (`amount > 0`) | Factures non rapprochées, ou groupes de factures d'une même école/client | Renseigne `Invoice::paid_at` avec la date d'opération si vide |
| Débit (`amount < 0`) | Dépenses isolées et notes de frais validées/payées | Renseigne `Expense::payment_date`, ou marque la note de frais comme payée |

Les suggestions cherchent d'abord les enregistrements dans une fenêtre de 45 jours et à `0.02` près du montant bancaire. Si aucun candidat strict n'existe, l'interface propose plus largement les éléments non rapprochés, triés par proximité de montant.

Le rapprochement groupé de factures est autorisé uniquement si :

- au moins deux factures sont sélectionnées ;
- toutes les factures appartiennent à la même école/client ;
- la somme de leurs `amountTtc()` correspond au crédit bancaire à `0.02` près ;
- aucune facture n'est déjà rapprochée.

## Calculs de solde

Deux notions de solde coexistent :

| Affichage | Source |
|-----------|--------|
| Solde bancaire du dashboard factures | `BankBalanceService::totalAt()` à chaque fin de mois |
| Solde de clôture trésorerie | Solde d'ouverture + factures payées - notes soumises/validées/payées - dépenses isolées |

Pour les soldes bancaires du dashboard, les lignes importées dupliquées sont dédupliquées par compte, date, libellé, débit et crédit avant sommation. Si l'entreprise a un compte bancaire de facturation actif, seul ce compte est utilisé ; sans compte sélectionné, toutes les lignes de l'entreprise sont prises en compte depuis la date d'ouverture trésorerie.

## Notes opérationnelles et pièges

- Sélectionner le compte bancaire de facturation avant de s'appuyer sur les KPI de solde bancaire ou sur les coordonnées bancaires des PDF de facture.
- Importer chaque relevé sur le compte correspondant. Le parseur peut mettre à jour le numéro/libellé du compte à partir des métadonnées du fichier.
- Supprimer un compte bancaire supprime ses imports, lignes et rapprochements, et vide le compte de facturation des entreprises qui l'utilisaient.
- Supprimer un import supprime par cascade ses lignes et rapprochements.
- Supprimer un rapprochement efface les enregistrements de rapprochement de la ligne ; cela ne remet pas actuellement à zéro `paid_at`, `payment_date` ou le statut de note de frais.
- Certaines anciennes factures peuvent stocker un montant HT ; le rapprochement utilise `Invoice::amountTtc()` pour comparer aux crédits bancaires.

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `app/Http/Controllers/TreasuryController.php` | Dashboard trésorerie, liste factures, dépenses et notes de frais |
| `app/Http/Controllers/BankController.php` | CRUD banque/compte, écrans import, actions rapprocher/dérapprocher |
| `app/Services/InvoiceDashboardService.php` | KPI mensuels factures, planning et banque |
| `app/Services/BankBalanceService.php` | Contexte compte de facturation et soldes bancaires dédupliqués |
| `app/Services/BankStatement/BankReconciliationService.php` | Import, recherche de candidats, effets de bord du rapprochement |
| `app/Services/BankStatement/CaBankStatementParser.php` | Parseur XLSX actuel |
| `app/Models/Bank*.php`, `BankStatement*.php`, `BankReconciliation.php` | Modèles du domaine banque/trésorerie |

## Voir aussi

- [V2 — navigation](v2-navigation-modules.md)
- [V2 — facturation par école](v2-facturation-par-ecole.md)
