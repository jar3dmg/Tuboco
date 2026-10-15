<?php
declare(strict_types=1);

function userProfile(PDO $db, int $userId): ?array
{
    $query = $db->prepare('SELECT has_garden, garden_plants, has_pots, pot_plants, interests, goal FROM onboarding_profiles WHERE user_id = ?');
    $query->execute([$userId]);
    $profile = $query->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        return null;
    }

    foreach (['garden_plants', 'pot_plants', 'interests'] as $field) {
        $profile[$field] = json_decode($profile[$field] ?: '[]', true);
    }

    return $profile;
}

function saveProfile(PDO $db, array $user, array $data): void
{
    $interests = array_values(array_filter($data['interests'] ?? []));
    $goal = trim($data['goal'] ?? '');

    if (!$interests || $goal === '') {
        respond(['error' => 'Elige al menos un interés y un objetivo.'], 422);
    }

    $query = $db->prepare('INSERT INTO onboarding_profiles(user_id, has_garden, garden_plants, has_pots, pot_plants, interests, goal) VALUES (?, ?, ?, ?, ?, ?, ?) ON CONFLICT(user_id) DO UPDATE SET has_garden = excluded.has_garden, garden_plants = excluded.garden_plants, has_pots = excluded.has_pots, pot_plants = excluded.pot_plants, interests = excluded.interests, goal = excluded.goal, completed_at = CURRENT_TIMESTAMP');
    $query->execute([(int) $user['id'], !empty($data['has_garden']) ? 1 : 0, json_encode([]), !empty($data['has_pots']) ? 1 : 0, json_encode([]), json_encode($interests), $goal]);
    respond(['ok' => true]);
}
