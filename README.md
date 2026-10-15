# Tuboco — Tu botánica de confianza

Plataforma de aprendizaje de botánica con registro, inicio de sesión, cuestionario inicial y catálogo de cursos personalizado.

## Ejecutar localmente

Requiere PHP 8.2 o posterior con `pdo_sqlite` y `sqlite3`.

```powershell
php -S localhost:8000
```

Visita `http://localhost:8000`. La primera solicitud crea la base SQLite en `storage/botanica.sqlite`.

## Estructura

```text
api.php                 Punto de entrada de la API
server/
  bootstrap.php         Sesión, SQLite y esquema de datos
  http.php              Lectura y respuestas HTTP
  users.php             Registro, login y usuario autenticado
  profiles.php          Datos del cold start
assets/js/
  api.js                Cliente HTTP del frontend
  catalog.js            Catálogo y opciones del cuestionario
app.js                  Vistas e interacciones de la interfaz
app.css                 Estilos de Tuboco
storage/                Base de datos y sesiones locales, ignoradas por Git
```

## Datos guardados

- `users`: nombre, correo y contraseña protegida mediante `password_hash`.
- `onboarding_profiles`: jardín, macetas, intereses y objetivo del usuario.
