<?php
declare(strict_types=1);

return [
    'version' => '005_database_auth_sessions',
    'up' => static function (PDO $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS auth_sessions (
            token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            INDEX idx_auth_sessions_user (user_id),
            INDEX idx_auth_sessions_expiry (expires_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");
    },
];
