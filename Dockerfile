FROM php:8.4-cli

# Install system deps
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy app
COPY . .

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader

# Install frontend deps
RUN npm install

# Build frontend
RUN npm run build

# Laravel optimizations
RUN php artisan config:cache || true
RUN php artisan route:cache || true

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
