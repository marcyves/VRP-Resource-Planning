---
description: Deployment steps for production
---

# Guide de mise en production (Laravel + Vite)

Pour mettre votre application en ligne, suivez ces étapes sur votre serveur de production.

### 1. Préparer l'environnement

Assurez-vous que votre serveur dispose de :

- PHP >= 8.2
- MySQL / MariaDB
- Node.js & NPM
- Nginx ou Apache

### 2. Configuration du fichier .env

Copiez votre `.env.example` en `.env` et configurez les variables suivantes :

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=votre_base
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passee
```

### 3. Installation des dépendances

// turbo

```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### 4. Base de données

// turbo

```bash
php artisan migrate --force
```

### 5. Optimisations Laravel

// turbo

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Permissions

Assurez-vous que les dossiers `storage` et `bootstrap/cache` sont accessibles en écriture par le serveur web :

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data .
```

### 7. Génération de la clé (si nouvelle install)

```bash
php artisan key:generate --show
```
