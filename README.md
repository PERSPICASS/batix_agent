# BATIX Growth / batix_agent

Agent marketing digital autonome pour BatixPro.

Le projet est séparé de `batix_Saas`. PostgreSQL est installé directement sur le VPS ; il n'est pas lancé dans Docker pour `batix_agent`. Redis, Laravel, Nginx, le worker et le scheduler restent conteneurisés.

## Architecture production

- PostgreSQL : service du VPS ;
- `app` : Laravel / PHP-FPM ;
- `nginx` : exposition via Traefik ;
- `queue` : Laravel queue worker ;
- `scheduler` : tâches planifiées ;
- `redis` : cache et queues dans Docker.

Les services PHP utilisent `host.docker.internal:host-gateway` pour joindre PostgreSQL sur l'hôte Linux.

## Configuration base de données

Le `.env` de production doit utiliser :

```env
DB_CONNECTION=pgsql
DB_HOST=host.docker.internal
DB_PORT=5432
DB_DATABASE=batix_agent
DB_USERNAME=batix_agent
DB_PASSWORD=CHANGE_ME_DB_PASSWORD
DB_SSLMODE=disable
```

Créer sur PostgreSQL du VPS une base et un rôle dédiés `batix_agent`. PostgreSQL doit accepter les connexions provenant du subnet Docker du réseau `batix_agent_agent_internal` via `pg_hba.conf`, sans exposer le port 5432 publiquement.

Pour connaître le subnet après création du réseau :

```bash
docker network inspect batix_agent_agent_internal --format '{{(index .IPAM.Config 0).Subnet}}'
```

## Installation VPS

Répertoire attendu :

```text
/opt/batix/apps/prod/batix_agent
```

Le déploiement utilise `docker-compose.yml` + `docker-compose.prod.yml` et la branche `prod`, sur le même principe que `PERSPICASS/batix_Saas`.

Le workflow GitHub Actions attend `VPS_HOST`, `VPS_USER` et `VPS_SSH_KEY`.

Ne jamais commiter `.env` ni les secrets OpenAI, Meta, WhatsApp ou PostgreSQL.

## Administrateur

Les comptes de connexion sont stockés dans la table `admin_users`. Après les migrations, créez ou mettez à jour un administrateur :

```bash
php artisan growth:admin admin --name="Administrateur"
```

La commande demande le mot de passe de façon masquée. Vous pouvez la relancer à tout moment pour modifier le mot de passe ou réactiver le compte.

## Raccordement WhatsApp Cloud API

Le tableau des prospects peut envoyer un message WhatsApp et importer les réponses reçues dans le journal commercial. Configurez les variables suivantes dans le `.env` du serveur :

```env
META_APP_SECRET=...
META_WEBHOOK_VERIFY_TOKEN=choisissez-une-valeur-secrete
WHATSAPP_GRAPH_VERSION=v21.0
WHATSAPP_PHONE_NUMBER_ID=...
WHATSAPP_ACCESS_TOKEN=...
```

Dans la configuration Webhooks de l’application Meta, utilisez l’URL publique `https://votre-domaine/webhooks/whatsapp` et la même valeur de vérification. Le serveur valide les requêtes entrantes avec `META_APP_SECRET`, crée un prospect pour un nouveau numéro et évite les doublons de messages via leur identifiant externe.

Une fois ces valeurs renseignées et la configuration rechargée, l’interface affiche « WhatsApp connecté » et propose l’envoi depuis chaque fiche prospect.

## Développement local

Prérequis : PHP 8.2+ avec les extensions SQLite et PostgreSQL, Composer, Node.js 22+ et npm.

```bash
cp .env.example .env
php artisan key:generate
composer install
npm install
```

Pour travailler sans PostgreSQL ni Redis, utilisez SQLite et les drivers en mémoire dans votre `.env` local :

```env
APP_ENV=local
DB_CONNECTION=sqlite
DB_DATABASE=/chemin/absolu/vers/batix_agent/database/database.sqlite
CACHE_STORE=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

Créez ensuite le fichier SQLite, exécutez les migrations et démarrez les processus de développement :

```bash
touch database/database.sqlite
php artisan migrate
npm run dev
php artisan serve
```

## Vérifications avant livraison

```bash
composer lint
composer test
npm run build
```

La CI exécute ces vérifications sur les branches `main` et `prod`, ainsi que pour les pull requests, avant le build de l’image Docker de production.
