<?php
declare(strict_types=1);

function currentUser(PDO $db): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $query = $db->prepare('SELECT id, name, email FROM users WHERE id = ?');
    $query->execute([$_SESSION['user_id']]);
    return $query->fetch(PDO::FETCH_ASSOC) ?: null;
}

function registerUser(PDO $db, array $data): void
{
    $name = trim($data['name'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        respond(['error' => 'Completa tu nombre, un correo válido y una contraseña de al menos 6 caracteres.'], 422);
    }

    $query = $db->prepare('INSERT INTO users(name, email, password) VALUES (?, ?, ?)');
    $query->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    $_SESSION['user_id'] = (int) $db->lastInsertId();
    respond(['ok' => true]);
}

function loginUser(PDO $db, array $data): void
{
    $query = $db->prepare('SELECT * FROM users WHERE email = ?');
    $query->execute([strtolower(trim($data['email'] ?? ''))]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data['password'] ?? '', $user['password'])) {
        respond(['error' => 'Correo o contraseña incorrectos.'], 401);
    }

    $_SESSION['user_id'] = (int) $user['id'];
    respond(['ok' => true]);
}
