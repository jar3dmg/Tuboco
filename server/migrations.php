<?php
declare(strict_types=1);

/** @return array<int, array{version: string, up: callable(PDO, string): void}> */
function migrations(): array
{
    return [
        require __DIR__ . '/../database/migrations/002_mysql_schema.php',
        require __DIR__ . '/../database/migrations/003_import_sqlite_data.php',
        require __DIR__ . '/../database/migrations/004_user_interface_preferences.php',
    ];
}

function runMigrations(PDO $db, string $sqlitePath): void
{
    $db->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(100) PRIMARY KEY, applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
    $applied = $db->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);

    foreach (migrations() as $migration) {
        if (in_array($migration['version'], $applied, true)) {
            continue;
        }

        ($migration['up'])($db, $sqlitePath);
        $statement = $db->prepare('INSERT INTO schema_migrations (version) VALUES (?)');
        $statement->execute([$migration['version']]);
    }
}
