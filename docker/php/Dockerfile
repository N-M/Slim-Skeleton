ARG PHP_VERSION=8.1.5

FROM composer:2.2.6 AS composer

FROM php:${PHP_VERSION}-fpm-alpine

WORKDIR /srv/
COPY . ./
COPY composer.json composer.lock /

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		chromium \
		file \
		gettext \
		zip \
		libzip-dev\
		git \
		autoconf \
		automake \
		libpng-dev \
		gawk \
		build-base \
		php8-pcntl \
		php8-gd \
        bind-tools \
	;

RUN apk del --no-cache ${PHPIZE_DEPS}

RUN apk add --update bash openssl ca-certificates

RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-dev \
		libzip-dev \
		libintl \
		zlib-dev \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
	; \
	\
	docker-php-ext-install -j$(nproc) \
		bcmath \
		mysqli \
		pdo_mysql \
		zip \
		pcntl \
		gd \
		exif \
        sockets \
	; \
	\
	pecl clear-cache; \
	docker-php-ext-enable \
		opcache \
		exif \
        sockets \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .api-phpexts-rundeps $runDeps;

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/php.ini
RUN echo "xdebug.client_host=$(/sbin/ip route|awk '/default/ { print $3 }')" >> /usr/local/etc/php/php.ini
#RUN rm /usr/local/etc/php/conf.d/docker-php-ext-pcntl.ini && echo "extension=/usr/lib/php8/modules/pcntl.so" >> /usr/local/etc/php/conf.d/docker-php-ext-pcntl.ini

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
	php -dmemory_limit=-1 /usr/bin/composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

# build for dev
ARG APP_ENV=dev
WORKDIR /srv
RUN ls -la && composer install --prefer-dist --no-autoloader --no-scripts --no-progress;

VOLUME /srv/var

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# fix xdebug log error permission
RUN touch /var/log/xdebug.log; \
    chmod 666 /var/log/xdebug.log

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
