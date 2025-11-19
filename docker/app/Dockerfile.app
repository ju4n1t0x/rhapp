FROM php:8.3-fpm

# 1. Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libpng-dev libwebp-dev libjpeg-dev libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip intl opcache


# 3. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Configurar el directorio de trabajo
WORKDIR /var/www/html

# 5. Copiar archivos de composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# 6. Copiar el resto de los archivos de la aplicación
COPY . .

# 7. Ejecutar scripts de composer y permisos
RUN composer dump-autoload --optimize
RUN chown -R www-data:www-data storage bootstrap/cache