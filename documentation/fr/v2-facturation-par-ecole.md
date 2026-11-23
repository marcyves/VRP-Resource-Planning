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
| Tableaux sessions | Groupe, horaire, heures, **HT**, **TTC**, n° facture |
| Totaux | Heures et montants HT / TTC par cours et par école |
| Actions (mode Edit) | **Facturer** un cours, ou assigner / **Facturer tout le mois** si le mois est entièrement non facturé |

### Montants par ligne de session

Chaque intervention affiche HT et TTC à côté des heures (`Tools::getBillingInformation()`) :

| Colonne | Source |
|---------|--------|
| Heures | `sessionDurationHours()` — badge d’alerte si la durée ≠ `session_length` du cours |
| HT | `planningGain()` stocké dans `gain` |
| TTC | `gain_ttc` = `round(gain * Tools::VAT_MULTIPLIER, 2)` (multiplicateur TVA **1,2**) |
| N° facture | `planning.invoice_id` |

Les **totaux** cours / école affichent `gain` HT et `gain * 1.2` TTC en ligne — ils ne somment pas les `gain_ttc` arrondis par ligne.

La directive `@money` ajoute déjà `€` (`AppServiceProvider`). Les cellules de ligne utilisent `@money($schedule['gain'])` / `@money($schedule['gain_ttc'])` **sans second symbole euro**. Les totaux utilisent `number_format(...) € HT / ... € TTC`.

Mise en page (`resources/css/bills.css`) : `table-layout: fixed` ; groupe 30 % avec ellipse ; horaire 25ch ; heures 10ch ; montants 15ch ; facture 10ch.

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
- `planningGain()` — montant HT session (`gain`)
- `gain_ttc` — `round(gain * VAT_MULTIPLIER, 2)` pour la colonne TTC

Utilisé par la préparation facturation **et** les stats « non facturé » de la liste écoles.

Le bandeau **gain mensuel** / **taux horaire** sous les totaux école réutilise le même `monthlyGain` HT, sans libellé TVA — à ne pas confondre avec les cartes KPI TTC de l’agenda.

## Lien avec la Trésorerie

Les factures créées depuis la section facturation d'une école sont listées dans **Trésorerie**. Quand une ligne bancaire créditrice est rapprochée d'une facture, le rapprochement bancaire renseigne `Invoice::paid_at` avec la date de l'opération bancaire si le champ était encore vide.

### Périmètre : un cours ou tout le mois

Mode Edit uniquement. L’en-tête de chaque cours est une seule ligne : nom du cours, date de facture et action (`bills.css` `.billing-course-header`, `flex-wrap: nowrap`).

| Situation | Ce que l’écran propose |
|-----------|------------------------|
| Le cours a au moins une session avec `planning.invoice_id` vide | Date + **Facturer** (`invoice_create_this_course`). GET `invoice.create` avec `from_school_billing=1`, `school_id`, `course_id`, `month`, `year`, `cmd=detailed`. |
| Toutes les sessions de ce cours ont déjà une facture | **Facture déjà assignée**. Cet écran n’ouvre pas une seconde facture pour ce cours. |
| Toutes les sessions du mois sont non facturées | Pied de page : assigner une facture existante (`school.billing.setBill`, sans `course_id`) et **Facturer tout le mois** (même URL de création, sans `course_id`). |
| Mois mixte | Les actions du pied de page restent masquées. Les cours non facturés affichent toujours **Facturer**. |

`invoices.course_id` (FK nullable vers `courses`, `nullOnDelete`, migration `2026_10_19_100000_add_comment_and_course_id_to_invoices_table`) mémorise le périmètre. Une facture de mois entier le laisse à `null`. `Invoice::course()`.

`InvoiceController::resolveCourseForSchool` : `course_id` vide = le mois ; un cours qui n’appartient pas à cette école → **403**. L’école doit être dans `User::getSchools()`, sinon create et store renvoient aussi **403**. Les lecteurs sont en **403** ; admins et éditeurs peuvent créer.

Les lignes d’aperçu viennent de `Tools::getInvoiceDetails(..., $courseId)`. `Planning::getPlanningBySchoolAndDate` filtre `plannings.course_id` seulement si un cours est fourni. À l’enregistrement, `InvoiceService::linkPlanningToInvoice` écrit `préfixe facture + id` sur **toutes** les sessions de la fenêtre, y compris celles qui avaient déjà un numéro. L’écran école masque **Facturer tout le mois** dès qu’une session est facturée : un mois mixte ne peut pas être réécrit d’un coup. **Facturer** sur un cours seulement en partie facturé réécrit quand même toutes les sessions de ce cours pour le mois. Une création Trésorerie qui poste un `course_id` fait de même. `BillingController::setBill` écrase de la même façon ; le formulaire d’assignation n’apparaît que si le mois est entièrement non facturé et n’envoie pas `course_id`.

Depuis la facturation école, le périmètre est un `course_id` caché. L’indication affiche le nom du cours ou **Tout le mois**. La description est préremplie avec le nom du cours. `from_school_billing` renvoie vers `school.show#billing`.

Hors facturation école, le formulaire de création affiche un select **Périmètre** (`invoice_scope`) : vide = tout le mois, sinon chaque cours de l’école (`$school->courses()`, ordre du nom, **y compris les cours archivés** — pas `SchoolCourseOptions`). Le select ne recharge pas l’aperçu. L’aperçu suit le `course_id` du GET ; `store` rattache le `course_id` posté. Pour changer les lignes, rouvrir la création avec la query voulue.

### Montants de facture

La TVA des factures est fixe : `Tools::VAT_MULTIPLIER` vaut **1,2** (`Tools::vatRateLabel()` → `20%`). Ce n’est pas le pourcentage `tax_amount` des dépenses.

`invoices.amount` est stocké en **TTC** à la création et à l’édition : `Tools::amountTtcFromHt($ht)` = `round(HT × 1,2, 2)`.

`InvoiceController::store` choisit d’abord le HT, puis convertit. Il appelle toujours `Tools::getInvoiceDetails` pour l’école, le mois, l’année et le cours postés (cours vide = mois entier), même si l’écran de création affichait un aperçu vide.

| Situation | HT retenu (stocké en TTC) | Lignes PDF |
|-----------|---------------------------|------------|
| Des sessions existent dans cette fenêtre | `round(HT planning, 2)`. Les champs postés `amount_ht`, `amount_ttc` et l’ancien `amount` sont ignorés. | Lignes de planning. **P.U. HT** = tarif horaire, quantité = heures. |
| Aucune session (facture à la demande, hors agenda) | `Tools::invoiceHtFromForm`. Un `amount_ht` **positif** l’emporte. Sinon un `amount_ttc` positif, puis un ancien `amount` positif, est un TTC converti en `round(TTC / 1,2, 2)`. | Une ligne de type `T` via `InvoiceService::buildOnDemandLines` : `[description, "20%", HT, 1, 1, "T"]`. **P.U. HT** et le total de ligne sont ce HT, quantité **1,00**. |

`course_id` est toujours enregistré s’il a été posté.

Un HT à **0**, un formulaire vide ou un résultat négatif est refusé (erreur `amount_ht`, `invoice_amount_must_be_positive`). Aucune facture n’est créée. `invoiceHtFromForm(0, 120)` vaut quand même 100 : un HT non positif laisse passer un TTC positif.

Le pied (`InvoiceGenerator::drawInvoiceDetailsTable`) ne somme pas les lignes. **Total HT** = `Invoice::amountHt()`, **Total TVA 20%** = `amountTtc() − amountHt()`, **Total TTC** = `amountTtc()`. `amountStoredAsHt()` n’est vrai que si le montant stocké égale le HT planning lié à 0,02 près. Une facture à la demande actuelle (pas de total planning correspondant) est donc relue en TTC.

Formulaire de création (`resources/views/invoice/create.blade.php`) : champs texte `amount_ht` et `amount_ttc`. Alpine convertit en direct (`× 1,2` / `÷ 1,2`, deux décimales). Indication : `invoice_amount_vat_hint`. `mergeInvoiceDecimalFields` retire les espaces et les espaces insécables, et change les virgules en points avant validation. Quand le GET a chargé des lignes de planning (`cmd=detailed` et des sessions existent), les deux champs sont **en lecture seule** et préremplis avec ce HT. Le total d’aperçu est HT × 1,2.

**Facturer** / **Facturer tout le mois** depuis l’école passent `cmd=detailed` : l’aperçu correspond à ce que `store` tarifiera. **Créer une facture** depuis les factures (`invoice.create?school_id=`) ne passe pas `cmd`. L’aperçu peut être vide et les champs saisissables alors que des sessions existent pour le mois posté. `store` ignore alors les montants saisis et tarifie le planning. Changer le mois, l’année ou le périmètre change la fenêtre de recherche avec les valeurs postées ; le select de périmètre ne recharge toujours pas l’aperçu.

Édition (`invoice/edit`) : un champ nommé `amount`, libellé **Montant HT**, valeur `amountHt()`. La mise à jour traite ce champ comme du HT (à la création, l’ancien champ `amount` est du TTC). Elle stocke le TTC et régénère le PDF avec `resolveLines` : planning lié, puis `getInvoiceDetails`, puis une ligne à la demande à partir de la description et du nouveau HT. Modifier le HT d’une facture d’agenda change le pied et laisse les prix unitaires des lignes sur les tarifs du planning. Une facture payée (`paid_at` renseigné) n’est pas éditable.

Tests : `tests/Feature/OnDemandInvoiceAmountTest.php`, `tests/Unit/InvoiceAmountConversionTest.php`.

### Commentaire de facture

`invoices.comment` est un texte nullable, validé `nullable|string|max:2000`. Une valeur vide est stockée `null`.

| Surface | Comportement |
|---------|--------------|
| Création et édition | Textarea. Aide : affiché sur l’écran de la facture et sur le PDF. L’édition ne change pas `course_id`. Une facture payée (`paid_at` renseigné) n’est pas éditable. La mise à jour régénère le PDF. |
| Liste Trésorerie | Sous la description (`.invoice-table__comment`, `white-space: pre-wrap`). |
| PDF | Une fois, dans la colonne **Désignation**, juste sous la première ligne de type `T` (titre de cours). `InvoiceGenerator::drawDesignationComment` : Helvetica Bold **12 pt** (`setTitleFont`), `MultiCell` largeur **108**. Une facture multi-cours ne le répète pas sous les titres suivants. Sans ligne `T`, il est dessiné une fois après la dernière ligne. Il n’est pas sous les conditions de règlement. |
| CII | `IncludedNote` supplémentaire, code sujet **`AAI`**, après les mentions `PMT`, `PMD`, `AAB`. Un commentaire réduit à des espaces est omis. Ce n’est pas une ligne tarifée. |

### Récupération du document

Si le PDF disparaît ensuite, l’ouverture de la facture le régénère avec `InvoiceService::ensurePdfOnDisk()`. Le service utilise d’abord les lignes de planning rattachées, puis `Tools::getInvoiceDetails()` pour le mois, l’école et le `course_id` de la facture, et enfin une ligne manuelle à partir de la description et du montant HT.

## Bouton « Sauter aux sessions non facturées »

- Recherche en arrière mois par mois la dernière période avec au moins une session sans facture
- **Désactivé** s’il n’existe aucun mois antérieur non facturé
- Méthodes modèle : `School::findPreviousUnbilledPeriod()`, `hasPreviousUnbilledPeriod()`

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `app/Http/Controllers/BillingController.php` | Navigation période, setBill, jump unbilled |
| `app/Http/Controllers/InvoiceController.php` | Montants HT/TTC, périmètre, commentaire, téléchargement, verrou payé |
| `app/Http/Utility/Tools.php` | TVA 1,2, `invoiceHtFromForm`, `getInvoiceDetails` |
| `app/Services/InvoiceService.php` | PDF, régénération, rattachement planning selon le cours |
| `app/Classes/InvoiceGenerator.php` | Commentaire dans la colonne Désignation |
| `app/Http/Controllers/SchoolController.php` | Chargement données billing sur `show` |
| `app/Models/School.php` | `getBillingPlanning()`, recherche non facturé |
| `app/Models/User.php` | `getUnbilledStatsBySchool()` pour la liste |
| `resources/css/bills.css` | Styles section facturation |

## Voir aussi

- [V2 — navigation](v2-navigation-modules.md)
- [V2 — trésorerie & rapprochement bancaire](v2-tresorerie-rapprochement-bancaire.md)
- [Facturation électronique](facturation-electronique.md)
- [Modèle de données](modele-donnees-formation.md)
