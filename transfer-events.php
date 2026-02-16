<?php
declare(strict_types=1);

// 1. Configurações de erro para não travar o servidor
ini_set('display_errors', '0');
error_reporting(0);

// 2. Garante o cabeçalho JSON antes de qualquer coisa
header('Content-Type: application/json; charset=utf-8');

const TOKEN = 'cherrymm';

// 3. Função de saída simplificada para garantir resposta rápida
function sendResponse($code, $data) {
    if (!headers_sent()) {
        http_response_code($code);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 4. Validação do Token
$token = $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] 
      ?? $_SERVER['HTTP_X_WEBHOOK_TOKEN'] 
      ?? $_SERVER['HTTP_X_AUTH_TOKEN'] 
      ?? null;

if (!$token || !hash_equals(TOKEN, $token)) {
    sendResponse(401, ['ok' => false, 'msg' => 'Unauthorized']);
}

// 5. Captura do Payload
$input = file_get_contents("php://input");
if (!$input) {
    sendResponse(200, ['ok' => true, 'msg' => 'Empty payload']);
}

$data = json_decode($input, true);

// 6. Log no console (Railway logs)
// Usamos error_log para não imprimir nada na tela e não dar 502
error_log("Webhook Asaas Recebido: " . ($data['event'] ?? 'sem evento'));

$event = $data['event'] ?? 'UNKNOWN';
$transfer = $data['transfer'] ?? [];

// 7. Resposta de Sucesso imediata
// Respondemos 200 para o Asaas não achar que o servidor caiu
sendResponse(200, [
    'ok' => true,
    'event' => $event,
    'transferId' => $transfer['id'] ?? null,
    'status' => $transfer['status'] ?? null,
    'failReason' => $transfer['failReason'] ?? null
]);
