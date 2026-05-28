FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]