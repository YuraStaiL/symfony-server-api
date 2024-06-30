# symfony-client
Deploy:
1. docker network create symfony-network
2. docker compose -f ./docker/docker-compose.yml build
3. docker compose -f ./docker/docker-compose.yml up 
4. docker compose -f ./docker/docker-compose.yml exec -u www-data php-fpm bash
5. run in a container php bin/console doctrine:migrations:migrate