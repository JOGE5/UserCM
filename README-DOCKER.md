# Campus Market - Docker Development Environment

Este entorno de Docker ha sido preparado para levantar el proyecto `CampusMarket` en cualquier equipo de manera rápida y sencilla, sin necesidad de instalar PHP, Composer, Node o MySQL localmente.

## Requisitos Previos

- **Docker Desktop** (con WSL2 habilitado en Windows).
- Los puertos `8000`, `3307` y `5173` **deben estar libres** (Cerrar Laragon, XAMPP u otros servidores que ocupen estos puertos).

## Pasos para levantar el proyecto

1. **Clonar el repositorio:**
   ```bash
   git clone <URL_DEL_REPOSITORIO>
   cd UserCM
   ```

2. **Configurar variables de entorno:**
   Copia el archivo base para Docker:
   ```bash
   cp .env.docker .env
   ```
   *(Nota: Puedes dejar el `.env.docker` tal cual y Docker se encargará del resto, pero para Artisan y tu editor es útil tener el `.env`)*

3. **Construir y levantar los contenedores:**
   ```bash
   docker compose up -d --build
   ```

4. **Acceder a la aplicación:**
   - App Laravel: [http://localhost:8000](http://localhost:8000)
   - Servidor Vite (Hot Reload): [http://localhost:5173](http://localhost:5173)

---

## Usuarios de Prueba (Seeders)

La base de datos se puebla automáticamente con los siguientes usuarios.

**SuperAdministrador:**
- **Email:** `agustinapaza1817@gmail.com`
- **Contraseña:** `ahmed1800Aa@`

**Estudiante de Prueba:**
- **Email:** `estudiante@campus.local`
- **Contraseña:** `password`

---

## Comandos Útiles

**Entrar al contenedor principal (Laravel/PHP):**
```bash
docker compose exec app bash
```

**Ejecutar comandos Artisan (Ej. migrar):**
```bash
docker compose exec app php artisan migrate
```

**Ver logs de la aplicación:**
```bash
docker compose logs -f app
```

**Detener el proyecto:**
```bash
docker compose down
```

**Reiniciar desde cero (Borrando base de datos y contenedores):**
```bash
docker compose down -v
docker compose up -d --build
```

---

## Solución de Problemas (Troubleshooting)

- **Conflictos de puertos:** Si obtienes un error como `bind: address already in use`, significa que algún servicio local (Laragon, MySQL local) está usando el puerto `3307` u `8000`. Asegúrate de detenerlos.
- **Permisos en Windows:** El script `entrypoint.sh` intenta corregir los permisos de las carpetas `storage` y `bootstrap/cache`. Si ves errores de permisos en los logs, puedes ejecutar manualmente:
  ```bash
  docker compose exec app chmod -R 777 storage bootstrap/cache
  ```
- **Vite no recarga automáticamente:** Vite utiliza el polling de Windows para detectar cambios (`CHOKIDAR_USEPOLLING=true`). Debería funcionar por defecto, pero si los cambios en `resources/js` no se reflejan, revisa los logs de Node con `docker compose logs -f node`.
