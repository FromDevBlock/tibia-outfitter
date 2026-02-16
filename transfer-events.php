<?php
declare(strict_types=1);

// Impede que erros de permissão ou avisos quebrem o JSON de saída
ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

const TOKEN = 'cherrymm';

function getToken(): ?string {
  if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth = $_SERVER['HTTP_AUTHORIZATION'];
    if (stripos($auth, 'Bearer ') === 0) {
      return trim(substr($auth, 7));
    }
  }

  return $_SERVER['HTTP_ASAAS_ACCESS_TOKEN']
    ?? $_SERVER['HTTP_X_WEBHOOK_TOKEN']
    ?? $_SERVER['HTTP_X_AUTH_TOKEN']
    ?? $_SERVER['HTTP_ACCESS_TOKEN']
    ?? null;
}

function out($code, $data) {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

// Validação básica de método
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  echo "transfer events webhook ativo";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  out(405, ['ok' => false]);
}

// Validação do Token
$token = getToken();
if (!$token || !hash_equals(TOKEN, $token)) {
  out(401, ['ok' => false]);
}

// Leitura do corpo da requisição
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    out(400, ['ok' => false, 'error' => 'Payload inválido']);
}

// Registra no log do servidor para debug (visível no console/terminal)
error_log("[Asaas Webhook] Dados: " . $input);

$event = $data['event'] ?? 'UNKNOWN';
$transfer = $data['transfer'] ?? [];

// Captura detalhes da transferência e possível motivo de falha
$tId = $transfer['id'] ?? null;
$tStatus = $transfer['status'] ?? 'UNKNOWN';
$failReason = $transfer['failReason'] ?? null;

// Resposta para o Asaas (Status 200 é obrigatório para "aprovar" o recebimento)
out(200, [
  'ok' => true,
  'event' => $event,
  'transferId' => $tId,
  'status' => $tStatus,
  'failReason' => $failReason
]);
