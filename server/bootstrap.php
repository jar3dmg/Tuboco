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

    $connection = new PDO('sqlite:' . STORAGE_PATH . '/botanica.sqlite');
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->exec('PRAGMA foreign_keys = ON');
    $connection->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE, password TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
    $connection->exec('CREATE TABLE IF NOT EXISTS onboarding_profiles (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL UNIQUE, has_garden INTEGER NOT NULL DEFAULT 0, garden_plants TEXT, has_pots INTEGER NOT NULL DEFAULT 0, pot_plants TEXT, interests TEXT NOT NULL, goal TEXT NOT NULL, completed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)');

    return $connection;
}
