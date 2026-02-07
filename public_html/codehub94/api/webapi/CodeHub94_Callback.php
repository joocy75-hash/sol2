<?php
// callback.php - FINAL VERSION WITH DYNAMIC SUFFIX FROM CONFIG
include "../../conn.php";

// ====== LOAD CONFIG FROM CLIENT SIDE ======
require_once __DIR__ . '/gamblly_apiconfig.php';

date_default_timezone_set("Asia/Kolkata");
$now = date("Y-m-d H:i:s");

// Disable logging
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

function writeLog($status, $msg, $details = []) {
    return;
}

$rawBody = file_get_contents("php://input");
$callbackData = json_decode($rawBody, true);

writeLog('CALLBACK_RECEIVED', 'Incoming request', ['data' => $callbackData]);

// Allow GET & POST
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'msg' => 'Method not allowed']);
    exit;
}

// Health check
if (empty($rawBody) || $callbackData === null || empty($callbackData)) {
    writeLog('INFO', 'Health check');
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'balance' => 0.00
    ]);
    exit;
}

// Must have player_uid
if (!isset($callbackData['player_uid'])) {
    writeLog('FAILED', 'Missing player_uid', ['data' => $callbackData]);
    http_response_code(400);
    echo json_encode(['success' => false, 'msg' => 'Invalid request', 'received' => $callbackData]);
    exit;
}

$player_uid = trim($callbackData['player_uid']);
$received_api_key = trim($callbackData['api_key'] ?? '');

// ====== VALIDATE API KEY ======
// Ensure request comes from trusted server (which knows our API Key)
if ($received_api_key !== $CLIENT_CONFIG['api_key']) {
    writeLog('FAILED', 'Invalid API Key', ['received' => $received_api_key]);
    http_response_code(403);
    echo json_encode(['success' => false, 'msg' => 'Unauthorized']);
    exit;
}

// ====== USER ID EXTRACTION (DIRECT) ======
// Server already stripped prefix/suffix. player_uid IS the user_id.
$user_id = (int)$player_uid;

if ($user_id <= 0) {
    writeLog('FAILED', 'Invalid user_id', ['player_uid' => $player_uid]);
    http_response_code(400);
    echo json_encode(['success' => false, 'msg' => 'Invalid player ID']);
    exit;
}

/*
// PREFIX/SUFFIX VALIDATION REMOVED - HANDLED BY SERVER
$allowedPrefixes = ...
*/

writeLog('INFO', 'Player validated', [
    'user_id' => $user_id
]);

// ====== AUTO CREATE TABLE IF NOT EXISTS ======
$checkTable = $conn->query("SHOW TABLES LIKE 'transactions'");
if ($checkTable->num_rows == 0) {
    $createTable = "CREATE TABLE `transactions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `txn_id` varchar(255) DEFAULT NULL,
        `game_name` varchar(255) DEFAULT NULL,
        `bet` decimal(10,2) DEFAULT 0.00,
        `win` decimal(10,2) DEFAULT 0.00,
        `result` varchar(50) DEFAULT NULL,
        `api_key` varchar(255) DEFAULT NULL,
        `response_data` text DEFAULT NULL,
        `created_at` datetime DEFAULT current_timestamp(),
        `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_user_id` (`user_id`),
        KEY `idx_txn_id` (`txn_id`),
        KEY `idx_game_name` (`game_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if ($conn->query($createTable)) {
        writeLog('INFO', 'Table transactions created successfully');
    } else {
        writeLog('ERROR', 'Failed to create transactions table', ['error' => $conn->error]);
    }
}

// ====== INSERT TRANSACTION ======
$txn_id = $callbackData['serial_number'] ?? '';
$game_name = $callbackData['game_name'] ?? '';
$api_key = $callbackData['api_key'] ?? '';
$bet_amt = (float)($callbackData['bet_amount'] ?? 0);
$win_amt = (float)($callbackData['win_amount'] ?? 0);
$action_type = $callbackData['action'] ?? '';
$response_data = json_encode($callbackData);

// Prepare insert
$stmtTxn = $conn->prepare("INSERT INTO transactions (user_id, txn_id, game_name, bet, win, result, api_key, response_data, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if ($stmtTxn) {
    $stmtTxn->bind_param("issddssss", $user_id, $txn_id, $game_name, $bet_amt, $win_amt, $action_type, $api_key, $response_data, $now);
    $stmtTxn->execute();
    $stmtTxn->close();
} else {
    writeLog('ERROR', 'Failed to prepare transaction insert', ['error' => $conn->error]);
}

// Get current balance
$query = "SELECT motta FROM shonu_kaichila WHERE balakedara = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$current_balance = (float)($row['motta'] ?? 0);
$stmt->close();

// Apply bet/win
$action = $callbackData['action'] ?? '';
$bet_amount = isset($callbackData['bet_amount']) ? (float)$callbackData['bet_amount'] : 0;
$win_amount = isset($callbackData['win_amount']) ? (float)$callbackData['win_amount'] : 0;

// Apply Bet
if ($bet_amount > 0) {
    $current_balance -= $bet_amount;
    if ($current_balance < 0) $current_balance = 0;
}

// Apply Win
if ($win_amount > 0) {
    $current_balance += $win_amount;
}

// Update balance
$updateStmt = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
$updateStmt->bind_param("di", $current_balance, $user_id);
$updateStmt->execute();
$updateStmt->close();

writeLog('SUCCESS', 'Callback processed successfully', [
    'user_id' => $user_id,
    'action' => $action,
    'bet_amount' => $bet_amount,
    'win_amount' => $win_amount,
    'new_balance' => $current_balance,
    'player_uid' => $player_uid
]);

// Return balance
http_response_code(200);
echo json_encode([
    'success' => true,
    'balance' => number_format($current_balance, 2, '.', '')
]);
?>