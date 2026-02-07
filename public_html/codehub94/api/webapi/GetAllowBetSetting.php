<?php

// ==== Include ====
include "../../conn.php";
include "../../functions2.php";

// ==== Headers ====
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
header('Vary: Origin');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allow_origin = '';

// ==== CORS Check ====
if ($origin) {
    $stmt = $conn->prepare("SELECT 1 FROM allowed_origins WHERE domain = ? AND status = 1");
    $stmt->bind_param("s", $origin);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $allow_origin = $origin;
    $stmt->close();
}

// ==== OPTIONS Preflight ====
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if ($allow_origin) header("Access-Control-Allow-Origin: $allow_origin");
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, ar-origin, ar-real-ip, ar-session');
    http_response_code(200);
    exit;
}

if ($allow_origin) {
    header("Access-Control-Allow-Origin: $allow_origin");
}

// ==== Time ====
date_default_timezone_set("Asia/Dhaka");
$now = date("Y-m-d H:i:s");

// ==== Default Response ====
$res = [
    'code' => 11,
    'msg' => 'Invalid request',
    'msgCode' => 12,
    'serviceNowTime' => $now
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($res);
    exit;
}

// ==== Input ====
$input = json_decode(file_get_contents("php://input"), true);
$userId = 'N/A';

// ==== Missing Params → Redirect to Sol-0203 ====
if (!$input || !isset($input['language'], $input['random'], $input['signature'], $input['timestamp'])) {
    $res = [
        'code' => 7,
        'msg' => 'Login to play games',
        'msgCode' => 6,
        'action' => 'redirect',
        'redirect_url' => 'https://codehub94.online/Sol-0203',  // ← YAHAN APNA DOMAIN DAALO
        'serviceNowTime' => $now
    ];
    echo json_encode($res);
    exit;
}

try {
    $lang = mysqli_real_escape_string($conn, $input['language']);
    $rand = mysqli_real_escape_string($conn, $input['random']);
    $sign = strtoupper(mysqli_real_escape_string($conn, $input['signature']));

    $checkStr = '{"language":' . $lang . ',"random":"' . $rand . '"}';
    $calcSign = strtoupper(md5($checkStr));

    if ($calcSign !== $sign) {
        throw new Exception("Invalid signature");
    }

    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s+([a-zA-Z0-9._-]+)$/i', $auth, $m)) {
        throw new Exception("Login required");
    }

    $token = $m[1];
    $jwt = is_jwt_valid($token);
    $data = json_decode($jwt, true);

    if (!$data || !isset($data['payload']['id'])) {
        throw new Exception("Session expired");
    }

    $userId = $data['payload']['id'];

    // Recharge Check
    $stmt = $conn->prepare("SELECT 1 FROM thevani WHERE balakedara = ? AND sthiti = 1 LIMIT 1");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $hasRecharge = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    // Settings
    $setting = $conn->query("SELECT allowbet, min_recharge FROM web_setting LIMIT 1")->fetch_assoc();
    $allowbet = !empty($setting['allowbet']);
    $min_recharge = $setting['min_recharge'] ?? 100;

    // SUCCESS
    $res = [
        'code' => 0,
        'msg' => 'Success',
        'msgCode' => 0,
        'serviceNowTime' => $now,
        'data' => [
            'canDirectToGame' => (bool)$allowbet,
            'userRechargeTimes' => $hasRecharge ? 1 : 0,
            'allowNoRechargeGame' => '0',
            'userRechargeAmount' => $hasRecharge ? 1.0 : 0.0,
            'lowestRechargeAmountToGame' => (float)$min_recharge
        ]
    ];

    echo json_encode($res, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // LOG ERROR
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    error_log("[GAME_API] $now | ERROR: " . $e->getMessage() . " | UserID: $userId | IP: $ip | UA: $ua");

    // USER KO SIRF MESSAGE + REDIRECT
    $res = [
        'code' => 7,
        'msg' => 'Login to play games',
        'msgCode' => 6,
        'action' => 'redirect',
        'redirect_url' => 'https://codehub94.online/Sol-0203',  // ← YAHAN APNA DOMAIN
        'serviceNowTime' => $now
    ];

    http_response_code(200);
    echo json_encode($res);
}
?>