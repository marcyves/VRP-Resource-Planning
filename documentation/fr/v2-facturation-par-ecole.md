# V2 — Facturation par école

**EN:** [v2-billing-per-school.md](../en/v2-billing-per-school.md)

## Changement majeur

La **préparation** d’un client est sur la fiche école (`school/show?panel=billing`). Le menu **Facturer** est la liste du mois devant cette fiche (`/facturer`). L’ancien onglet Agenda (`/billing`) n’est plus l’entrée.

### Avant (v1)

- Onglets Agenda : Planning · Calendrier · **Préparation facturation** · Par date
- Vue globale toutes écoles confondues

### Après (v2)

- Le menu **Facturer** ouvre la liste du mois des clients qui ont encore des séances non facturées (`/facturer`).
- La préparation d’un client reste sur `school/show?panel=billing`.
- Les anciennes URL `/billing*` redirigent toujours vers l’école en session.

## Liste du mois (`/facturer`)

`BillingNavController` (`billing.prepare`). Aucune école n’est requise en session (`BillingNavTest`).

| Route | Nom | Effet |
|-------|-----|--------|
| `GET /facturer` | `billing.prepare` | Mois déjà dans la session agenda |
| `GET /facturer/previous` | `billing.prepare.previous` | −1 mois |
| `GET /facturer/next` | `billing.prepare.next` | +1 mois |
| `GET /nav/billing` | `nav.billing` | Redirection vers `billing.prepare` |

La liste elle-même, ainsi que précédent et suivant, écrivent les clés de session de l’agenda : `current_year`, `current_month` et `billing_year`, toutes tirées de ce mois calendaire. Changer de mois dans Facturer déplace donc la période de l’agenda et le filtre d’année de la liste des écoles.

Les lignes viennent de `Planning::getDetails` pour ce mois, regroupées par `PlanningBillingSummary::fromPlanning`. Le HT est `Tools::planningGain`. Le TTC est `round(HT × 1,2, 2)` par école (la classe écrit le littéral `1.2`, la même valeur que `Tools::VAT_MULTIPLIER`).

Les trois chiffres sous la période couvrent **toutes** les écoles qui ont une séance ce mois-là, y compris celles déjà entièrement facturées :

| Chiffre | Formule |
|---------|---------|
| Déjà facturé | `round(monthTotal − monthRemaining, 2)` |
| Reste à facturer | somme de `unbilled_amount_ttc` |
| Total | somme de `amount_ttc` |

Les cartes utilisent `.resource-grid` / `.billing-prepare-card` (la même grille que les écoles et les groupes). Une carte n’apparaît que si `unbilled_sessions > 0`. Tri : nombre de séances non facturées, puis HT non facturé, puis heures (`courses.session_length` sur la ligne de planning — tri seulement, pas affiché). Le montant déjà facturé d’une carte est `amount_ttc − unbilled_amount_ttc`. **Préparer** ouvre `school.show` avec `panel=billing`.

Une liste de cartes vide affiche quand même les totaux de la période et `messages.billing_prepare_empty`.

Le titre de période passe par `TerminologyLocale::normalizeBaseLocale` et Carbon `translatedFormat('F')`, puis l’année.

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

`amount` stocké : un `amount` renseigné est enregistré en TTC. Un montant vide devient `round(HT planning × 1,2, 2)`. Les sessions trouvées pilotent toujours le PDF. Une facture manuelle (aucune session, montant renseigné) stocke une ligne forfaitaire et conserve `course_id` s’il a été posté.

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
| `app/Http/Controllers/BillingNavController.php` | Liste Facturer du mois |
| `app/Support/PlanningBillingSummary.php` | HT / TTC par école pour cette liste |
| `app/Http/Controllers/BillingController.php` | Navigation période, setBill, jump unbilled |
| `app/Http/Controllers/InvoiceController.php` | Périmètre, commentaire, téléchargement, verrou payé |
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
