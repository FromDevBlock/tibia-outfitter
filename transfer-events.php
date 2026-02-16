<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

const TOKEN = 'cherrymm';
const LOG = '/tmp/asaas_transfer_events.log'; 

function getToken(): ?string {

  if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {

    $auth = $_SERVER['HTTP_AUTHORIZATION'];

    if (stripos($auth, 'Bearer ') === 0)
      return trim(substr($auth, 7));

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

function logPayload($data) {
  @file_put_contents(
    LOG,
    date("[d/m/Y H:i:s] ") . json_encode($data) . PHP_EOL,
    FILE_APPEND
  );
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  echo "transfer events webhook ativo";

  exit;

}

if ($_SERVER['REQUEST_METHOD'] !== 'POST')

  out(405, ['ok'=>false]);

$token = getToken();

if (!$token || !hash_equals(TOKEN, $token))

  out(401, ['ok'=>false]);

$data = json_decode(file_get_contents("php://input"), true);

logPayload($data);

$event = $data['event'] ?? null;

$transfer = $data['transfer'] ?? [];

out(200, [

  'ok' => true,

  'event' => $event,

  'transferId' => $transfer['id'] ?? null,

  'status' => $transfer['status'] ?? null

]);
