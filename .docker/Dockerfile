# syntax=docker/dockerfile:1
#
# One Dockerfile, four stages, a single PHP extension list.
#
#   base   PHP runtime + extensions. Never used directly.
#   dev    + composer, node, xdebug. No app code — it is bind-mounted.
#   build  + vendor and front-end assets, compiled from the repo.
#   prod   nginx + php-fpm under supervisord, code baked in.
#
# Build a specific stage with --target:
#   docker build --target dev  -f .docker/Dockerfile -t inventory-app:dev  .
#   docker build --target prod -f .docker/Dockerfile -t inventory-app:prod .
#
# Both work from a plain checkout. The prod image used to require a release
# tarball produced by CI, which meant nobody could reproduce it locally; the
# build stage now does that work itself.

# =============================================================================
# base — the PHP runtime every other stage inherits
# =============================================================================
FROM php:8.5-fpm-alpine AS base

ADD --chmod=0755 \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    /usr/local/bin/

# The single extension list. Previously duplicated across three Dockerfiles,
# which had already drifted — the devcontainer shipped sqlite but no mysql or
# redis, so it could not talk to the dev stack at all.
#
# pdo_sqlite is here for the test suite: phpunit.xml forces sqlite :memory:.
RUN install-php-extensions \
        pdo_mysql \
        pdo_sqlite \
        bcmath \
        intl \
        gd \
        zip \
        pcntl \
        sockets \
        opcache \
        exif \
        redis \
    && rm -rf /var/cache/apk/* /tmp/pear

WORKDIR /var/www/app

# =============================================================================
# dev — interactive development and one-shot artisan/composer jobs
# =============================================================================
FROM base AS dev

RUN apk add --no-cache git curl unzip nodejs npm \
    && install-php-extensions xdebug \
    && curl -sS https://getcomposer.org/installer \
        | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/cache/apk/* /tmp/pear

COPY .docker/php.dev.ini   /usr/local/etc/php/conf.d/99-app.ini
COPY .docker/fpm.dev.conf  /usr/local/etc/php-fpm.d/zzz-app.conf
# Ships disabled (see the file) — set XDEBUG_MODE=debug on the container to
# turn step debugging on without rebuilding.
COPY .docker/xdebug.ini    /usr/local/etc/php/conf.d/zz-xdebug.ini

ENV COMPOSER_ALLOW_SUPERUSER=1

CMD ["php-fpm"]

# =============================================================================
# build — compile vendor + assets, so prod needs no build tooling of its own
# =============================================================================
FROM dev AS build

COPY . /var/www/app

# composer's post-autoload-dump hooks boot Laravel (package:discover,
# filament:upgrade), which needs an .env to read. It is throwaway — deleted
# again below, and the real configuration is injected at run time. No APP_KEY
# is generated here: baking one into a shared image would be a bug, and
# nothing in this step needs to decrypt anything.
# .dockerignore strips the runtime cache directories (they are volumes in
# production and gigabytes locally), but Laravel refuses to boot without them —
# package:discover dies with "Please provide a valid cache path". Recreate the
# empty skeleton before anything tries to bootstrap the framework.
RUN mkdir -p bootstrap/cache \
             storage/app/public \
             storage/framework/views \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/logs \
    && cp .env.example .env \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && npm ci --no-fund --no-audit \
    && npm run build \
    && rm -rf node_modules .env \
    && composer clear-cache \
    && npm cache clean --force

# =============================================================================
# prod — what actually serves traffic
# =============================================================================
FROM base AS prod

RUN apk add --no-cache nginx supervisor \
    && mkdir -p /run/nginx \
    && rm -rf /var/cache/apk/*

COPY .docker/php.ini          /usr/local/etc/php/conf.d/99-app.ini
COPY .docker/fpm.conf         /usr/local/etc/php-fpm.d/zzz-app.conf
COPY .docker/nginx.conf       /etc/nginx/http.d/default.conf
COPY .docker/supervisord.conf /etc/supervisord.conf

COPY --from=build /var/www/app /var/www/app

RUN rm -rf public/storage \
    && ln -sfn /var/www/app/storage/app/public public/storage \
    && mkdir -p bootstrap/cache \
              storage/app/public \
              storage/framework/views \
              storage/framework/cache/data \
              storage/framework/sessions \
              storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
