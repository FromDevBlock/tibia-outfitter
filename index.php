<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

/*
  index.php - Webhook Asaas (Transfer events)
  - Página GET pra mostrar que tá rodando
  - POST pra receber eventos de transferências:
    TRANSFER_CREATED, TRANSFER_PENDING, TRANSFER_IN_BANK_PROCESSING,
    TRANSFER_BLOCKED, TRANSFER_DONE, TRANSFER_FAILED, TRANSFER_CANCELLED,
    TRANSFER_CANCELLED

  Configure no Asaas (Transfer events webhook):
  - URL: https://seu-app.up.railway.app/
  - Token de autenticação: coloque o mesmo em WEBHOOK_AUTH_TOKEN
  - Habilitar validação do webhook: ligado
*/

const WEBHOOK_AUTH_TOKEN = 'cherrymm';
const LOG_FILE = __DIR__ . '/asaas_transfer_events.log';

function getBearerToken(): ?string {
  $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if ($auth && stripos($auth, 'Bearer ') === 0) return trim(substr($auth, 7));
  if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) return trim($_SERVER['HTTP_X_AUTH_TOKEN']);
  if (!empty($_SERVER['HTTP_ASAAS_ACCESS_TOKEN'])) return trim($_SERVER['HTTP_ASAAS_ACCESS_TOKEN']);
  if (!empty($_SERVER['HTTP_X_WEBHOOK_TOKEN'])) return trim($_SERVER['HTTP_X_WEBHOOK_TOKEN']);
  if (!empty($_SERVER['HTTP_ACCESS_TOKEN'])) return trim($_SERVER['HTTP_ACCESS_TOKEN']);
  return null;
}

function jsonOut(int $code, array $data): void {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function readJsonBody(): array {
  $raw = file_get_contents('php://input');
  if ($raw === false || trim($raw) === '') return [];
  $json = json_decode($raw, true);
  return is_array($json) ? $json : [];
}

function safeLog(array $payload): void {
  $line = date('[c] ') . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
  @file_put_contents(LOG_FILE, $line, FILE_APPEND);
}

function pick(array $arr, array $keys, $default = null) {
  foreach ($keys as $k) {
    if (array_key_exists($k, $arr)) return $arr[$k];
  }
  return $default;
}

/*
  ============================
  GET - página inicial
  ============================
*/
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $host = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'unknown');
  $time = date('d/m/Y H:i:s');
  echo "<!doctype html>
<html lang='pt-br'>
<head>
<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Cherry MM • Asaas Webhook</title>
<style>
  body{margin:0;background:#0f0f14;color:#fff;font-family:Arial;height:100vh;display:flex;align-items:center;justify-content:center}
  .box{background:#1a1b26;border-radius:12px;padding:28px 26px;box-shadow:0 20px 60px rgba(0,0,0,.45);text-align:center;max-width:520px}
  .ok{font-size:20px;color:#00ff88;margin-bottom:10px}
  .small{font-size:13px;color:#a8a8b3;line-height:1.5}
  .pill{display:inline-block;margin-top:12px;background:#24263a;border:1px solid rgba(255,255,255,.08);padding:8px 10px;border-radius:999px;font-size:12px;color:#d7d7de}
</style>
</head>
<body>
  <div class='box'>
    <div class='ok'>Webhook Asaas (Transfer events) ativo</div>
    <div class='small'>Host: <b>{$host}</b><br>Hora: <b>{$time}</b></div>
    <div class='pill'>GET: OK • POST: / (Asaas)</div>
  </div>
</body>
</html>";
  exit;
}

/*
  ============================
  POST - recebe eventos
  ============================
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  jsonOut(405, ['ok' => false, 'error' => 'method_not_allowed']);
}

$incomingToken = getBearerToken();
if (!$incomingToken || !hash_equals(WEBHOOK_AUTH_TOKEN, $incomingToken)) {
  jsonOut(401, ['ok' => false, 'error' => 'unauthorized']);
}

$payload = readJsonBody();

/*
  IMPORTANTE: não quebrar se vierem campos novos
  - Acessos sempre com ?? e is_array
*/
$event = (string)($payload['event'] ?? 'UNKNOWN');
$transfer = is_array($payload['transfer'] ?? null) ? $payload['transfer'] : [];

$transferId = (string)pick($transfer, ['id'], '');
$status = (string)pick($transfer, ['status'], '');
$type = (string)pick($transfer, ['type'], '');
$value = (float)pick($transfer, ['value'], 0);
$netValue = (float)pick($transfer, ['netValue'], 0);
$operationType = (string)pick($transfer, ['operationType'], '');
$failReason = $transfer['failReason'] ?? null;
$receiptUrl = $transfer['transactionReceiptUrl'] ?? null;

safeLog([
  'event' => $event,
  'transferId' => $transferId,
  'status' => $status,
  'type' => $type,
  'value' => $value,
  'netValue' => $netValue,
  'operationType' => $operationType,
  'failReason' => $failReason,
  'transactionReceiptUrl' => $receiptUrl,
  'raw' => $payload
]);

/*
  Aqui você pode integrar com seu bot:
  - se TRANSFER_DONE => marcar como confirmado no seu sistema
  - se TRANSFER_FAILED => avisar / abrir disputa etc
*/

$known = [
  'TRANSFER_CREATED',
  'TRANSFER_PENDING',
  'TRANSFER_IN_BANK_PROCESSING',
  'TRANSFER_BLOCKED',
  'TRANSFER_DONE',
  'TRANSFER_FAILED',
  'TRANSFER_CANCELLED'
];

jsonOut(200, [
  'ok' => true,
  'received' => [
    'event' => $event,
    'transferId' => $transferId ?: null,
    'status' => $status ?: null
  ],
  'knownEvent' => in_array($event, $known, true)
]);
