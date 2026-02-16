<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

const TOKEN = 'cherrymm';

// Validação de Token
$token = $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? $_SERVER['HTTP_X_WEBHOOK_TOKEN'] ?? null;
if (!$token || !hash_equals(TOKEN, $token)) {
    http_response_code(401);
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$event = $data['event'] ?? '';
$transfer = $data['transfer'] ?? [];
$externalRef = $transfer['externalReference'] ?? '';

// LOG NO CONSOLE DA RAILWAY PARA VOCÊ VER
error_log("EVENTO: $event | REF: $externalRef");

if ($event === 'TRANSFER_CREATED') {
    // Se no externalReference tiver a nossa flag '_APPROVE'
    // e o status for PENDING (segunda chamada do Asaas)
    if (strpos($externalRef, '_APPROVE') !== false && $transfer['status'] === 'PENDING') {
        echo json_encode(["status" => "APPROVED"]);
        exit;
    }
}

// Para a primeira chamada ou outros eventos, apenas 200 OK
http_response_code(200);
echo json_encode(["ok" => true]);
