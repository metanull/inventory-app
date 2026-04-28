# Dev / CI image — PHP 8.4 + Node.js 24 (LTS/Krypton) + Xdebug
#
# Serves two purposes:
#   1. VS Code Dev Container  — open the workspace "in container", all tooling
#      (php, composer, node, npm, artisan, pest, pint, vite) runs inside.
#   2. Direct test/lint runner — same image used by CI-equivalent docker commands.
#
# Build:  docker build -f .devcontainer/Dockerfile -t inventory-app-dev .
# Run tests outside VS Code:
#   docker run --rm -v "${PWD}:/workspaces/inventory-app" inventory-app-dev \
#     php artisan test --testsuite=Api --no-coverage --no-ansi

FROM php:8.4-cli-bookworm

# ── System packages ───────────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        libsqlite3-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libexif-dev \
        unzip \
        git \
        curl \
        sudo \
        procps \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        zip \
        pdo_sqlite \
        gd \
        exif \
        bcmath \
        pcntl \
        sockets \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ── Xdebug ────────────────────────────────────────────────────────────────────
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY .devcontainer/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

# ── Node.js 24.x (LTS/Krypton) ───────────────────────────────────────────────
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ── Composer ──────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── PHP tuning ────────────────────────────────────────────────────────────────
RUN echo 'memory_limit = 512M' > /usr/local/etc/php/conf.d/zz-memory.ini

ENV COMPOSER_ALLOW_SUPERUSER=1

# ── Non-root user (VS Code Dev Container convention) ─────────────────────────
ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=${USER_UID}

RUN groupadd --gid ${USER_GID} ${USERNAME} \
    && useradd --uid ${USER_UID} --gid ${USER_GID} -m ${USERNAME} \
    && echo "${USERNAME} ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers.d/${USERNAME} \
    && chmod 0440 /etc/sudoers.d/${USERNAME} \
    && mkdir -p /workspaces/inventory-app/vendor \
                /workspaces/inventory-app/node_modules \
                /workspaces/inventory-app/spa/node_modules \
                /workspaces/inventory-app/scripts/importer/node_modules \
    && chown -R ${USERNAME}:${USERNAME} /workspaces

WORKDIR /workspaces/inventory-app

USER ${USERNAME}

# Keep the container alive when used as a Dev Container.
# Overridden at runtime when used as a one-shot test runner.
CMD ["sleep", "infinity"]
