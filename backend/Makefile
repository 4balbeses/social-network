.PHONY: up down php composer migrate logs

up:
	docker-compose up --build -d

down:
	docker-compose down

php:
	docker exec -it symfony_php bash

composer:
	docker exec -it symfony_php composer install

migrate:
	docker exec -it symfony_php symfony console doctrine:migrations:migrate

logs:
	docker-compose logs -f