<?php
declare(strict_types=1);

require __DIR__ . '/../back/bootstrap.php';
require __DIR__ . '/seeders/ExtendedCatalogSeeder.php';

$result = ExtendedCatalogSeeder::run(database());
echo "Catálogos ampliados correctamente.\n";
echo "Categorías: {$result['categories']}; regiones: {$result['regions']}; etiquetas: {$result['tags']}; taxones: {$result['plant_taxa']}.\n";
