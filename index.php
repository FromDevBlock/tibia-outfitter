<?php
declare(strict_types=1);

const WEBHOOK_AUTH_TOKEN = 'cherrymm';

function jsonOut(int $code, array $data): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function readJsonBody(): array {
  $raw = file_get_contents('php://input');
  if (!$raw) return [];
  $json = json_decode($raw, true);
  return is_array($json) ? $json : [];
}

function getToken(): ?string {
  $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (stripos($auth, 'Bearer ') === 0) {
    return trim(substr($auth, 7));
  }
  if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
    return trim($_SERVER['HTTP_X_AUTH_TOKEN']);
  }
  return null;
}

/*
  ============================
  PÁGINA INICIAL VISÍVEL
  ============================
*/

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  echo '<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cherry MM • Webhook</title>
<style>

body{
background:#0f0f14;
color:#fff;
font-family:Arial;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
margin:0;
}

.box{
background:#1a1b26;
padding:30px;
border-radius:10px;
box-shadow:0 0 20px rgba(0,0,0,.5);
text-align:center;
}

.ok{
color:#00ff88;
font-size:22px;
margin-bottom:10px;
}

.small{
color:#aaa;
font-size:14px;
}

</style>
</head>
<body>

<div class="box">

<div class="ok">
Webhook Cherry MM funcionando
</div>

<div class="small">
Servidor ativo em:<br>
'.htmlspecialchars($_SERVER['HTTP_HOST']).'
</div>

<div class="small" style="margin-top:10px">
'.date('d/m/Y H:i:s').'
</div>

</div>

</body>
</html>';

  exit;
}

/*
  ============================
  WEBHOOK ASAAS
  ============================
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $token = getToken();

  if (!$token || !hash_equals(WEBHOOK_AUTH_TOKEN, $token)) {

    jsonOut(401, [
      'error' => 'unauthorized'
    ]);

  }

  $payload = readJsonBody();

  file_put_contents(
    __DIR__.'/asaas.log',
    date('[d/m/Y H:i:s] ') . json_encode($payload) . PHP_EOL,
    FILE_APPEND
  );

  $id = $payload['data']['id'] ?? null;

  jsonOut(200, [

    'approved' => true,

    'id' => $id,

    'message' => 'Transferência autorizada automaticamente'

  ]);

}

jsonOut(405, [
  'error' => 'method_not_allowed'
]);
