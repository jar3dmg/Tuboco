<?php
declare(strict_types=1);

return [
    'version' => '002_user_interface_preferences',
    'up' => static function (PDO $db): void {
        $columns = $db->query("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'user_profiles'")->fetchAll(PDO::FETCH_COLUMN);
        $add = [
            'theme' => "ALTER TABLE user_profiles ADD COLUMN theme VARCHAR(20) NOT NULL DEFAULT 'light'",
            'font_size' => "ALTER TABLE user_profiles ADD COLUMN font_size VARCHAR(20) NOT NULL DEFAULT 'normal'",
            'reading_mode' => "ALTER TABLE user_profiles ADD COLUMN reading_mode TINYINT(1) NOT NULL DEFAULT 0",
            'font_mode' => "ALTER TABLE user_profiles ADD COLUMN font_mode VARCHAR(20) NOT NULL DEFAULT 'standard'",
        ];
        foreach ($add as $column => $sql) {
            if (!in_array($column, $columns, true)) { $db->exec($sql); }
        }
    },
];
