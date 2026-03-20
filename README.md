# Hubox API & Admin Panel

Este es el backend y panel de administración (construido con Filament) para el proyecto Hubox.

## Requisitos

- PHP >= 8.2
- Composer
- Base de datos (MySQL, PostgreSQL, etc.)

## Instalación y Configuración Básica

1.  **Clonar el repositorio y preparar el entorno:**
    ```bash
    cp .env.example .env
    composer install
    ```

2.  **Configurar variables de entorno (`.env`):**
    Asegúrate de configurar correctamente:
    *   La conexión a tu base de datos (`DB_*`).
    *   La URL de tu frontend (`APP_FRONTEND_URL`).
    *   Credenciales de AWS S3 para el almacenamiento de archivos (`AWS_*`).
    *   API Key de Brevo para el envío de correos (`BREVO_API_KEY`).

3.  **Generar la clave de la aplicación y ejecutar migraciones:**
    ```bash
    php artisan key:generate
    php artisan migrate
    ```

4.  **Poblar la base de datos (Seeders):**
    Este paso es crucial, ya que creará los tipos de documentos necesarios y el usuario administrador por defecto.
    ```bash
    composer dump-autoload
    php artisan db:seed
    ```

## Acceso al Panel de Administración (Filament)

Una vez que el proyecto esté corriendo y hayas ejecutado las migraciones y seeders, puedes acceder al panel de administración añadiendo `/admin` a tu URL base (por ejemplo: `http://localhost:8000/admin`).

### Credenciales de Administrador por defecto:

Para iniciar sesión en el panel por primera vez, utiliza las siguientes credenciales (creadas por el `AdminUserSeeder`):

-   **Email:** `mauroivaning@gmail.com`
-   **Contraseña:** `password`

> **⚠️ IMPORTANTE PARA PRODUCCIÓN:** 
> Se recomienda encarecidamente iniciar sesión y cambiar esta contraseña inmediatamente en entornos productivos, o modificar el seeder si manejas otra estrategia de despliegue.
