<?php
declare(strict_types=1);
require __DIR__ . '/../back/bootstrap.php';
require __DIR__ . '/seeders/RecommendationRulesSeeder.php';
$count = RecommendationRulesSeeder::run(database());
echo "Reglas sembradas correctamente: {$count}.\n";
