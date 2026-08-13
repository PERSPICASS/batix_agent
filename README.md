# BATIX Growth / batix_agent

Agent marketing digital autonome pour BatixPro.

Le projet est volontairement séparé de `batix_Saas` : il possède son propre runtime Docker, sa base PostgreSQL, Redis, son worker et son scheduler. Les futures intégrations avec BatixPro, Meta/Facebook et WhatsApp passent par API/webhooks et non par une dépendance UI dans BatixPro.

## MVP actuel

- campagnes marketing ;
- génération IA de 3 contenus complémentaires : post, script Reel et publicité ;
- validation/rejet manuel des contenus ;
- prospects et provenance des leads ;
- scoring IA sur 100 ;
- prochaine action commerciale suggérée ;
- script WhatsApp proposé ;
- aucune publication Meta ni aucun envoi WhatsApp automatique à ce stade.

## Architecture Docker production

- `app` : Laravel / PHP-FPM ;
- `nginx` : exposition via le réseau Traefik externe `web` ;
- `queue` : Laravel queue worker ;
- `scheduler` : `schedule:run` toutes les minutes ;
- `postgres` : base dédiée ;
- `redis` : cache et queues dédiés.

La structure de déploiement reprend le principe de `PERSPICASS/batix_Saas` : `docker-compose.yml` + `docker-compose.prod.yml`, branche `prod`, déploiement SSH sur le VPS.

## Installation VPS

Répertoire attendu :

```bash
/opt/batix/apps/prod/batix_agent
```

Première installation :

```bash
git clone git@github.com:PERSPICASS/batix_agent.git /opt/batix/apps/prod/batix_agent
cd /opt/batix/apps/prod/batix_agent
git checkout prod
cp .env.example .env
nano .env
```

Générer une clé Laravel :

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml build app
docker compose -f docker-compose.yml -f docker-compose.prod.yml run --rm app php artisan key:generate --show
```

Reporter la valeur obtenue dans `APP_KEY` du `.env`, puis :

```bash
./deploy.sh
```

## Variables GitHub Actions

Le workflow `.github/workflows/deploy.yml` attend :

- variable `VPS_HOST` ;
- variable `VPS_USER` ;
- secret `VPS_SSH_KEY`.

## Variables sensibles

Ne jamais commiter `.env`. Les secrets OpenAI, Meta, WhatsApp, base de données et Basic Auth restent uniquement sur le VPS / dans les secrets GitHub nécessaires.
