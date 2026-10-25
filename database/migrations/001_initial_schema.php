<?php
declare(strict_types=1);

return [
    'version' => '001_initial_schema',
    'up' => static function (PDO $db): void {
        $hasTable = static function (string $table) use ($db): bool {
            $query = $db->prepare("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = ?");
            $query->execute([$table]);
            return (bool) $query->fetchColumn();
        };
        $columns = static function (string $table) use ($db): array {
            return array_column($db->query("PRAGMA table_info($table)")->fetchAll(), 'name');
        };

        // Preserve the two pre-migration tables so their data can be copied below.
        if ($hasTable('onboarding_profiles') && !$hasTable('legacy_onboarding_profiles')) {
            $db->exec('ALTER TABLE onboarding_profiles RENAME TO legacy_onboarding_profiles');
        }
        if ($hasTable('users') && in_array('name', $columns('users'), true) && !$hasTable('legacy_users')) {
            $db->exec('ALTER TABLE users RENAME TO legacy_users');
        }

        $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    paternal_last_name TEXT,
    maternal_last_name TEXT,
    email TEXT NOT NULL UNIQUE,
    phone TEXT,
    password TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TEXT,
    updated_at TEXT
);
CREATE TABLE IF NOT EXISTS regions (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL UNIQUE, name TEXT NOT NULL, description TEXT, is_active INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)));
CREATE TABLE IF NOT EXISTS course_categories (id INTEGER PRIMARY KEY AUTOINCREMENT, parent_id INTEGER REFERENCES course_categories(id), code TEXT NOT NULL UNIQUE, name TEXT NOT NULL, description TEXT, is_active INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)));
CREATE TABLE IF NOT EXISTS plant_taxa (id INTEGER PRIMARY KEY AUTOINCREMENT, parent_id INTEGER REFERENCES plant_taxa(id), taxon_type TEXT NOT NULL CHECK (taxon_type IN ('family', 'species')), code TEXT NOT NULL UNIQUE, scientific_name TEXT NOT NULL, common_name TEXT, ecosystem TEXT, toxicity_level TEXT CHECK (toxicity_level IN ('safe', 'caution', 'toxic')), pet_safety_warning TEXT, is_active INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)));
CREATE TABLE IF NOT EXISTS tag_groups (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL UNIQUE, name TEXT NOT NULL, description TEXT);
CREATE TABLE IF NOT EXISTS tags (id INTEGER PRIMARY KEY AUTOINCREMENT, tag_group_id INTEGER NOT NULL REFERENCES tag_groups(id), code TEXT NOT NULL UNIQUE, name TEXT NOT NULL, description TEXT, sort_order INTEGER NOT NULL DEFAULT 0, is_active INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)));
CREATE TABLE IF NOT EXISTS user_profiles (user_id INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE, region_id INTEGER REFERENCES regions(id), knowledge_level TEXT NOT NULL DEFAULT 'beginner' CHECK (knowledge_level IN ('beginner', 'intermediate', 'advanced')), has_pets INTEGER NOT NULL DEFAULT 0 CHECK (has_pets IN (0, 1)), weak_connection INTEGER NOT NULL DEFAULT 0 CHECK (weak_connection IN (0, 1)), created_at TEXT, updated_at TEXT);
CREATE TABLE IF NOT EXISTS user_tags (user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE, tag_id INTEGER NOT NULL REFERENCES tags(id), priority INTEGER NOT NULL DEFAULT 3 CHECK (priority BETWEEN 1 AND 5), created_at TEXT, PRIMARY KEY (user_id, tag_id));
CREATE INDEX IF NOT EXISTS idx_user_tags_tag_user ON user_tags(tag_id, user_id);
CREATE TABLE IF NOT EXISTS user_plants (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE, plant_taxon_id INTEGER NOT NULL REFERENCES plant_taxa(id), context_tag_id INTEGER REFERENCES tags(id), quantity INTEGER CHECK (quantity IS NULL OR quantity > 0), notes TEXT, created_at TEXT);
CREATE INDEX IF NOT EXISTS idx_user_plants_user_taxon ON user_plants(user_id, plant_taxon_id);
CREATE TABLE IF NOT EXISTS courses (id INTEGER PRIMARY KEY AUTOINCREMENT, category_id INTEGER NOT NULL REFERENCES course_categories(id), level TEXT NOT NULL DEFAULT 'beginner' CHECK (level IN ('beginner', 'intermediate', 'advanced')), title TEXT NOT NULL, slug TEXT NOT NULL UNIQUE, short_description TEXT, description TEXT, cover_image_url TEXT, estimated_minutes INTEGER CHECK (estimated_minutes IS NULL OR estimated_minutes >= 0), status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')), is_free INTEGER NOT NULL DEFAULT 1 CHECK (is_free IN (0, 1)), created_at TEXT, updated_at TEXT, published_at TEXT);
CREATE TABLE IF NOT EXISTS course_tags (course_id INTEGER NOT NULL REFERENCES courses(id) ON DELETE CASCADE, tag_id INTEGER NOT NULL REFERENCES tags(id), weight INTEGER NOT NULL DEFAULT 0, is_required INTEGER NOT NULL DEFAULT 0 CHECK (is_required IN (0, 1)), PRIMARY KEY (course_id, tag_id));
CREATE INDEX IF NOT EXISTS idx_course_tags_tag_course ON course_tags(tag_id, course_id);
CREATE TABLE IF NOT EXISTS course_plant_taxa (course_id INTEGER NOT NULL REFERENCES courses(id) ON DELETE CASCADE, plant_taxon_id INTEGER NOT NULL REFERENCES plant_taxa(id), weight INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (course_id, plant_taxon_id));
CREATE INDEX IF NOT EXISTS idx_course_plant_taxa_taxon_course ON course_plant_taxa(plant_taxon_id, course_id);
CREATE TABLE IF NOT EXISTS course_regions (course_id INTEGER NOT NULL REFERENCES courses(id) ON DELETE CASCADE, region_id INTEGER NOT NULL REFERENCES regions(id), weight INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (course_id, region_id));
CREATE INDEX IF NOT EXISTS idx_course_regions_region_course ON course_regions(region_id, course_id);
CREATE TABLE IF NOT EXISTS course_modules (id INTEGER PRIMARY KEY AUTOINCREMENT, course_id INTEGER NOT NULL REFERENCES courses(id) ON DELETE CASCADE, title TEXT NOT NULL, description TEXT, position INTEGER NOT NULL CHECK (position > 0), estimated_minutes INTEGER CHECK (estimated_minutes IS NULL OR estimated_minutes >= 0), is_required INTEGER NOT NULL DEFAULT 1 CHECK (is_required IN (0, 1)), UNIQUE (course_id, position));
CREATE TABLE IF NOT EXISTS lessons (id INTEGER PRIMARY KEY AUTOINCREMENT, module_id INTEGER NOT NULL REFERENCES course_modules(id) ON DELETE CASCADE, title TEXT NOT NULL, description TEXT, position INTEGER NOT NULL CHECK (position > 0), estimated_minutes INTEGER CHECK (estimated_minutes IS NULL OR estimated_minutes >= 0), is_required INTEGER NOT NULL DEFAULT 1 CHECK (is_required IN (0, 1)), is_preview INTEGER NOT NULL DEFAULT 0 CHECK (is_preview IN (0, 1)), UNIQUE (module_id, position));
CREATE TABLE IF NOT EXISTS lesson_blocks (id INTEGER PRIMARY KEY AUTOINCREMENT, lesson_id INTEGER NOT NULL REFERENCES lessons(id) ON DELETE CASCADE, type TEXT NOT NULL CHECK (type IN ('text', 'video', 'document', 'image', 'link', 'quiz', 'practice', 'download')), title TEXT, body TEXT, content_data TEXT CHECK (content_data IS NULL OR json_valid(content_data)), position INTEGER NOT NULL CHECK (position > 0), is_required INTEGER NOT NULL DEFAULT 1 CHECK (is_required IN (0, 1)), low_bandwidth INTEGER NOT NULL DEFAULT 0 CHECK (low_bandwidth IN (0, 1)), UNIQUE (lesson_id, position));
CREATE TABLE IF NOT EXISTS user_courses (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE, course_id INTEGER NOT NULL REFERENCES courses(id), status TEXT NOT NULL DEFAULT 'not_started' CHECK (status IN ('not_started', 'in_progress', 'paused', 'completed', 'abandoned')), progress_percentage REAL NOT NULL DEFAULT 0 CHECK (progress_percentage BETWEEN 0 AND 100), final_score REAL CHECK (final_score IS NULL OR final_score BETWEEN 0 AND 100), started_at TEXT, last_accessed_at TEXT, completed_at TEXT, abandoned_at TEXT, abandonment_reason TEXT CHECK (abandonment_reason IS NULL OR abandonment_reason IN ('lack_of_time', 'lack_of_interest', 'difficulty', 'other')), created_at TEXT, updated_at TEXT, UNIQUE (user_id, course_id));
CREATE INDEX IF NOT EXISTS idx_user_courses_user_status ON user_courses(user_id, status);
CREATE TABLE IF NOT EXISTS user_lesson_progress (user_course_id INTEGER NOT NULL REFERENCES user_courses(id) ON DELETE CASCADE, lesson_id INTEGER NOT NULL REFERENCES lessons(id), progress_percentage REAL NOT NULL DEFAULT 0 CHECK (progress_percentage BETWEEN 0 AND 100), is_completed INTEGER NOT NULL DEFAULT 0 CHECK (is_completed IN (0, 1)), score REAL CHECK (score IS NULL OR score BETWEEN 0 AND 100), last_block_id INTEGER REFERENCES lesson_blocks(id), last_position_seconds INTEGER CHECK (last_position_seconds IS NULL OR last_position_seconds >= 0), started_at TEXT, completed_at TEXT, updated_at TEXT, PRIMARY KEY (user_course_id, lesson_id));
CREATE TABLE IF NOT EXISTS user_block_attempts (id INTEGER PRIMARY KEY AUTOINCREMENT, user_course_id INTEGER NOT NULL REFERENCES user_courses(id) ON DELETE CASCADE, lesson_block_id INTEGER NOT NULL REFERENCES lesson_blocks(id), attempt_number INTEGER NOT NULL DEFAULT 1 CHECK (attempt_number > 0), answer_data TEXT CHECK (answer_data IS NULL OR json_valid(answer_data)), score REAL CHECK (score IS NULL OR score BETWEEN 0 AND 100), is_passed INTEGER CHECK (is_passed IS NULL OR is_passed IN (0, 1)), feedback TEXT, submitted_at TEXT, UNIQUE (user_course_id, lesson_block_id, attempt_number));
CREATE TABLE IF NOT EXISTS recommendation_rules (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, priority INTEGER NOT NULL DEFAULT 0, is_active INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)), description TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE IF NOT EXISTS recommendation_rule_conditions (id INTEGER PRIMARY KEY AUTOINCREMENT, rule_id INTEGER NOT NULL REFERENCES recommendation_rules(id) ON DELETE CASCADE, attribute TEXT NOT NULL, operator TEXT NOT NULL CHECK (operator IN ('equals', 'has_tag', 'has_plant', 'abandoned_reason')), value TEXT NOT NULL CHECK (json_valid(value)));
CREATE TABLE IF NOT EXISTS recommendation_rule_actions (id INTEGER PRIMARY KEY AUTOINCREMENT, rule_id INTEGER NOT NULL REFERENCES recommendation_rules(id) ON DELETE CASCADE, action TEXT NOT NULL CHECK (action IN ('add_score', 'subtract_score', 'exclude_course', 'show_warning', 'prefer_text_content')), target_tag_id INTEGER REFERENCES tags(id), score INTEGER NOT NULL DEFAULT 0, message TEXT);
CREATE TABLE IF NOT EXISTS user_course_recommendations (user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE, course_id INTEGER NOT NULL REFERENCES courses(id) ON DELETE CASCADE, score REAL NOT NULL DEFAULT 0, reasons TEXT CHECK (reasons IS NULL OR json_valid(reasons)), is_opened INTEGER NOT NULL DEFAULT 0 CHECK (is_opened IN (0, 1)), is_dismissed INTEGER NOT NULL DEFAULT 0 CHECK (is_dismissed IN (0, 1)), generated_at TEXT, PRIMARY KEY (user_id, course_id));
CREATE INDEX IF NOT EXISTS idx_recommendations_user_score ON user_course_recommendations(user_id, score DESC);
SQL);

        $groups = [
            ['interest', 'Intereses', 'Temas que interesan al usuario.'], ['goal', 'Objetivos', 'Resultados que desea lograr.'],
            ['context', 'Contexto', 'Tipo de espacio o cultivo.'], ['light', 'Luz', 'Disponibilidad de luz.'],
            ['space', 'Espacio', 'Tamaño del espacio disponible.'], ['time', 'Tiempo', 'Tiempo disponible para aprender y cuidar.'],
            ['ecosystem', 'Ecosistema', 'Ambiente de cultivo o de la planta.'],
        ];
        $groupStatement = $db->prepare('INSERT INTO tag_groups (code, name, description) VALUES (?, ?, ?) ON CONFLICT(code) DO UPDATE SET name = excluded.name, description = excluded.description');
        foreach ($groups as $group) { $groupStatement->execute($group); }
        $groupIds = $db->query('SELECT code, id FROM tag_groups')->fetchAll(PDO::FETCH_KEY_PAIR);
        $tags = [
            ['interest', 'interest-basic-botany', 'Botánica básica'], ['interest', 'interest-orchard', 'Huertos y frutales'], ['interest', 'interest-care', 'Cuidado de plantas'], ['interest', 'interest-landscaping', 'Paisajismo'], ['interest', 'interest-indoor', 'Plantas de interior'], ['interest', 'interest-health', 'Botánica y salud'],
            ['goal', 'goal-care', 'Cuidar mis plantas'], ['goal', 'goal-learn', 'Aprender por diversión'], ['goal', 'goal-orchard', 'Crear un huerto'], ['goal', 'goal-professional', 'Desarrollo profesional'],
            ['context', 'context-pot', 'Maceta'], ['context', 'context-garden', 'Jardín'], ['context', 'context-balcony', 'Balcón o terraza'],
            ['light', 'light-low', 'Poca luz'], ['light', 'light-indirect', 'Luz indirecta'], ['light', 'light-sun', 'Mucha luz solar'],
            ['space', 'space-small', 'Poco espacio'], ['space', 'space-small-indoor', 'Poco espacio interior'], ['space', 'space-large-indoor', 'Mucho espacio interior'],
            ['time', 'time-low', 'Poco tiempo'], ['time', 'time-medium', 'Tiempo moderado'], ['time', 'time-high', 'Mucho tiempo'],
            ['ecosystem', 'ecosystem-indoor', 'Interior'], ['ecosystem', 'ecosystem-outdoor', 'Exterior'], ['ecosystem', 'ecosystem-shade', 'Sombra'], ['ecosystem', 'ecosystem-sun', 'Sol'], ['ecosystem', 'ecosystem-dry', 'Seco'], ['ecosystem', 'ecosystem-humid', 'Húmedo'],
        ];
        $tagStatement = $db->prepare('INSERT INTO tags (tag_group_id, code, name, sort_order) VALUES (?, ?, ?, ?) ON CONFLICT(code) DO UPDATE SET tag_group_id = excluded.tag_group_id, name = excluded.name, sort_order = excluded.sort_order');
        foreach ($tags as $position => [$group, $code, $name]) { $tagStatement->execute([$groupIds[$group], $code, $name, $position]); }

        if ($hasTable('legacy_users')) {
            $db->exec("INSERT INTO users (id, first_name, email, password, created_at, updated_at) SELECT id, trim(CASE WHEN name = '' THEN 'Usuario' ELSE name END), email, password, created_at, created_at FROM legacy_users");
        }
        if ($hasTable('legacy_onboarding_profiles')) {
            $db->exec("INSERT OR IGNORE INTO user_profiles (user_id, created_at, updated_at) SELECT user_id, completed_at, completed_at FROM legacy_onboarding_profiles");
            $tagIds = $db->query('SELECT code, id FROM tags')->fetchAll(PDO::FETCH_KEY_PAIR);
            $tagInsert = $db->prepare('INSERT INTO user_tags (user_id, tag_id, created_at) VALUES (?, ?, ?) ON CONFLICT(user_id, tag_id) DO NOTHING');
            foreach ($db->query('SELECT * FROM legacy_onboarding_profiles') as $profile) {
                $createdAt = $profile['completed_at'] ?: date('Y-m-d H:i:s');
                foreach (json_decode($profile['interests'] ?: '[]', true) ?: [] as $interest) {
                    $code = 'interest-' . match ($interest) { 'Botánica básica' => 'basic-botany', 'Huertos y frutales' => 'orchard', 'Cuidado de plantas' => 'care', 'Paisajismo' => 'landscaping', 'Plantas de interior' => 'indoor', 'Botánica y salud' => 'health', default => '' };
                    if ($code !== '' && isset($tagIds[$code])) { $tagInsert->execute([$profile['user_id'], $tagIds[$code], $createdAt]); }
                }
                $goalCode = match ($profile['goal']) { 'Cuidar mis plantas' => 'goal-care', 'Aprender por diversión' => 'goal-learn', 'Crear un huerto' => 'goal-orchard', 'Desarrollo profesional' => 'goal-professional', default => '' };
                if ($goalCode !== '' && isset($tagIds[$goalCode])) { $tagInsert->execute([$profile['user_id'], $tagIds[$goalCode], $createdAt]); }
                foreach ([['has_garden', 'context-garden'], ['has_pots', 'context-pot']] as [$column, $code]) { if (!empty($profile[$column])) { $tagInsert->execute([$profile['user_id'], $tagIds[$code], $createdAt]); } }
            }
        }
    },
];
