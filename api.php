<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/server/bootstrap.php';
require __DIR__ . '/server/http.php';
require __DIR__ . '/server/users.php';
require __DIR__ . '/server/profiles.php';

$database = database();
$action = $_GET['action'] ?? '';
$data = requestBody();

try {
    if ($action === 'status') {
        $user = currentUser($database);
        respond(['user' => $user, 'profile' => $user ? userProfile($database, (int) $user['id']) : null]);
    }

    if ($action === 'register') {
        registerUser($database, $data);
    }

    if ($action === 'login') {
        loginUser($database, $data);
    }

    if ($action === 'logout') {
        session_destroy();
        respond(['ok' => true]);
    }

    $user = currentUser($database);
    if (!$user) {
        respond(['error' => 'Inicia sesión para continuar.'], 401);
    }

    if ($action === 'save_profile') {
        saveProfile($database, $user, $data);
    }

    if ($action === 'save_preferences') {
        saveInterfacePreferences($database, $user, $data);
    }

    respond(['error' => 'Acción no encontrada.'], 404);
} catch (PDOException $exception) {
    $message = str_contains($exception->getMessage(), 'UNIQUE')
        ? 'Ya existe una cuenta con ese correo.'
        : 'No fue posible guardar la información.';

    respond(['error' => $message], 422);
}
