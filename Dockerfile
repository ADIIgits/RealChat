FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpq-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install frontend dependencies
RUN npm install

# Build frontend assets
# Build frontend assets
RUN npm run build

EXPOSE 10000

CMD php artisan optimize:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000

# RUN npm run build

# # Laravel cache optimizations
# RUN php artisan config:clear || true
# RUN php artisan config:cache || true
# RUN php artisan route:cache || true
# RUN php artisan view:cache || true

# # Expose Render port
# EXPOSE 10000

# # Start Laravel
# CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000