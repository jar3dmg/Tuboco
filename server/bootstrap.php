<?php
declare(strict_types=1);

const STORAGE_PATH = __DIR__ . '/../storage';

if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0775, true);
}

$sessionsPath = STORAGE_PATH . '/sessions';
if (!is_dir($sessionsPath)) {
    mkdir($sessionsPath, 0775, true);
}

session_save_path($sessionsPath);
session_start();

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
    runMigrations($connection, STORAGE_PATH . '/botanica.sqlite');

    return $connection;
}
