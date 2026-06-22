FROM php:8.5-fpm-alpine

ADD --chmod=0755 \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    /usr/local/bin/

RUN install-php-extensions \
        pdo_sqlite \
        intl \
        zip \
        gd \
        exif \
        bcmath \
        pcntl \
        sockets \
        xdebug \
    && apk add --no-cache git curl unzip nodejs npm sudo \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY .devcontainer/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

RUN echo 'memory_limit = 512M' > /usr/local/etc/php/conf.d/zz-memory.ini

ENV COMPOSER_ALLOW_SUPERUSER=1

ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=${USER_UID}

RUN addgroup -g ${USER_GID} ${USERNAME} \
    && adduser -u ${USER_UID} -G ${USERNAME} -s /bin/sh -D ${USERNAME} \
    && mkdir -p /etc/sudoers.d \
    && echo "${USERNAME} ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/${USERNAME} \
    && chmod 0440 /etc/sudoers.d/${USERNAME} \
    && mkdir -p /workspaces/inventory-app/vendor \
                /workspaces/inventory-app/node_modules \
                /workspaces/inventory-app/spa/node_modules \
                /workspaces/inventory-app/scripts/importer/node_modules \
    && chown -R ${USERNAME}:${USERNAME} /workspaces

WORKDIR /workspaces/inventory-app

USER ${USERNAME}

CMD ["sleep", "infinity"]
