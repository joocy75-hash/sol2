<?php
// ---------------------------------------------------------------
// 1. DB connection (make sure this file exists and works)
// ---------------------------------------------------------------
include "../../conn.php";

// ---------------------------------------------------------------
// 2. JSON + CORS headers
// ---------------------------------------------------------------
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
header('Vary: Origin');

// ---------------------------------------------------------------
// 3. CORS origin validation
// ---------------------------------------------------------------
$origin       = $_SERVER['HTTP_ORIGIN'] ?? '';
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

// ---------------------------------------------------------------
// 4. Preflight (OPTIONS) handling
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if ($allow_origin) {
        header("Access-Control-Allow-Origin: $allow_origin");
    }
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ar-origin, ar-real-ip, ar-session');
    http_response_code(200);
    exit(0);
}

// ---------------------------------------------------------------
// 5. Allow the validated origin for the real request
// ---------------------------------------------------------------
if ($allow_origin) {
    header("Access-Control-Allow-Origin: $allow_origin");
}

// ---------------------------------------------------------------
// 6. Server time (you can change the timezone if needed)
// ---------------------------------------------------------------
date_default_timezone_set('Asia/Kolkata');
$serviceNowTimeFormatted = date('Y-m-d H:i:s');

// ---------------------------------------------------------------
// 7. Build the payload (hard-coded for demo – pull from DB in prod)
// ---------------------------------------------------------------
$withdrawList = [
    [
        "withdrawID"          => 4,
        "name"                => "E-Wallet",
        "isAdd"               => 0,
        "withBeforeImgUrl"    => "https://ossimg.crhhh.com/bdtgame/payNameIcon/WithBeforeImgIcon_202506181926333lo3.png",
        "withAfterImgUrl"     => "https://ossimg.crhhh.com/bdtgame/payNameIcon/WithBeforeImgIcon2_202506181926337oqj.png",
        "recommandWithAmount" => "",
        "withdrawTip"         => ""
    ],
    [
        "withdrawID"          => 3,
        "name"                => "USDT",
        "isAdd"               => 0,
        "withBeforeImgUrl"    => "https://ossimg.crhhh.com/bdtgame/payNameIcon/WithBeforeImgIcon_20230912191710wdgg.png",
        "withAfterImgUrl"     => "https://ossimg.crhhh.com/bdtgame/payNameIcon/WithBeforeImgIcon2_2023091219171154by.png",
        "recommandWithAmount" => "",
        "withdrawTip"         => null
    ]
];

$response = [
    "data" => [
        "withdrawlist"       => $withdrawList,
        "isOpenSafeGuide"    => false,
        "safeGuideContent"   => ""
    ],
    "code" => 0,
    "msg"  => "Succeed",
    "msgCode" => 0,
    "serviceNowTime" => $serviceNowTimeFormatted
];

// ---------------------------------------------------------------
// 8. Output JSON (pretty-printed for debugging – remove in prod)
// ---------------------------------------------------------------
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
// For production you may prefer: echo json_encode($response);