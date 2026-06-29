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
