# A small image that runs the Nkwen API on a free host such as Render.
# B2 change: install the MySQL driver (pdo_mysql). The rest is the same as B1.
FROM php:8.3-cli

# Tools Composer needs to download packages.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

# B2: PHP's MySQL driver. Without it you get "could not find driver".
RUN docker-php-ext-install pdo_mysql

# Copy Composer from its official image.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install only what production needs (no PHPUnit, no Faker).
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_CLI_SERVER_WORKERS=4

EXPOSE 10000
CMD ["sh", "docker/start.sh"]
