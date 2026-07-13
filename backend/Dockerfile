FROM composer:2 AS composer

FROM php:8.2-cli-bookworm

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        pdo_pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY docker/start-backend.sh /usr/local/bin/start-backend

RUN chmod +x /usr/local/bin/start-backend

WORKDIR /var/www/html

EXPOSE 8000

CMD ["/usr/local/bin/start-backend"]
