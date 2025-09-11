FROM php:8.3-fpm

ARG PUID=1000
ARG PGID=1000

# Installation des dépendances système avec root
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev \
    libonig-dev libxml2-dev && \
    docker-php-ext-install pdo pdo_mysql && \
    rm -rf /var/lib/apt/lists/*

# Install intl
RUN apt-get update && apt-get install -y \
    libicu-dev \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installer Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony && \
    chmod +x /usr/local/bin/symfony

# Copier la configuration PHP personnalisée pour les uploads
COPY php-uploads.ini /usr/local/etc/php/conf.d/php-uploads.ini

# Créer l'utilisateur non-root
RUN addgroup --gid ${PGID} appgroup \
    && adduser --disabled-password --gecos "" --uid ${PUID} --gid ${PGID} appuser

# Donner les permissions nécessaires à appuser pour php-fpm
RUN chown -R appuser:appgroup /usr/local/etc/php-fpm.d/ \
    && chown -R appuser:appgroup /var/log/ \
    && mkdir -p /var/run/php && chown -R appuser:appgroup /var/run/php

# Configurer php-fpm pour l'utilisateur non-root
RUN echo '[global]' > /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'daemonize = no' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo '[www]' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'listen = 9000' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'listen.owner = appuser' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'listen.group = appgroup' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'listen.mode = 0660' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'user = appuser' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'group = appgroup' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'pm = dynamic' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'pm.max_children = 20' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'pm.start_servers = 2' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'pm.min_spare_servers = 1' >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo 'pm.max_spare_servers = 3' >> /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

# S'assurer que le répertoire de travail existe et appartient à appuser
RUN mkdir -p /var/www/html && chown -R appuser:appgroup /var/www/html

# Créer un script d'entrée qui gère les permissions au démarrage
RUN echo '#!/bin/bash' > /usr/local/bin/entrypoint.sh \
    && echo '# Fix permissions for mounted volume' >> /usr/local/bin/entrypoint.sh \
    && echo 'if [ -d "/var/www/html" ]; then' >> /usr/local/bin/entrypoint.sh \
    && echo '  sudo chown -R appuser:appgroup /var/www/html' >> /usr/local/bin/entrypoint.sh \
    && echo 'fi' >> /usr/local/bin/entrypoint.sh \
    && echo '' >> /usr/local/bin/entrypoint.sh \
    && echo '# Install dependencies if needed' >> /usr/local/bin/entrypoint.sh \
    && echo 'if [ ! -f vendor/autoload_runtime.php ]; then' >> /usr/local/bin/entrypoint.sh \
    && echo '  composer install --no-interaction --prefer-dist' >> /usr/local/bin/entrypoint.sh \
    && echo 'fi' >> /usr/local/bin/entrypoint.sh \
    && echo '' >> /usr/local/bin/entrypoint.sh \
    && echo 'exec "$@"' >> /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

# Install sudo for permission fixes
RUN apt-get update && apt-get install -y sudo \
    && echo 'appuser ALL=(ALL) NOPASSWD: /bin/chown' >> /etc/sudoers.d/appuser \
    && rm -rf /var/lib/apt/lists/*

# Basculer sur l'utilisateur non-root
USER appuser

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]
