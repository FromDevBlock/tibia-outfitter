<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

const TOKEN = 'cherrymm';

function getToken(): ?string {
  $headers = getallheaders();
  return $headers['Asaas-Access-Token'] 
    ?? $headers['X-Webhook-Token'] 
    ?? $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] 
    ?? null;
}

$token = getToken();
if (!$token || !hash_equals(TOKEN, $token)) {
    http_response_code(401);
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    exit;
}

$event = $data['event'] ?? '';
$transfer = $data['transfer'] ?? [];
$transferId = $transfer['id'] ?? null;

// LOG PARA DEBUG NO CONSOLE
error_log("[Asaas Validation] Evento: $event | ID: $transferId");

/**
 * LÓGICA DE APROVAÇÃO:
 * O Asaas enviará o evento 'TRANSFER_CREATED' para validação.
 */
if ($event === 'TRANSFER_CREATED') {
    // Aqui você verificaria no seu banco de dados se esse ID existe.
    // Como estamos validando, vamos responder APPROVED:
    echo json_encode([
        "status" => "APPROVED"
    ]);
    exit;
}

// Para outros eventos (como falha ou conclusão), apenas retorne 200 OK
http_response_code(200);
echo json_encode(["ok" => true]);
