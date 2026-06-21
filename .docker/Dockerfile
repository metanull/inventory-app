FROM php:8.5-fpm-alpine

ADD --chmod=0755 \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    /usr/local/bin/

RUN install-php-extensions \
        pdo_mysql \
        bcmath \
        intl \
        gd \
        zip \
        pcntl \
        opcache \
        exif \
        redis \
    && apk add --no-cache nginx supervisor \
    && mkdir -p /run/nginx \
    && rm -rf /var/cache/apk/* /tmp/pear

# Config files
COPY .docker/php.ini         /usr/local/etc/php/conf.d/99-app.ini
COPY .docker/fpm.conf        /usr/local/etc/php-fpm.d/zzz-app.conf
COPY .docker/nginx.conf      /etc/nginx/http.d/default.conf
COPY .docker/supervisord.conf /etc/supervisord.conf

# Application code (pre-built by CI from release tarball)
WORKDIR /var/www/app
COPY . .

RUN rm -rf public/storage \
    && ln -sfn /var/www/app/storage/app/public public/storage \
    && mkdir -p bootstrap/cache \
              storage/framework/views \
              storage/framework/cache/data \
              storage/framework/sessions \
              storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
