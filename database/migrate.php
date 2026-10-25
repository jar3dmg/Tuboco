<?php
declare(strict_types=1);

require __DIR__ . '/../server/bootstrap.php';

$db = database();
$tables = (int) $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
$tagGroups = (int) $db->query('SELECT COUNT(*) FROM tag_groups')->fetchColumn();
$tags = (int) $db->query('SELECT COUNT(*) FROM tags')->fetchColumn();
echo "Migraciones aplicadas correctamente.\n";
echo "Tablas: {$tables}; grupos de etiquetas: {$tagGroups}; etiquetas: {$tags}.\n";
