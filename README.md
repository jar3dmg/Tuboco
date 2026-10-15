# Raíz — plataforma de botánica

Aplicación enfocada en registro, inicio de sesión, cuestionario inicial y un inicio personalizado.

## Ejecutar localmente

Requiere PHP 8.2 con `pdo_sqlite` y `sqlite3`.

```powershell
php -S localhost:8000
```

Abre `http://localhost:8000`. En el primer uso se crea `storage/botanica.sqlite` con las tablas `users` y `onboarding_profiles`.
