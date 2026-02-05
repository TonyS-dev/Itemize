#!/bin/bash

# Load env vars
export $(grep -v '^#' .env.production | xargs)

# 1. Pull changes
git pull origin master

# 2. Build assets locally
npm install && npm run build

# 3. Start/Restart services
# Uses the new pure-app compose file. Traefik handles the rest.
docker-compose -f docker-compose.prod.yml up -d --build --remove-orphans

# 4. Run migrations
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

echo "Deployment finished! Check Traefik dashboard or your domain."
