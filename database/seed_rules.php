<?php
declare(strict_types=1);
require __DIR__ . '/../server/bootstrap.php';
require __DIR__ . '/seeders/RecommendationRulesSeeder.php';
$count = RecommendationRulesSeeder::run(database());
echo "Reglas sembradas correctamente: {$count}.\n";
