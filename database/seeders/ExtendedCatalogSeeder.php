<?php
declare(strict_types=1);

/** Amplía los catálogos de demostración sin crear cursos ni perfiles. */
final class ExtendedCatalogSeeder
{
    public static function run(PDO $db): array
    {
        self::seedCategories($db);
        self::seedRegions($db);
        self::seedTags($db);
        self::seedPlantTaxa($db);

        return [
            'categories' => (int) $db->query('SELECT COUNT(*) FROM course_categories')->fetchColumn(),
            'regions' => (int) $db->query('SELECT COUNT(*) FROM regions')->fetchColumn(),
            'tags' => (int) $db->query('SELECT COUNT(*) FROM tags')->fetchColumn(),
            'plant_taxa' => (int) $db->query('SELECT COUNT(*) FROM plant_taxa')->fetchColumn(),
        ];
    }

    private static function seedCategories(PDO $db): void
    {
        $rows = [
            ['aquatic-plants', 'Plantas acuáticas', 'Cultivo y cuidado de especies acuáticas y de estanque.'],
            ['native-biodiversity', 'Flora nativa y biodiversidad', 'Especies nativas, polinizadores y conservación local.'],
            ['flowering-plants', 'Plantas de flor', 'Floración, selección y cuidados ornamentales.'],
            ['trees-shrubs', 'Árboles y arbustos', 'Establecimiento, poda y mantenimiento de especies leñosas.'],
            ['bonsai', 'Bonsái', 'Formación y cuidado de árboles en contenedor.'],
            ['hydroponics', 'Hidroponía', 'Cultivo sin suelo y manejo de soluciones nutritivas.'],
            ['seed-saving', 'Semillas y semilleros', 'Recolección, conservación y germinación de semillas.'],
            ['seasonal-gardening', 'Jardinería estacional', 'Tareas y selección de especies según la temporada.'],
            ['soil-ecology', 'Ecología del suelo', 'Vida del suelo, materia orgánica y salud radicular.'],
            ['sustainable-gardening', 'Jardinería sostenible', 'Ahorro de agua, manejo responsable y biodiversidad.'],
            ['plant-therapy', 'Bienestar con plantas', 'Relación responsable entre plantas, hogar y bienestar.'],
        ];
        $query = $db->prepare('INSERT INTO course_categories (parent_id, code, name, description, is_active) VALUES (NULL, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), is_active=VALUES(is_active)');
        foreach ($rows as $row) { $query->execute($row); }
    }

    private static function seedRegions(PDO $db): void
    {
        $rows = [
            ['tropical-rainy', 'Tropical lluviosa', 'Temperatura cálida y lluvias abundantes durante gran parte del año.'],
            ['coastal-warm', 'Costera cálida', 'Calor, humedad, viento y posible salinidad ambiental.'],
            ['highland-urban', 'Altiplano urbano', 'Días templados, noches frescas y microclimas de ciudad.'],
            ['northern-desert', 'Desértica del norte', 'Radiación intensa, gran amplitud térmica y poca lluvia.'],
            ['mediterranean-like', 'Templada mediterránea', 'Veranos secos e inviernos moderados con lluvias estacionales.'],
            ['cloud-forest', 'Bosque mesófilo', 'Humedad alta, neblina y temperaturas moderadas.'],
            ['volcanic-highland', 'Alta montaña volcánica', 'Frío, radiación elevada y riesgo de heladas.'],
        ];
        $query = $db->prepare('INSERT INTO regions (code, name, description, is_active) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), is_active=VALUES(is_active)');
        foreach ($rows as $row) { $query->execute($row); }
    }

    private static function seedTags(PDO $db): void
    {
        $groupIds = $db->query('SELECT code, id FROM tag_groups')->fetchAll(PDO::FETCH_KEY_PAIR);
        $rows = [
            ['interest','interest-aquatic','Plantas acuáticas'], ['interest','interest-native','Flora nativa'], ['interest','interest-flowering','Plantas de flor'], ['interest','interest-trees-shrubs','Árboles y arbustos'], ['interest','interest-bonsai','Bonsái'], ['interest','interest-hydroponics','Hidroponía'], ['interest','interest-seeds','Semillas y semilleros'], ['interest','interest-sustainable','Jardinería sostenible'], ['interest','interest-aromatics','Plantas aromáticas'], ['interest','interest-foliage','Plantas de follaje'], ['interest','interest-pollinators','Plantas para polinizadores'],
            ['goal','goal-save-water','Ahorrar agua'], ['goal','goal-improve-soil','Mejorar el suelo'], ['goal','goal-grow-flowers','Lograr más floración'], ['goal','goal-start-hydroponics','Iniciar hidroponía'], ['goal','goal-care-trees','Cuidar árboles y arbustos'], ['goal','goal-attract-pollinators','Atraer polinizadores'], ['goal','goal-grow-aromatics','Cultivar aromáticas'], ['goal','goal-identify-plants','Identificar mis plantas'],
            ['context','context-window','Ventana'], ['context','context-rooftop','Azotea'], ['context','context-patio','Patio'], ['context','context-community-garden','Huerto comunitario'], ['context','context-greenhouse','Invernadero'], ['context','context-office','Oficina'], ['context','context-water-garden','Estanque o jardín acuático'],
            ['light','light-morning-sun','Sol directo de mañana'], ['light','light-filtered','Luz filtrada'], ['light','light-full-sun','Sol pleno'], ['light','light-artificial','Luz artificial de cultivo'],
            ['environment','environment-windy','Ambiente ventoso'], ['environment','environment-saline','Ambiente salino'], ['environment','environment-frost-risk','Riesgo de heladas'], ['environment','environment-heat-wave','Olas de calor'], ['environment','environment-rainy','Lluvias frecuentes'], ['environment','environment-poor-drainage','Drenaje deficiente'],
            ['ecosystem','ecosystem-aquatic','Acuático'], ['ecosystem','ecosystem-coastal','Costero'], ['ecosystem','ecosystem-mediterranean','Mediterráneo'],
            ['topic','topic-plant-identification','Identificación de plantas'], ['topic','topic-root-health','Salud radicular'], ['topic','topic-seed-starting','Semilleros'], ['topic','topic-seed-saving','Conservación de semillas'], ['topic','topic-grafting','Injerto'], ['topic','topic-layering','Acodo'], ['topic','topic-hydroponics','Hidroponía'], ['topic','topic-aquatic-care','Cuidado acuático'], ['topic','topic-pollinators','Polinizadores'], ['topic','topic-native-plants','Plantas nativas'], ['topic','topic-water-saving','Ahorro de agua'], ['topic','topic-mulching','Acolchado'], ['topic','topic-bonsai-training','Formación de bonsái'], ['topic','topic-flowering','Floración'], ['topic','topic-plant-cleaning','Limpieza de follaje'], ['topic','topic-container-gardening','Cultivo en contenedor'], ['topic','topic-vertical-gardening','Jardinería vertical'], ['topic','topic-urban-gardening','Jardinería urbana'], ['topic','topic-soil-life','Vida del suelo'], ['topic','topic-companion-planting','Asociación de cultivos'], ['topic','topic-seasonal-care','Cuidados estacionales'],
            ['content','content-audio','Contenido en audio'], ['content','content-downloadable','Material descargable'], ['content','content-checklist','Lista de verificación'], ['content','content-quiz','Cuestionario'], ['content','content-step-by-step','Guía paso a paso'],
            ['safety','safety-non-toxic','Especie no tóxica'], ['safety','safety-avoid-ingestion','Evitar ingestión'], ['safety','safety-thorns','Planta con espinas'], ['safety','safety-irritant-sap','Savia irritante'], ['safety','safety-allergy-aware','Precaución por alergias'], ['safety','safety-child-safe-placement','Ubicación segura para niños'],
        ];
        if (count($rows) !== 71) { throw new LogicException('La expansión debe contener 71 etiquetas.'); }
        $query = $db->prepare('INSERT INTO tags (tag_group_id, code, name, description, sort_order, is_active) VALUES (?, ?, ?, NULL, ?, 1) ON DUPLICATE KEY UPDATE tag_group_id=VALUES(tag_group_id), name=VALUES(name), sort_order=VALUES(sort_order), is_active=VALUES(is_active)');
        foreach ($rows as $position => [$group, $code, $name]) {
            if (!isset($groupIds[$group])) { throw new LogicException("No existe el grupo de etiquetas {$group}."); }
            $query->execute([$groupIds[$group], $code, $name, 100 + $position]);
        }
    }

    private static function seedPlantTaxa(PDO $db): void
    {
        $families = [['family-moraceae','Moraceae','Moráceas'],['family-marantiaceae','Marantaceae','Marantáceas'],['family-bromeliaceae','Bromeliaceae','Bromelias'],['family-rutaceae','Rutaceae','Cítricos'],['family-rosaceae','Rosaceae','Rosáceas'],['family-apiaceae','Apiaceae','Apiáceas'],['family-asphodelaceae','Asphodelaceae','Áloes y haworthias'],['family-euphorbiaceae','Euphorbiaceae','Euforbias']];
        $familyQuery = $db->prepare("INSERT INTO plant_taxa (parent_id,taxon_type,code,scientific_name,common_name,ecosystem,toxicity_level,pet_safety_warning,is_active) VALUES (NULL,'family',?,?,?,NULL,NULL,NULL,1) ON DUPLICATE KEY UPDATE scientific_name=VALUES(scientific_name),common_name=VALUES(common_name),is_active=VALUES(is_active)");
        foreach ($families as [$code,$scientific,$common]) { $familyQuery->execute([$code,$scientific,$common]); }
        $familyIds = $db->query("SELECT code,id FROM plant_taxa WHERE taxon_type='family'")->fetchAll(PDO::FETCH_KEY_PAIR);
        $species = [
            ['family-moraceae','species-ficus-elastica','Ficus elastica','Ficus elástica','indoor','toxic','Puede causar irritación digestiva en mascotas si se ingiere.'], ['family-moraceae','species-ficus-benjamina','Ficus benjamina','Ficus benjamina','indoor','toxic','Puede causar irritación digestiva en mascotas si se ingiere.'],
            ['family-marantiaceae','species-goeppertia-orbifolia','Goeppertia orbifolia','Calatea orbifolia','humid','safe',NULL], ['family-marantiaceae','species-ctenanthe-burle-marxii','Ctenanthe burle-marxii','Ctenanthe','humid','safe',NULL],
            ['family-bromeliaceae','species-tillandsia-ionantha','Tillandsia ionantha','Tillandsia','humid','safe',NULL], ['family-bromeliaceae','species-guzmania-lingulata','Guzmania lingulata','Guzmania','humid','safe',NULL],
            ['family-rutaceae','species-citrus-limon','Citrus limon','Limonero','sun','caution','Evita la ingestión de hojas y aceites por mascotas.'],
            ['family-rosaceae','species-rosa-chinensis','Rosa chinensis','Rosa china','sun','safe','Las espinas requieren una ubicación segura.'], ['family-rosaceae','species-fragaria-ananassa','Fragaria × ananassa','Fresa','sun','safe',NULL],
            ['family-apiaceae','species-coriandrum-sativum','Coriandrum sativum','Cilantro','sun','safe',NULL],
            ['family-asphodelaceae','species-aloe-vera','Aloe vera','Sábila','dry','toxic','Puede causar malestar digestivo en perros y gatos si se ingiere.'], ['family-asphodelaceae','species-haworthiopsis-fasciata','Haworthiopsis fasciata','Haworthia cebra','dry','safe',NULL],
            ['family-euphorbiaceae','species-euphorbia-pulcherrima','Euphorbia pulcherrima','Nochebuena','indoor','caution','Su savia puede irritar; mantenla fuera del alcance de mascotas y niños.'], ['family-euphorbiaceae','species-euphorbia-tirucalli','Euphorbia tirucalli','Árbol de lápiz','dry','toxic','La savia es irritante; evita el contacto con ojos y la ingestión.'],
        ];
        $speciesQuery = $db->prepare("INSERT INTO plant_taxa (parent_id,taxon_type,code,scientific_name,common_name,ecosystem,toxicity_level,pet_safety_warning,is_active) VALUES (?,'species',?,?,?,?,?,?,1) ON DUPLICATE KEY UPDATE parent_id=VALUES(parent_id),scientific_name=VALUES(scientific_name),common_name=VALUES(common_name),ecosystem=VALUES(ecosystem),toxicity_level=VALUES(toxicity_level),pet_safety_warning=VALUES(pet_safety_warning),is_active=VALUES(is_active)");
        foreach ($species as [$family,$code,$scientific,$common,$ecosystem,$toxicity,$warning]) { $speciesQuery->execute([$familyIds[$family],$code,$scientific,$common,$ecosystem,$toxicity,$warning]); }
    }
}
