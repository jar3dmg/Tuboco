<?php
declare(strict_types=1);

/** Abre una única conexión PDO y aplica las migraciones pendientes. */
function database(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    require_once __DIR__ . '/database_config.php';
    $config = databaseConfig();
    $server = new PDO("mysql:host={$config['host']};port={$config['port']};charset=utf8mb4", $config['user'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$config['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $connection = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4", $config['user'], $config['password']);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    require_once __DIR__ . '/migrations.php';
    runMigrations($connection);

    return $connection;
}
