# Hubox API & Admin Panel

Este es el backend y panel de administración para el proyecto Hubox.

## Stack Tecnológico y Dependencias Principales

Este proyecto está construido utilizando tecnologías y paquetes modernos del ecosistema de PHP y Laravel:

*   **Lenguaje:** PHP `^8.2`
*   **Framework:** Laravel `^12.0`
*   **Panel de Administración:** Filament `^3.0`
*   **Gestión de Archivos:** Spatie Laravel MediaLibrary `^11.21`
    *   *Plugin:* Filament Spatie Media Library Plugin `^3.2`
    *   *Almacenamiento en la Nube:* Flysystem AWS S3 v3 `^3.0`
*   **Autenticación API:** Tymon JWT Auth `^2.3` (JSON Web Tokens)
*   **Servicio de Correos:** Brevo (vía API HTTP)
*   **Protección (Anti-bot):** Google reCAPTCHA v3

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
    *   Claves de reCAPTCHA.

3.  **Generar claves y ejecutar migraciones:**
    ```bash
    php artisan key:generate
    php artisan jwt:secret  # Para generar la clave de JWT Auth
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
