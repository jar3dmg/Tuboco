<?php
declare(strict_types=1);

/** Construye la configuración usando variables de entorno y valores locales seguros. */
function databaseConfig(): array
{
    return [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_DATABASE') ?: 'botanica_cursos',
        'user' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
    ];
}
