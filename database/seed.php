<?php
declare(strict_types=1);

require __DIR__ . '/../server/bootstrap.php';
require __DIR__ . '/seeders/CatalogSeeder.php';
require __DIR__ . '/seeders/ExtendedCatalogSeeder.php';
require __DIR__ . '/seeders/RecommendationRulesSeeder.php';

$db = database();
CatalogSeeder::run($db);
$result = ExtendedCatalogSeeder::run($db);
$rules = RecommendationRulesSeeder::run($db);
echo "Seeders ejecutados correctamente.\n";
echo "Categorías: {$result['categories']}; regiones: {$result['regions']}; etiquetas: {$result['tags']}; taxones: {$result['plant_taxa']}; reglas: {$rules}.\n";
