Sasia Juan Ignacio - Evi 2025

# RHAPP

Aplicación de gestión administrativa empresarial, control de empleados, vacaciones, fichajes y sistema de tickets.

---

## 🚀 Setup Rápido con Docker

### Requisitos previos
- Docker y Docker Compose instalados
- Git

### Pasos para levantar el proyecto


1. **Construir las imágenes**
   ```bash
   docker compose build
   ```

2. **Levantar los contenedores**
   ```bash
   docker compose up -d
   ```

3. **Acceder a la aplicación**
   
   La aplicación estará disponible en:
   ```
   http://127.0.0.1:8081/dashboard
   ```

**¡Eso es todo!** El sistema se encargará automáticamente de:
- ✅ Instalar dependencias de Composer (vendor)
- ✅ Instalar dependencias de NPM (node_modules)
- ✅ Generar la clave de aplicación (APP_KEY)
- ✅ Ejecutar migraciones de base de datos
- ✅ Cargar datos iniciales (seeders)

---

## 🛠️ Comandos útiles durante el desarrollo

### Ejecutar comandos Artisan
```bash
docker compose exec app php artisan [comando]
```

Ejemplos:
```bash
# Crear una migración
docker compose exec app php artisan make:migration create_example_table

# Ejecutar migraciones
docker compose exec app php artisan migrate

# Crear un controlador
docker compose exec app php artisan make:controller ExampleController
```



### Acceder a la base de datos

La base de datos MySQL está expuesta en el puerto `3308`:

bash
# Desde el host
mysql -h 127.0.0.1 -P 3308 -u laravel -p
# Password: 1234


O directamente desde el contenedor:
bash
docker compose exec db mysql -u laravel -p laravel



---

## 📦 Arquitectura de contenedores

El proyecto utiliza Docker Compose con 5 servicios:

| Servicio | Descripción | Puerto |
|----------|-------------|--------|
| **app** | PHP-FPM 8.3 con Laravel | - |
| **nginx** | Servidor web Nginx | 8081 |
| **db** | MySQL 8.0 | 3308 |
| **migrations** | Ejecuta migraciones y seeders al inicio | - |
| **cron** | Ejecuta tareas programadas de Laravel | - |

---

## 🔧 Decisiones técnicas de dockerización

### 1. **Entrypoint automático para APP_KEY**
**Problema:** Laravel requiere una `APP_KEY` válida para funcionar. En un setup tradicional, el desarrollador debe ejecutar manualmente `php artisan key:generate`.

**Solución:** Se implementó un script `entrypoint.sh` en el contenedor `app` que:
- Verifica si existe `.env`, si no, lo crea desde `.env.example`
- Detecta si `APP_KEY` está vacía o tiene un placeholder
- Genera automáticamente una clave válida y la exporta al entorno del proceso

**Beneficio:** El desarrollador no necesita configurar manualmente la clave, todo funciona "out of the box".

---

### 2. **Volumen nombrado para node_modules**
**Problema:** Al usar bind mount (`./:/var/www/html`), el directorio `node_modules` del host puede sobrescribir el del contenedor, causando conflictos de arquitectura (especialmente en Mac M1/M2).

**Solución:** Se declaró un volumen nombrado específico para `node_modules`:
```yaml
volumes:
  - node_modules:/var/www/html/node_modules
```

**Beneficio:** 
- Las dependencias npm se instalan dentro del contenedor con binarios nativos correctos
- Evita problemas con paquetes nativos (como `@rollup/rollup-darwin-arm64`)
- El desarrollador puede trabajar desde el host sin conflictos

---

### 3. **Instalación de dependencias durante el build**
**Decisión:** Tanto `composer install` como `npm install` se ejecutan durante `docker compose build`, no al iniciar el contenedor.

**Justificación:**
- Las dependencias quedan "bakeadas" en la imagen
- Arranque más rápido de contenedores
- Aprovecha el sistema de caché de Docker (capas)
- Reduce tiempo de espera en cada `docker compose up`

**Implementación:**
```dockerfile
RUN composer install --no-dev --optimize-autoloader
RUN npm install --no-progress --include=dev
```

---

### 4. **Contenedor dedicado para migraciones**
**Problema:** Ejecutar migraciones manualmente es propenso a errores (el desarrollador puede olvidarlo).

**Solución:** Se creó un contenedor `migrations` que:
- Espera a que la base de datos esté lista (`depends_on` con healthcheck)
- Ejecuta automáticamente `php artisan migrate --force`
- Carga datos iniciales con `php artisan db:seed --force`
- Se mantiene vivo para debugging con `tail -f /dev/null`

**Beneficio:** Setup completamente automatizado, sin intervención manual.

---

### 5. **Separación de .env y env_file**
**Problema inicial:** Se usaba `env_file: .env.docker` en docker-compose, lo que causaba conflictos con el `.env` del filesystem.

**Solución final:** 
- Se eliminó `env_file` del servicio `app`
- Laravel lee `.env` directamente del filesystem (bind mount)
- El entrypoint genera la clave en `.env` y la exporta al proceso

**Beneficio:** 
- Configuración más clara y predecible
- El `.env` generado automáticamente funciona tanto en contenedor como en host
- Menos archivos de configuración que mantener

---

### 6. **Configuración de base de datos en .env.example**
**Decisión:** El archivo `.env.example` ya viene con la configuración correcta de MySQL para Docker:
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=1234
```

**Justificación:**
- El desarrollador no necesita editar configuraciones
- Al copiar `.env.example` a `.env`, todo funciona inmediatamente
- Reduce errores de configuración comunes

---

### 7. **Healthchecks para dependencias**
**Implementación:** Se agregaron healthchecks al servicio `db`:
```yaml
healthcheck:
  test: ["CMD", "mysqladmin" ,"ping", "-h", "localhost"]
  interval: 10s
  timeout: 5s
  retries: 5
```

**Beneficio:**
- Los contenedores dependientes (`app`, `migrations`, `cron`) esperan a que MySQL esté realmente lista
- Evita errores de "Connection refused" al inicio
- Inicio confiable y predecible

---

---

## 📝 Notas adicionales

- **Puerto 8081:** La aplicación usa el puerto 8081 para evitar conflictos con otros servicios en el puerto 80.
- **Node modules:** Se mantienen en un volumen Docker separado para evitar conflictos de arquitectura.
- **Desarrollo local:** Puedes editar archivos directamente en tu editor y los cambios se reflejarán en tiempo real gracias al bind mount.
- **Vite (opcional):** Si necesitas hot-reload de assets frontend, ejecuta `npm install && npm run dev` desde el host.


