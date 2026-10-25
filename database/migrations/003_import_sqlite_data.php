<?php
declare(strict_types=1);

return [
    'version' => '003_import_sqlite_data',
    'up' => static function (PDO $mysql, string $sqlitePath): void {
        if (!is_file($sqlitePath)) { return; }
        $sqlite = new PDO('sqlite:' . $sqlitePath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $tables = ['regions','course_categories','plant_taxa','tag_groups','tags','users','user_profiles','user_tags','user_plants','courses','course_tags','course_plant_taxa','course_regions','course_modules','lessons','lesson_blocks','user_courses','user_lesson_progress','user_block_attempts','recommendation_rules','recommendation_rule_conditions','recommendation_rule_actions','user_course_recommendations'];
        foreach ($tables as $table) {
            $exists = $sqlite->prepare("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = ?");
            $exists->execute([$table]);
            if (!$exists->fetchColumn()) { continue; }
            $columns = array_column($sqlite->query("PRAGMA table_info(`$table`)")->fetchAll(PDO::FETCH_ASSOC), 'name');
            if ($columns === []) { continue; }
            $quoted = implode(', ', array_map(static fn(string $column): string => "`$column`", $columns));
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $insert = $mysql->prepare("INSERT IGNORE INTO `$table` ($quoted) VALUES ($placeholders)");
            $rows = $sqlite->query("SELECT $quoted FROM `$table`");
            while ($row = $rows->fetch(PDO::FETCH_NUM)) { $insert->execute($row); }
        }
    },
];
