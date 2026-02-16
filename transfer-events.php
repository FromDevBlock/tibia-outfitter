<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

const TOKEN = 'cherrymm';
// Caminho para o "Map" em disco (Railway permite escrita no /tmp ou na pasta do app)
define("CACHE_DIR", sys_get_temp_dir() . "/asaas_cache_");

/**
 * Funções que simulam o comportamento de um Map persistente
 */
function map_has($id) {
    return file_exists(CACHE_DIR . md5($id));
}

function map_set($id) {
    file_put_contents(CACHE_DIR . md5($id), time());
}

function map_delete($id) {
    if (map_has($id)) unlink(CACHE_DIR . md5($id));
}

// 1. Validação de Segurança
$token = $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? $_SERVER['HTTP_X_WEBHOOK_TOKEN'] ?? null;
if (!$token || !hash_equals(TOKEN, $token)) {
    http_response_code(401);
    exit;
}

// 2. Leitura do Payload
$input = file_get_contents("php://input");
$data = json_decode($input, true);
$event = $data['event'] ?? '';
$transferId = $data['transfer']['id'] ?? null;

if (!$transferId) exit;

error_log("Webhook: $event | ID: $transferId");

// 3. Mecanismo de Aprovação (O Fluxo dos 5 segundos)
if ($event === 'TRANSFER_CREATED') {

    if (map_has($transferId)) {
        // SEGUNDA CHAMADA: Encontrou no "Map", então aprova
        map_delete($transferId);
        
        echo json_encode([
            "status" => "APPROVED"
        ]);
        exit;
    } else {
        // PRIMEIRA CHAMADA: Não está no "Map", salva e retorna 200
        map_set($transferId);

        http_response_code(200);
        echo json_encode([
            "ok" => true,
            "message" => "Aguardando validação"
        ]);
        exit;
    }
}

// Outros eventos (DONE, FAILED) apenas confirmamos o recebimento
http_response_code(200);
echo json_encode(["ok" => true]);
