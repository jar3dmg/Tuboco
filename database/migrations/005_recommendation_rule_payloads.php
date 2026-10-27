<?php
declare(strict_types=1);

return [
    'version' => '005_recommendation_rule_payloads',
    'up' => static function (PDO $db, string $sqlitePath): void {
        $columns = $db->query("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'recommendation_rules'")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('code', $columns, true)) {
            $db->exec('ALTER TABLE recommendation_rules ADD COLUMN code VARCHAR(120) NULL AFTER id');
            $db->exec('CREATE UNIQUE INDEX uq_recommendation_rules_code ON recommendation_rules(code)');
        }
        $actionColumns = $db->query("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'recommendation_rule_actions'")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('action_data', $actionColumns, true)) {
            $db->exec('ALTER TABLE recommendation_rule_actions ADD COLUMN action_data JSON NULL AFTER message');
        }
    },
];
