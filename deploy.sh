#!/bin/bash
export $(grep -v '^#' .env.production | xargs)

git pull origin main

docker compose -f docker-compose.prod.yml up -d --build --remove-orphans

docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

echo "Deployment finished!"
