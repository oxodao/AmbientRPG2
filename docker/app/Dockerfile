FROM dunglas/frankenphp:1-php8.4 AS frankenphp_upstream
FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV MERCURE_TRANSPORT_URL=bolt:///data/mercure.db
ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

# persistent / runtime deps
# hadolint ignore=DL3008
RUN apt-get update && apt-get install -y --no-install-recommends \
	file \
	git \
    acl \
	&& rm -rf /var/lib/apt/lists/*

RUN set -eux; \
	install-php-extensions \
		@composer \
		apcu \
		intl \
		opcache \
		zip \
		pdo_pgsql \
		redis \
        xsl \
	;

###> recipes ###
###< recipes ###

COPY --link ./docker/app/frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 ./docker/app/frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile" ]

FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN set -eux; \
	install-php-extensions \
		xdebug \
	;

COPY --link --chmod=755 ./docker/app/frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link ./docker/app/frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--watch" ]

FROM node:25 AS frontend
COPY --link ./frontend /app
RUN cd /app && npm install && npm run build

FROM frankenphp_base AS frankenphp_prod
ENV APP_ENV=prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link --chmod=755 ./docker/app/frankenphp/start.sh /usr/local/bin/docker-entrypoint
COPY --link ./docker/app/frankenphp/Caddyfile.prod /etc/frankenphp/Caddyfile
COPY --link ./docker/app/frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

COPY --link ../../backend/composer.* ../../backend/symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --link ../../backend ./

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync;

# Setting the version infos
RUN sed -i \
    -e "s/APP_VERSION: 'indev'/APP_VERSION: '${AMBIENTRPG_VERSION}'/" \
    -e "s/APP_COMMIT: 'INDEV'/APP_COMMIT: '${AMBIENTRPG_COMMIT}'/" \
    /app/config/packages/version_release.yaml

COPY --from=frontend /app/dist/ /frontend