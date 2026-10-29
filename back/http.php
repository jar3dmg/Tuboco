<?php
declare(strict_types=1);

/** Lee el cuerpo JSON de la solicitud; devuelve un arreglo vacío si no existe. */
function requestBody(): array
{
    return json_decode(file_get_contents('php://input'), true) ?: [];
}

/** Envía una respuesta JSON y finaliza la ejecución. */
function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
