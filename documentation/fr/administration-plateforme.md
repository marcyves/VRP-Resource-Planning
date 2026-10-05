# Administration plateforme et provisionnement multi-tenant

**EN:** [platform-administration.md](../en/platform-administration.md)

## Intention

VRP est multi-tenant. Un compte **super administrateur** gère les entreprises et leurs premiers utilisateurs depuis l'espace plateforme, tandis que les modules métier restent limités à une seule entreprise.

Le parcours plateforme sert à contrôler l'onboarding :

- créer un premier super administrateur en ligne de commande ;
- se connecter à `/super-admin/companies` ;
- créer chaque entreprise avec son préfixe de facturation, son profil terminologique et son premier administrateur ;
- ajouter les autres utilisateurs depuis la fiche entreprise.

L'inscription publique est désactivée par défaut et ne provisionne pas d'entreprise. Utiliser le parcours super admin pour l'onboarding courant. Les invités arrivent sur `/` (`WelcomeController`) et demandent un compte sur `/demande-acces`.

## Modèle d'accès

| Acteur | Données requises | Zone autorisée | Comportement frontière |
|--------|------------------|----------------|------------------------|
| Super admin | `status_id` résout vers `super admin`, `company_id = null` | `/super-admin/companies` | Les routes tenant redirigent vers la liste des entreprises |
| Admin tenant | `company_id` renseigné, rôle `admin` | `/home`, modules métier, `/admin/login-stats` | `/super-admin/*` renvoie 403 ; les stats de connexion sont limitées à l'entreprise |
| Éditeur/lecteur tenant | `company_id` renseigné, rôle `éditeur` ou `rédacteur` | `/home` et modules métier | `/admin/login-stats` et `/super-admin/*` renvoient 403 |
| Utilisateur connecté sans entreprise | Pas super admin et pas de `company_id` | Aucune | Le middleware tenant renvoie 403 |

Les frontières de routes sont dans `routes/web.php` :

- `/super-admin/*` utilise `auth` et l'alias middleware `superadmin` ;
- les routes métier utilisent `auth`, `tenant` et `SetTerminologyLocale` ;
- après login, la redirection passe par `User::homePath()`.

## Runbook de démarrage

1. Exécuter les migrations pour créer le statut `super admin` et rendre `users.company_id` nullable :

   ```bash
   php artisan migrate
   ```

2. Créer le premier compte plateforme :

   ```bash
   php artisan vrp:create-super-admin admin@example.com "Admin plateforme"
   ```

   La commande demande le mot de passe de façon interactive sauf si `--password=` est fourni. Elle valide l'unicité de l'e-mail et les règles de mot de passe Laravel, crée un utilisateur sans `company_id`, puis assigne `Status::superAdminId()`. Éviter `--password` en shell interactif : la valeur peut rester dans l'historique.

3. Se connecter sur `/login`. Un super admin est redirigé vers `/super-admin/companies`.

4. Garder `VRP_ALLOW_REGISTRATION=false` sauf si l'ancien parcours `/register` doit volontairement être exposé.

## Création d'une entreprise

Depuis `/super-admin/companies/create`, un super admin renseigne :

| Champ | Contrainte | Effet |
|-------|------------|-------|
| Nom entreprise | requis, max 255 caractères | Crée `companies.name` |
| Préfixe facture | requis, max 10 caractères, alphanumérique, unique | Stocké en majuscules dans `companies.bill_prefix` |
| Profil terminologique | `education`, `consulting` ou `medical` | Pilote les libellés tenant via `SetTerminologyLocale` |
| Nom/e-mail/mot de passe admin | requis ; e-mail unique ; confirmation mot de passe | Crée le premier utilisateur de l'entreprise |

`CompanyProvisioner` exécute l'opération dans une transaction. Il crée l'entreprise, crée le premier admin tenant avec `status_id = Status::ADMIN` et `mode = Edit`, puis synchronise les coordonnées de contact de l'entreprise depuis cet admin.

## Gestion des utilisateurs tenant

Sur `/super-admin/companies/{company}`, le super admin peut ajouter des utilisateurs à l'entreprise sélectionnée.

| Rôle | Statut stocké | Mode |
|------|---------------|------|
| Administrateur | `Status::ADMIN` | `Edit` |
| Éditeur | `Status::EDITOR` | `Edit` |
| Lecteur | `Status::READER` | `Browse` |

La requête accepte uniquement ces trois rôles tenant ; l'UI ne crée pas de super administrateur à l'intérieur d'une entreprise. Si l'entreprise n'a pas encore de contact et que le nouvel utilisateur est admin, `CompanyUserProvisioner` le définit comme contact.

## Frontières des données tenant

La plupart des données métier sont atteintes via le `company_id` de l'utilisateur connecté.

- Les écoles, factures et groupes sont chargés avec des filtres utilisateur/entreprise.
- Les programmes portent maintenant un `company_id` obligatoire ; `ProgramController` ouvre uniquement les programmes de l'entreprise courante et rattache les nouveaux programmes à cette entreprise.
- Les cours sont filtrés via leur école. La création et la modification de cours exigent aussi un `program_id` appartenant à l'entreprise courante.
- La terminologie vient de `companies.terminology_profile` ; la variable `TERMINOLOGY_PROFILE` sert seulement de repli quand aucune entreprise n'est chargée.

Pour ajouter une fonctionnalité tenant, suivre le même modèle : route protégée par le middleware `tenant` et requêtes filtrées sur l'entreprise de l'utilisateur courant.

## Suppression d'entreprise

La suppression depuis l'espace super admin appelle `CompanyDeleter` dans une transaction. Elle supprime les références contact et compte bancaire de facturation, puis supprime ou détache les données de l'entreprise :

- écoles et leurs mappings calendrier, sources calendrier, documents, liens école-utilisateur, cours, liens groupe-cours et plannings de cours ;
- groupes de l'entreprise et plannings de groupes ;
- factures et programmes de l'entreprise ;
- utilisateurs tenant et leurs liens école-utilisateur ;
- ligne `companies`.

Cette action est destructive et n'est pas un soft delete. Exporter ou sauvegarder les données tenant avant confirmation en production.

## Contrôle de l'inscription

| Réglage | Défaut | Comportement |
|---------|--------|--------------|
| `VRP_ALLOW_REGISTRATION` | `false` | Les requêtes GET et POST `/register` renvoient 404 |
| `VRP_ALLOW_REGISTRATION=true` | opt-in | Le formulaire d'inscription publique est disponible |
| `VRP_ACCOUNT_REQUEST_EMAIL` | `MAIL_FROM_ADDRESS` si la clé est **absente** | Destinataire de `/demande-acces` |

L'inscription publique crée seulement un compte utilisateur ; elle ne crée ni entreprise ni rôle tenant. Pour l'onboarding de production, garder l'inscription désactivée et créer les tenants depuis `/super-admin/companies`.

### Demande de compte (`/demande-acces`)

La page d'accueil et le login pointent vers **Demander un compte** (`account-request.create`). Ce n'est **pas** un auto-provisionnement. Le chrome public est le layout marketing (wordmark seul — [design system CSS](v2-design-system-css.md#canvas-marketing-public)).

1. L'invité envoie nom d'entreprise, contact, e-mail, téléphone optionnel, profil terminologique, message (`StoreAccountRequestRequest` ; POST limité `5,1`).
2. `AccountRequestController` envoie `AccountRequestMail` à `config('vrp.account_request_email')`.
3. L'utilisateur voit un flash de succès. Aucune ligne user/company n'est créée.
4. Un opérateur provisionne ensuite le tenant depuis `/super-admin/companies`.

Si `VRP_ACCOUNT_REQUEST_EMAIL` est présent mais **vide** (comme dans `.env.example`), Laravel n'applique pas le repli `MAIL_FROM_ADDRESS`. Le formulaire redirige quand même en succès ; le contrôleur `report()` une `RuntimeException` et n'envoie rien.

Couverture : `tests/Feature/LandingPageTest.php`.

## Dépannage

| Symptôme | Vérification |
|----------|--------------|
| 403 super admin sur `/super-admin/companies` | L'utilisateur a un `status_id` correspondant à la ligne `super admin` |
| Super admin envoyé vers `/home` puis redirigé | Comportement normal : le middleware tenant renvoie vers l'espace plateforme |
| 403 utilisateur tenant sur les pages métier | L'utilisateur n'est pas super admin et doit avoir un `company_id` |
| Création entreprise refusée sur le préfixe | Préfixe alphanumérique, 10 caractères max, unique entre entreprises |
| Un cours ne peut pas utiliser un programme | Le programme appartient à une autre entreprise ou n'a pas de `company_id` |
| `/register` renvoie 404 | `VRP_ALLOW_REGISTRATION` vaut false, le défaut |
| Demande de compte OK mais pas d'e-mail | `VRP_ACCOUNT_REQUEST_EMAIL` est vide ; omettre la clé pour retomber sur `MAIL_FROM_ADDRESS`, ou renseigner une vraie boîte |
| POST `/demande-acces` en 429 | Limiteur `5,1` sur `account-request.store` |
| Un utilisateur connecté voit encore la landing | `WelcomeController` doit rediriger vers `User::homePath()` ; vérifier la session |
| Toutes les connexions sont dans la ville du proxy | `TRUSTED_PROXIES` est absent devant l'hôte. La production pose `TRUSTED_PROXIES=*` |
| Les IP publiques restent « lieu inconnu » | Fichier GeoLite2 absent et repli HTTP coupé, en timeout, ou mis en cache comme échec (`login_stats.geo_*` dans le journal) |
| L'admin d'entreprise ne trouve pas un e-mail inconnu | Attendu : cet échec a `company_id = null` ; seul le super admin le voit |

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `app/Console/Commands/CreateSuperAdminCommand.php` | Commande de bootstrap |
| `app/Http/Middleware/EnsureSuperAdmin.php` | Protection des routes plateforme |
| `app/Http/Middleware/EnsureTenantUser.php` | Garde les routes métier dans le périmètre entreprise |
| `app/Http/Controllers/SuperAdmin/CompanyController.php` | Liste, création, modification et suppression entreprise |
| `app/Http/Controllers/SuperAdmin/CompanyUserController.php` | Ajout d'utilisateurs tenant |
| `app/Http/Controllers/AccountRequestController.php` | Mail invité `/demande-acces` |
| `app/Http/Controllers/WelcomeController.php` | Landing invitée `/` ; redirection si déjà connecté |
| `resources/views/layouts/marketing.blade.php` | Chrome public (wordmark, skip link, bascule de thème) |
| `app/Services/CompanyProvisioner.php` | Création transactionnelle entreprise + premier admin |
| `app/Services/CompanyUserProvisioner.php` | Création utilisateur tenant et synchronisation contact |
| `app/Services/CompanyDeleter.php` | Nettoyage destructif d'un tenant |
| `config/vrp.php` | Gardes `VRP_ALLOW_REGISTRATION` et `VRP_ACCOUNT_REQUEST_EMAIL` |
| `config/terminology.php` | Profils terminologiques disponibles |
| `tests/Feature/SuperAdmin/*` | Couverture routes plateforme et provisioning |
| `tests/Feature/LandingPageTest.php` | Accueil + demande de compte |
| `tests/Feature/ProgramCompanyScopeTest.php` | Isolation tenant des programmes |
| `app/Listeners/RecordLoginStatistics.php` | `Login` / `Failed` / `Lockout` → `login_events` |
| `tests/Feature/LoginStatisticsTest.php` | Enregistrement, périmètre admin, filtre d'issue |

## Pièges fréquents

- Garder `VRP_ALLOW_REGISTRATION=false` sur les déploiements multi-tenant pilotés. L'inscription publique crée un utilisateur sans contexte tenant ; le middleware métier exige `company_id` pour accéder aux modules classiques.
- Ne pas traiter `/demande-acces` comme une inscription. Cela envoie seulement un mail aux opérateurs.
- Ne pas rattacher de données métier à un super admin. Le compte plateforme a volontairement `company_id = null`.
- Choisir les préfixes facture avec soin. Ils sont uniques, mis en majuscules et utilisés pour associer d'anciens identifiants facture de planning.
- Exécuter les migrations avant de créer le premier super admin ; sinon le statut requis et `users.company_id` nullable peuvent manquer.

## Statistiques de connexion

Journal des tentatives de connexion, réservé aux admins (libellé sidebar **Connexions**). L'enregistrement ne bloque jamais l'authentification : `RecordLoginStatistics` avale ses erreurs (`login_stats.record_failed`), et un échec de géolocalisation enregistre quand même la ligne avec un lieu vide.

### Où ça s'affiche

| Acteur | Sidebar | Route | Périmètre |
|--------|---------|-------|-----------|
| Super admin | Icône bouclier après Entreprises et Facturation électronique | `GET /super-admin/login-stats` (`super-admin.login-stats.index`) | Toutes les lignes, y compris les échecs sans entreprise |
| Admin d'entreprise (`Status::ADMIN`) | Icône bouclier après le séparateur Trésorerie, avant Référentiel | `GET /admin/login-stats` (`login-stats.index`) | Lignes dont le `company_id` est celui de l'admin |
| Éditeur / rédacteur | Pas de lien | Même URL tenant | 403 (`User::isAdmin()`) |

Un admin d'entreprise qui ouvre l'URL super admin reçoit 403 du middleware `superadmin`.

### Ce qui est stocké

Table `login_events` (migration `2026_10_01_100000_create_login_events_table`). Pas de `created_at` / `updated_at`. Le mot de passe n'est jamais écrit.

| Colonne | Source |
|---------|--------|
| `username` | E-mail de la tentative, trimé. Les succès utilisent `$user->email` |
| `user_id` | Utilisateur reconnu, ou null |
| `company_id` | Entreprise de cet utilisateur, ou null |
| `ip` | `$request->ip()`, ou `0.0.0.0` si vide. Dépend de `TRUSTED_PROXIES` |
| `geo_label` | Voir [Géolocalisation](#géolocalisation) |
| `success` / `locked_out` | Issue |
| `occurred_at` | `now()` à l'écriture |

`EventServiceProvider` abonne `RecordLoginStatistics`, qui appelle `LoginEventRecorder`.

| Événement auth | Ligne |
|----------------|-------|
| `Login` | `success = true`, `locked_out = false` |
| `Failed` | `success = false`, `locked_out = false`. Un e-mail connu est rattaché à l'utilisateur et à l'entreprise même si le mot de passe est faux |
| `Lockout` | `success = false`, `locked_out = true`. `Auth::attempt` n'est pas appelé : cette requête n'écrit pas en plus une ligne `Failed` |

`LoginRequest` autorise **5** échecs par `transliterate(e-mail en minuscules)|ip`. La tentative suivante déclenche `Lockout`, et chaque tentative ultérieure tant que le limiteur tient ajoute une ligne de verrouillage. Le tableau distingue le verrouillage ; le filtre **Échec** inclut quand même ces lignes (`success = false`).

Les e-mails inconnus restent `company_id = null` : l'admin d'entreprise ne les voit pas. Supprimer une entreprise cascade ses événements. Supprimer un utilisateur met `user_id` à null et conserve l'identifiant. Il n'y a pas de commande de purge ; la table grossit jusqu'à suppression avec l'entreprise ou à la main.

### Écran

`resources/views/login-stats/index.blade.php`, styles dans `resources/css/login-stats.css`.

- Les compteurs et la barre succès/échec portent sur le jeu **filtré**. Ce n'est pas une série temporelle.
- Filtres GET, conservés d'une page à l'autre : `outcome` = `all` \| `success` \| `failed` ; `from` / `to` sont des jours calendaires inclusifs sur `occurred_at` (`00:00:00` à `23:59:59`) ; `q` est une sous-chaîne sur `username`, `ip` et le `geo_label` stocké (max 255). Les super admins ont aussi `company_id` : vide = toutes les entreprises, `none` = `company_id` null, sinon un id d'entreprise. Les admins tenant ignorent `company_id`.
- Tableau de 50 lignes, `occurred_at` puis `id` décroissants. Les heures s'affichent dans `config('app.timezone')`.
- Texte du lieu : `local` stocké devient `messages.login_stats_geo_local` (« Réseau local ») ; null ou vide devient le libellé inconnu ; toute autre valeur est affichée telle quelle.

### Géolocalisation

`App\Services\GeoLocator`. Les adresses privées ou réservées (loopback, `0.0.0.0`, autres plages non publiques) sont stockées comme le littéral `local` et ne sont pas interrogées.

Les IP publiques passent par GeoLite2-City (`geoip2/geoip2`) quand `LOGIN_STATS_GEO_MMDB` est lisible. Le libellé est `ville, subdivision, pays`. En cas d'échec et si `LOGIN_STATS_GEO_HTTP` vaut true, l'app fait un GET sur `LOGIN_STATS_GEO_HTTP_URL` (défaut `https://ipwho.is/{ip}`) avec `LOGIN_STATS_GEO_HTTP_TIMEOUT` (1,5 s). Cette requête envoie l'IP du client à ipwho.is. `LOGIN_STATS_GEO_HTTP=false` limite la recherche au fichier local.

Clé de cache `login-stats-geo:{ip}` : un libellé dure `LOGIN_STATS_GEO_CACHE_TTL` (défaut 30 jours) ; un échec dure `LOGIN_STATS_GEO_FAILURE_CACHE_TTL` (défaut 1 heure). Liste des variables : [Configuration](configuration.md).

### Pièges

- Sans `TRUSTED_PROXIES` devant l'hôte, chaque ligne est l'adresse du proxy et est géolocalisée comme ce proxy. Le déploiement production pose `TRUSTED_PROXIES=*`.
- Sail et un navigateur local enregistrent `local`, pas une ville. Chercher « Réseau local » ne trouve pas la valeur stockée `local`.
- Un `.mmdb` absent ou illisible, ou l'absence de la classe `GeoIp2\Database\Reader`, saute le fichier. Le HTTP part ensuite, sauf s'il est désactivé. Ne pas committer la base (~60 Mo).
- `phpunit.xml` pose `LOGIN_STATS_GEO_HTTP=false`. Couverture : `tests/Feature/LoginStatisticsTest.php`, `tests/Unit/GeoLocatorTest.php`.

## Voir aussi

- [Configuration](configuration.md)
- [Phase 1 — terminologie](phase-1-terminologie.md)
- [V2 — navigation et modules](v2-navigation-modules.md)
