.PHONY: up shell migrations migrate reset-db lint test

VERSION = 0.1.0
COMMIT = $(shell git rev-parse --short HEAD)

up:
	@docker compose up -d

shell:
	@docker compose exec app bash

clear:
	@docker compose exec app php bin/console cache:clear
	@docker compose exec app php bin/console cache:clear --env=test

migrations:
	@docker compose exec app php bin/console doctrine:migrations:diff

migrate:
	@docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

reset-db:
	@docker compose exec redis redis-cli flushall
	@docker compose exec app php bin/console doctrine:schema:drop --force --full-database
	@docker compose exec app php bin/console doctrine:schema:drop --force --full-database --env=test
	@$(MAKE) migrate
	@docker compose exec app php bin/console foundry:load-fixtures development --append --no-interaction

lint:
	@docker compose exec app php -d memory_limit=-1 vendor/bin/php-cs-fixer fix --diff
	@docker compose exec app php -d memory_limit=-1 vendor/bin/phpstan analyse
	@docker compose exec frontend npm run format
	@docker compose exec frontend npm run lint:fix

test:
	@docker compose exec app php bin/console doctrine:migration:migrate --env=test --no-interaction
	@docker compose exec app php bin/phpunit

build-release:
	@rm -rf backend/config/jwt
	@docker build --no-cache --file ./docker/app/Dockerfile --build-arg MY_PROJECT_VERSION=$(VERSION) --build-arg MY_PROJECT_COMMIT=$(COMMIT) --target frankenphp_prod --tag ghcr.io/my_name/my_project:0.1.0 .
