# Administration plateforme et provisionnement multi-tenant

**EN:** [platform-administration.md](../en/platform-administration.md)

## Intention

VRP est multi-tenant. Un compte **super administrateur** gère les entreprises et leurs premiers utilisateurs depuis l'espace plateforme, tandis que les modules métier restent limités à une seule entreprise.

Le parcours plateforme sert à contrôler l'onboarding :

- créer un premier super administrateur en ligne de commande ;
- se connecter à `/super-admin/companies` ;
- créer chaque entreprise avec son préfixe de facturation, son profil terminologique et son premier administrateur ;
- ajouter les autres utilisateurs depuis la fiche entreprise.

L'inscription publique est désactivée par défaut et ne provisionne pas d'entreprise. Utiliser le parcours super admin pour l'onboarding courant.

## Modèle d'accès

| Acteur | Données requises | Zone autorisée | Comportement frontière |
|--------|------------------|----------------|------------------------|
| Super admin | `status_id` résout vers `super admin`, `company_id = null` | `/super-admin/companies` | Les routes tenant redirigent vers la liste des entreprises |
| Admin/éditeur/lecteur tenant | `company_id` renseigné, rôle `admin`, `éditeur` ou `rédacteur` | `/home` et modules métier | `/super-admin/*` renvoie 403 |
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

   La commande demande le mot de passe de façon interactive sauf si `--password=` est fourni. Elle valide l'unicité de l'e-mail et les règles de mot de passe Laravel, crée un utilisateur sans `company_id`, puis assigne `Status::superAdminId()`.

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

L'inscription publique crée seulement un compte utilisateur ; elle ne crée ni entreprise ni rôle tenant. Pour l'onboarding de production, garder l'inscription désactivée et créer les tenants depuis `/super-admin/companies`.

## Dépannage

| Symptôme | Vérification |
|----------|--------------|
| 403 super admin sur `/super-admin/companies` | L'utilisateur a un `status_id` correspondant à la ligne `super admin` |
| Super admin envoyé vers `/home` puis redirigé | Comportement normal : le middleware tenant renvoie vers l'espace plateforme |
| 403 utilisateur tenant sur les pages métier | L'utilisateur n'est pas super admin et doit avoir un `company_id` |
| Création entreprise refusée sur le préfixe | Préfixe alphanumérique, 10 caractères max, unique entre entreprises |
| Un cours ne peut pas utiliser un programme | Le programme appartient à une autre entreprise ou n'a pas de `company_id` |
| `/register` renvoie 404 | `VRP_ALLOW_REGISTRATION` vaut false, le défaut |

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `app/Console/Commands/CreateSuperAdminCommand.php` | Commande de bootstrap |
| `app/Http/Middleware/EnsureSuperAdmin.php` | Protection des routes plateforme |
| `app/Http/Middleware/EnsureTenantUser.php` | Garde les routes métier dans le périmètre entreprise |
| `app/Http/Controllers/SuperAdmin/CompanyController.php` | Liste, création, modification et suppression entreprise |
| `app/Http/Controllers/SuperAdmin/CompanyUserController.php` | Ajout d'utilisateurs tenant |
| `app/Services/CompanyProvisioner.php` | Création transactionnelle entreprise + premier admin |
| `app/Services/CompanyUserProvisioner.php` | Création utilisateur tenant et synchronisation contact |
| `app/Services/CompanyDeleter.php` | Nettoyage destructif d'un tenant |
| `config/vrp.php` | Garde `VRP_ALLOW_REGISTRATION` |
| `config/terminology.php` | Profils terminologiques disponibles |
| `tests/Feature/SuperAdmin/*` | Couverture routes plateforme et provisioning |
| `tests/Feature/ProgramCompanyScopeTest.php` | Isolation tenant des programmes |
# Administration plateforme

**EN:** [platform-administration.md](../en/platform-administration.md)

VRP est une application multi-tenant : chaque entreprise cliente possède ses utilisateurs et ses données métier, tandis qu'un compte **super admin** séparé provisionne et maintient les entreprises depuis `/super-admin/companies`.

## Intention

| Sujet | Comportement |
|-------|--------------|
| Utilisateurs métier | `company_id` obligatoire ; accès aux modules VRP classiques (`/home`, agenda, trésorerie, fiche entreprise). |
| Super admins | Pas de `company_id` ; accès limité à l'administration plateforme. |
| Inscription publique | Désactivée par défaut avec `VRP_ALLOW_REGISTRATION=false` ; les comptes métier doivent être provisionnés depuis l'UI plateforme. |

## Initialiser un super admin

Exécuter les migrations avant la création du compte pour disposer du statut `super admin` et de `users.company_id` nullable :

```bash
php artisan migrate
php artisan vrp:create-super-admin admin@example.com "Platform Admin"
```

La commande demande le mot de passe de façon sécurisée quand `--password` est omis. Éviter `--password` en shell interactif : la valeur peut rester dans l'historique.

Après connexion, le super admin est redirigé vers :

```text
/super-admin/companies
```

## Frontières d'accès

| Zone | Route / middleware | Résultat |
|------|--------------------|----------|
| Administration plateforme | `/super-admin/companies*` avec `auth`, `superadmin` | Seul `User::isSuperAdmin()` entre ; les autres utilisateurs reçoivent 403. |
| Application métier | `/home`, `/dashboard`, agenda, facturation, trésorerie, fiche entreprise avec `auth`, `tenant`, `SetTerminologyLocale` | Les super admins sont renvoyés vers la liste entreprises ; les utilisateurs sans `company_id` reçoivent 403. |
| Connexion / redirections invité | `AuthenticatedSessionController`, `RedirectIfAuthenticated` | `User::homePath()` choisit l'accueil super-admin ou métier. |

La sidebar suit la même séparation : les super admins ne voient que la liste entreprises, les utilisateurs métier voient agenda, trésorerie et plan de charge.

## Parcours de provisionnement entreprise

Utiliser **Créer une entreprise** depuis `/super-admin/companies`.

| Champ | Contrainte / effet |
|-------|--------------------|
| Nom entreprise | Obligatoire, 255 caractères max. |
| Préfixe facture | Obligatoire, alphanumérique, 10 caractères max, unique dans `companies.bill_prefix` ; stocké en majuscules et utilisé dans les numéros de facture. |
| Profil terminologique | Une des valeurs configurées pour l'entreprise (`education`, `consulting`, `medical`). |
| Nom/e-mail/mot de passe administrateur | Crée le premier admin métier ; e-mail unique et mot de passe conforme aux règles Laravel. |

`CompanyProvisioner` encapsule la création dans une transaction :

1. crée `companies` avec le préfixe facture et le profil terminologique ;
2. crée le premier admin métier avec `status_id = Status::ADMIN`, `mode = Edit` et le nouveau `company_id` ;
3. copie les coordonnées de l'admin vers `companies.contact_user_id`, `email`, `phone` et `website` quand elles existent.

Exemple de données :

```text
company_name: Acme Formation
bill_prefix: ACM
terminology_profile: education
admin_email: alice@acme.test
```

## Gérer une entreprise existante

La page détail (`super-admin.companies.show`) permet de :

- modifier le profil terminologique de l'entreprise ;
- lister les utilisateurs de l'entreprise ;
- ajouter un utilisateur avec le rôle **Administrateur**, **Éditeur** ou **Lecteur** ;
- supprimer l'entreprise et ses données associées.

Lors de l'ajout d'un utilisateur, `CompanyUserProvisioner` affecte `mode = Edit` aux rôles admin/éditeur et `mode = Browse` aux lecteurs. Si l'entreprise n'a pas encore de contact et que le nouvel utilisateur est admin, le contact entreprise est synchronisé depuis cet utilisateur.

## Isolation tenant et données partagées

Le chantier super-admin isole aussi les programmes par entreprise :

- `programs.company_id` est obligatoire après migration ;
- `Program::forCurrentCompany()` limite les listes à l'entreprise de l'utilisateur connecté ;
- `ProgramController` renvoie 404 sur le programme d'une autre entreprise ;
- `CourseController` vérifie que `program_id` appartient à l'entreprise courante avant création ou mise à jour d'un cours.

Cela évite qu'un tenant voie ou rattache des cours aux programmes d'une autre entreprise.

## Suppression d'une entreprise

La suppression est destructive. `CompanyDeleter` nettoie les pointeurs contact/compte bancaire de l'entreprise, puis supprime les données tenant dans une transaction :

- écoles, cours, calendriers, documents, liens école-utilisateur ;
- groupes, liens groupe-cours, plannings liés aux groupes/cours de l'entreprise ;
- factures, programmes, utilisateurs ;
- l'entreprise elle-même.

Il efface aussi les anciens `invoice_id` de planning qui commencent par le préfixe facture de l'entreprise avant suppression des factures. Les soldes de trésorerie, dépenses, banques, comptes bancaires, imports bancaires, lignes de relevé et rapprochements s'appuient sur leurs clés étrangères `company_id` en `cascadeOnDelete` quand la ligne entreprise est supprimée. Utiliser la confirmation de la zone sensible uniquement quand les données du tenant doivent disparaître définitivement.

## Configuration et carte source

| Usage | Fichier |
|-------|---------|
| Flag inscription | `config/vrp.php`, `.env.example` (`VRP_ALLOW_REGISTRATION`) |
| CLI super-admin | `app/Console/Commands/CreateSuperAdminCommand.php` |
| Frontières routes | `routes/web.php`, `app/Http/Middleware/EnsureSuperAdmin.php`, `app/Http/Middleware/EnsureTenantUser.php` |
| Provisionnement/suppression | `app/Services/CompanyProvisioner.php`, `CompanyUserProvisioner.php`, `CompanyDeleter.php` |
| UI entreprise | `app/Http/Controllers/SuperAdmin/*`, `resources/views/super-admin/companies/*` |
| Isolation programmes | `app/Models/Program.php`, `app/Http/Controllers/ProgramController.php`, `CourseController.php` |
| Couverture tests | `tests/Feature/SuperAdmin/*`, `tests/Feature/ProgramCompanyScopeTest.php` |

## Pièges fréquents

- Garder `VRP_ALLOW_REGISTRATION=false` sur les déploiements multi-tenant pilotés. L'inscription publique crée un utilisateur sans contexte tenant ; le middleware métier exige `company_id` pour accéder aux modules classiques.
- Ne pas rattacher de données métier à un super admin. Le compte plateforme a volontairement `company_id = null`.
- Choisir les préfixes facture avec soin. Ils sont uniques, mis en majuscules et utilisés pour associer d'anciens identifiants facture de planning.
- Exécuter les migrations avant de créer le premier super admin ; sinon le statut requis et `users.company_id` nullable peuvent manquer.

## Voir aussi

- [Configuration](configuration.md)
- [Phase 1 — terminologie](phase-1-terminologie.md)
- [V2 — navigation et modules](v2-navigation-modules.md)
