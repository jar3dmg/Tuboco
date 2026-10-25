<?php
declare(strict_types=1);

function userProfile(PDO $db, int $userId): ?array
{
    $profileQuery = $db->prepare('SELECT knowledge_level, has_pets, weak_connection, theme, font_size, reading_mode, font_mode FROM user_profiles WHERE user_id = ?');
    $profileQuery->execute([$userId]);
    $profile = $profileQuery->fetch(PDO::FETCH_ASSOC);
    if ($profile === false) {
        return null;
    }

    $tags = $db->prepare('SELECT t.code, t.name FROM user_tags ut INNER JOIN tags t ON t.id = ut.tag_id WHERE ut.user_id = ?');
    $tags->execute([$userId]);
    $values = $tags->fetchAll(PDO::FETCH_ASSOC);
    $profile['has_garden'] = (int) (bool) array_filter($values, static fn (array $tag): bool => $tag['code'] === 'context-garden');
    $profile['has_pots'] = (int) (bool) array_filter($values, static fn (array $tag): bool => $tag['code'] === 'context-pot');
    $profile['garden_plants'] = [];
    $profile['pot_plants'] = [];
    $profile['interests'] = array_values(array_map(static fn (array $tag): string => $tag['name'], array_filter($values, static fn (array $tag): bool => str_starts_with($tag['code'], 'interest-'))));
    $goals = array_values(array_filter($values, static fn (array $tag): bool => str_starts_with($tag['code'], 'goal-')));
    $profile['goal'] = $goals[0]['name'] ?? '';

    return $profile;
}

function saveInterfacePreferences(PDO $db, array $user, array $data): void
{
    $theme = $data['theme'] ?? 'light';
    $fontSize = $data['font_size'] ?? 'normal';
    $readingMode = !empty($data['reading_mode']) ? 1 : 0;
    $fontMode = $data['font_mode'] ?? 'standard';
    $level = $data['knowledge_level'] ?? 'beginner';

    if (!in_array($theme, ['light', 'dark'], true) || !in_array($fontSize, ['small', 'normal', 'large'], true) || !in_array($fontMode, ['standard', 'dyslexia'], true) || !in_array($level, ['beginner', 'intermediate', 'advanced'], true)) {
        respond(['error' => 'La preferencia seleccionada no es válida.'], 422);
    }

    $query = $db->prepare('UPDATE user_profiles SET theme = ?, font_size = ?, reading_mode = ?, font_mode = ?, knowledge_level = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
    $query->execute([$theme, $fontSize, $readingMode, $fontMode, $level, (int) $user['id']]);
    respond(['ok' => true]);
}

function saveProfile(PDO $db, array $user, array $data): void
{
    $interests = array_values(array_filter($data['interests'] ?? []));
    $goal = trim($data['goal'] ?? '');

    if (!$interests || $goal === '') {
        respond(['error' => 'Elige al menos un interés y un objetivo.'], 422);
    }

    $userId = (int) $user['id'];
    $profile = $db->prepare('INSERT INTO user_profiles(user_id, created_at, updated_at) VALUES (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ON CONFLICT(user_id) DO UPDATE SET updated_at = CURRENT_TIMESTAMP');
    $profile->execute([$userId]);

    $db->prepare("DELETE FROM user_tags WHERE user_id = ? AND tag_id IN (SELECT id FROM tags WHERE code LIKE 'interest-%' OR code LIKE 'goal-%' OR code IN ('context-garden', 'context-pot'))")->execute([$userId]);
    $codes = [];
    foreach ($interests as $interest) {
        $codes[] = match ($interest) { 'Botánica básica' => 'interest-basic-botany', 'Huertos y frutales' => 'interest-orchard', 'Cuidado de plantas' => 'interest-care', 'Paisajismo' => 'interest-landscaping', 'Plantas de interior' => 'interest-indoor', 'Botánica y salud' => 'interest-health', default => '' };
    }
    $codes[] = match ($goal) { 'Cuidar mis plantas' => 'goal-care', 'Aprender por diversión' => 'goal-learn', 'Crear un huerto' => 'goal-orchard', 'Desarrollo profesional' => 'goal-professional', default => '' };
    if (!empty($data['has_garden'])) { $codes[] = 'context-garden'; }
    if (!empty($data['has_pots'])) { $codes[] = 'context-pot'; }
    $tag = $db->prepare('INSERT INTO user_tags(user_id, tag_id, created_at) SELECT ?, id, CURRENT_TIMESTAMP FROM tags WHERE code = ?');
    foreach (array_filter($codes) as $code) { $tag->execute([$userId, $code]); }
    respond(['ok' => true]);
}
