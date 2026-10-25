# Tuboco — Tu botánica de confianza

Plataforma de aprendizaje de botánica con registro, inicio de sesión, cuestionario inicial y catálogo de cursos personalizado.

## Ejecutar localmente

Requiere PHP 8.2 o posterior con `pdo_mysql` y MariaDB/MySQL iniciado.

```powershell
C:\xampp-2\php\php.exe database\migrate.php
C:\xampp-2\php\php.exe -S localhost:8000
```

Visita `http://localhost:8000`. La primera solicitud también ejecuta las migraciones pendientes.

## Migraciones MySQL / MariaDB

Las migraciones se registran en `schema_migrations`, así que pueden ejecutarse varias veces sin duplicar tablas ni catálogos.

La base se llama `botanica_cursos` por defecto y aparecerá en phpMyAdmin. En la primera migración se importan los datos disponibles del archivo SQLite histórico, que se conserva como respaldo en `storage/`.

Para aplicar y comprobar las migraciones manualmente:

```powershell
C:\xampp-2\php\php.exe database\migrate.php
```

Para cambios futuros, agrega una migración con una versión nueva a `database/migrations/` y regístrala en `server/migrations.php`. No modifiques una migración que ya se haya aplicado en instalaciones compartidas.

La conexión puede configurarse mediante las variables de entorno `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`. Para el XAMPP local se usa `127.0.0.1`, usuario `root`, contraseña vacía y la base `botanica_cursos`.

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
