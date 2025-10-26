#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
		composer install --prefer-dist --no-progress --no-interaction
	fi

	php bin/console -V

	if [ "$(find ./migrations -iname '*.php' -print -quit)" ]; then
		php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
	fi

	if [ ! "$( find ./config/jwt -iname '*.pem' -print -quit )" ]; then
		php bin/console lexik:jwt:generate-keypair
	fi

	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

	export MERCURE_SUBSCRIBER_JWT_KEY=$(cat ./config/jwt/public.pem)
fi

exec docker-php-entrypoint "$@"
