# RHAPP

Aplicacion de gestion administrativa empresarial, control de empleados, vacaciones, fichajes y sistema de tickets


## Docker

- 1. Primero hacer el build de app con el comando
**docker compose build app**

- 2. luego realizar el build de migrations y cronjobs con el comando
**docker compose build migrations cron**

- 3. una vez hechos los builds, ejecutar
**docker compose up -d**
para montar las imagenes

- 4. una vez que los contenedores estan levantados, hay que realizar el seeder de la bd
**docker compose exec app php artisan db:seed**


