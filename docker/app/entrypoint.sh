#!/bin/sh
set -e

# Si .env no existe, copiarlo desde .env.example
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Si APP_KEY está vacío o no tiene base64:, generarlo
if ! grep -qE "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "Generating APP_KEY..."
    # Generar key y reemplazar en .env
    KEY=$(php artisan key:generate --show --no-interaction)
    sed -i "s|^APP_KEY=.*|APP_KEY=$KEY|g" .env
    # Exportar al entorno del proceso actual
    export APP_KEY="$KEY"
    echo "APP_KEY generated and exported: $KEY"
fi

# Ejecutar el comando original (php-fpm)
exec "$@"
