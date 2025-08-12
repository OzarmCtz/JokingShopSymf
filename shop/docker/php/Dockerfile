FROM php:8.3-fpm

ARG PUID=1000
ARG PGID=1000

# Installation des dépendances système avec root
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev \
    libonig-dev libxml2-dev && \
    docker-php-ext-install pdo pdo_mysql && \
    rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installer Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony && \
    chmod +x /usr/local/bin/symfony

# Créer l'utilisateur non-root
RUN addgroup --gid ${PGID} appgroup \
    && adduser --disabled-password --gecos "" --uid ${PUID} --gid ${PGID} appuser

WORKDIR /var/www/html

# Changer la propriété du répertoire de travail
RUN chown -R appuser:appgroup /var/www/html

# Basculer sur l'utilisateur non-root
USER appuser
