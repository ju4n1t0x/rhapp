FROM php:8.3-fpm

# 1. Instalar dependencias del sistema y Node.js (para Vite)
RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    build-essential \
    libpng-dev libwebp-dev libjpeg-dev libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip intl opcache


# 3. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Configurar el directorio de trabajo
WORKDIR /var/www/html

# 5. Copiar archivos de composer y npm
COPY composer.json composer.lock package*.json ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader \
    && npm install --no-progress --include=dev

# 6. Copiar el resto de los archivos de la aplicación
COPY . .

# 7. Copiar y dar permisos al entrypoint
COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# 8. Instalar dependencias npm y ajustar permisos
RUN npm install --no-progress --include=dev
RUN composer dump-autoload --optimize
RUN chown -R www-data:www-data storage bootstrap/cache

# 9. Configurar entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]