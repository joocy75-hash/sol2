<?php 
include "../../conn.php";
include "../../functions2.php";
// include "../../gamblly_apiconfig.php"; 
require_once __DIR__ . '/gamblly_apiconfig.php';

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

function writeLog($status, $msg, $details = []) {
    return;
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
header('Vary: Origin');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allow_origin = '';
if ($origin) {
    $stmt = $conn->prepare("SELECT domain FROM allowed_origins WHERE domain=? AND status=1");
    $stmt->bind_param("s", $origin);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $allow_origin = $origin;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if ($allow_origin) header("Access-Control-Allow-Origin: $allow_origin");
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ar-origin, ar-real-ip, ar-session');
    exit(0);
}

if ($allow_origin) {
    header("Access-Control-Allow-Origin: $allow_origin");
}

date_default_timezone_set("Asia/Kolkata");
$now = date("Y-m-d H:i:s");

// ========= DYNAMIC HOME_URL GENERATE =========
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
$home_url = $protocol . $host . '';
// Example result: https://jalwagame.codehub94.online/blaze9/home.php
// Agar koi specific path chahiye (jaise /player/home) to yahan add kar sakte hain
// $home_url .= '/player/home';  // Need ho to uncomment

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['code' => 11, 'msg' => 'Method not allowed', 'serviceNowTime' => $now]);
    exit;
}

$rawBody = file_get_contents("php://input");
$shonupost = json_decode($rawBody, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['code' => 7, 'msg' => 'Invalid JSON', 'serviceNowTime' => $now]);
    exit;
}

if (empty($shonupost['gameCode']) || empty($shonupost['vendorCode'])) {
    http_response_code(200);
    echo json_encode(['code' => 7, 'msg' => 'gameCode or vendorCode missing', 'serviceNowTime' => $now]);
    exit;
}

$gameCode   = $shonupost['gameCode'];
$vendorCode = $shonupost['vendorCode'];
// Use language from POST, fallback to Config, default to 'en'
$inputLang = $shonupost['language'] ?? '';
$language = (!empty($inputLang) && $inputLang !== '0') ? $inputLang : ($CLIENT_CONFIG['language'] ?? 'en');

// Use currency from POST, fallback to Config
$inputCurrency = $shonupost['currency'] ?? '';
$currency = (!empty($inputCurrency) && $inputCurrency !== '0') ? $inputCurrency : ($CLIENT_CONFIG['currency'] ?? 'BDT');

// JWT Authorization
$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION'] ?? '');
$author = $bearer[1] ?? '';
$is_jwt_valid = is_jwt_valid($author);
$data_auth = json_decode($is_jwt_valid, true);

if ($data_auth['status'] !== 'Success') {
    http_response_code(401);
    echo json_encode(['code' => 4, 'msg' => 'No permission', 'msgCode' => 2, 'serviceNowTime' => $now]);
    exit;
}

// Get user
$sesquery = "SELECT id FROM shonu_subjects WHERE akshinak = '$author'";
$sesresult = $conn->query($sesquery);
$row = $sesresult->fetch_assoc();

if (!$row) {
    http_response_code(401);
    echo json_encode(['code' => 4, 'msg' => 'User not found', 'msgCode' => 2, 'serviceNowTime' => $now]);
    exit;
}

$uid = $row['id'];

// Fetch wallet balance
$rechargeQuery = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$uid' LIMIT 1";
$sesresult2 = $conn->query($rechargeQuery);
$row2 = $sesresult2->fetch_assoc();
$credit_amount = (float)($row2['motta'] ?? 0);

// Generated member_account - NO PREFIX/SUFFIX ON CLIENT SIDE
$generated_member_account = $uid;

// Generate Transaction ID (Transfer ID)
$transfer_id = uniqid('TR_');

// Payload with dynamic home_url
$data = [
    'api_key'        => $CLIENT_CONFIG['api_key'],
    // 'api_suffix'  => REMOVED,
    'member_account' => $generated_member_account,
    'game_uid'       => $gameCode,
    'credit_amount'  => $credit_amount,
    'currency_code'  => $currency,
    'home_url'       => $home_url,
    'transfer_id'    => $transfer_id,
    'language'       => $language,
    'platform'       => $CLIENT_CONFIG['platform']
];

// cURL call to Gamblly
$ch = curl_init($CLIENT_CONFIG['launch_url']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$apiResponse = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($apiResponse === false) {
    http_response_code(500);
    echo json_encode(['code' => 2, 'msg' => 'Connection error', 'serviceNowTime' => $now]);
    exit;
}

$result = json_decode($apiResponse, true);

if (isset($result['success']) && $result['success'] === true && !empty($result['game_url'])) {
    http_response_code(200);
    echo json_encode([
        'code' => 0,
        'msg' => 'Success',
        'data' => [
            'url' => $result['game_url'],
            'transfer_id' => $transfer_id,
            'returnType' => 1
        ],
        'serviceNowTime' => $now
    ]);
    writeLog('SUCCESS', 'Client launch success', ['user' => $generated_member_account]);
} else {
    // Error Response
    writeLog('FAILED', 'Provider error', ['msg' => $result['msg'] ?? 'Unknown']);
    http_response_code(200);
    echo json_encode([
        'code' => 2,
        'msg' => $result['msg'] ?? 'Unknown error',
        'serviceNowTime' => $now
    ]);
}
?>