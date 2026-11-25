# A small image that runs the Nkwen Products API on a free host such as Render.
# You do not need to change this file. Lesson B3 explains production servers properly.
FROM php:8.3-cli

# Tools Composer needs to download packages.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

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
