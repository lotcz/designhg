del .\src\composer.lock
docker-compose run composer install --no-dev --optimize-autoloader --classmap-authoritative --ignore-platform-reqs
