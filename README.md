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
