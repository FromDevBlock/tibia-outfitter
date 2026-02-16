<?php
declare(strict_types=1);

[span_2](start_span)// Define o tipo de conteúdo como JSON[span_2](end_span)
header('Content-Type: application/json; charset=utf-8');

[span_3](start_span)const TOKEN = 'cherrymm';[span_3](end_span)

/**
 * [span_4](start_span)[span_5](start_span)Obtém o token dos headers da requisição[span_4](end_span)[span_5](end_span)
 */
function getToken(): ?string {
  [span_6](start_span)if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {[span_6](end_span)
    [span_7](start_span)$auth = $_SERVER['HTTP_AUTHORIZATION'];[span_7](end_span)
    [span_8](start_span)if (stripos($auth, 'Bearer ') === 0)[span_8](end_span)
      [span_9](start_span)return trim(substr($auth, 7));[span_9](end_span)
  }

  [span_10](start_span)return $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'][span_10](end_span)
    ?? [span_11](start_span)$_SERVER['HTTP_X_WEBHOOK_TOKEN'][span_11](end_span)
    ?? [span_12](start_span)$_SERVER['HTTP_X_AUTH_TOKEN'][span_12](end_span)
    ?? [span_13](start_span)$_SERVER['HTTP_ACCESS_TOKEN'][span_13](end_span)
    [span_14](start_span)?? null;[span_14](end_span)
}

/**
 * [span_15](start_span)Responde ao Asaas e encerra o script[span_15](end_span)
 */
function out($code, $data) {
  [span_16](start_span)http_response_code($code);[span_16](end_span)
  [span_17](start_span)echo json_encode($data, JSON_UNESCAPED_UNICODE);[span_17](end_span)
  [span_18](start_span)exit;[span_18](end_span)
}

[span_19](start_span)// 1. Validação do Método[span_19](end_span)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  [span_20](start_span)echo "transfer events webhook ativo";[span_20](end_span)
  [span_21](start_span)exit;[span_21](end_span)
}

[span_22](start_span)if ($_SERVER['REQUEST_METHOD'] !== 'POST')[span_22](end_span)
  [span_23](start_span)out(405, ['ok' => false]);[span_23](end_span)

[span_24](start_span)// 2. Validação do Token[span_24](end_span)
[span_25](start_span)$token = getToken();[span_25](end_span)
[span_26](start_span)if (!$token || !hash_equals(TOKEN, $token))[span_26](end_span)
  [span_27](start_span)out(401, ['ok' => false]);[span_27](end_span)

[span_28](start_span)// 3. Leitura do Payload[span_28](end_span)
[span_29](start_span)$input = file_get_contents("php://input");[span_29](end_span)
[span_30](start_span)$data = json_decode($input, true);[span_30](end_span)

// --- LOG NO CONSOLE DO SERVIDOR ---
// Envia o JSON recebido para o log de erros do sistema (error_log)
// Isso não cria arquivos novos e não gera erro de permissão.
error_log("[Asaas Webhook] Payload recebido: " . $input);

[span_31](start_span)$event = $data['event'] ?? null;[span_31](end_span)
$transfer = $data['transfer'] ?? [span_32](start_span)[];[span_32](end_span)

[span_33](start_span)// 4. Retorno de Sucesso[span_33](end_span)
out(200, [
  'ok' => true,
  'event' => $event,
  [span_34](start_span)'transferId' => $transfer['id'] ?? null,[span_34](end_span)
  [span_35](start_span)'status' => $transfer['status'] ?? null[span_35](end_span)
]);
