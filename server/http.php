<?php
declare(strict_types=1);

function requestBody(): array
{
    return json_decode(file_get_contents('php://input'), true) ?: [];
}

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
