<?php
include "../../conn.php";

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
header('Vary: Origin');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allow_origin = '';

// CORS Whitelist Check
if ($origin) {
    $stmt = $conn->prepare("SELECT domain FROM allowed_origins WHERE domain = ? AND status = 1");
    $stmt->bind_param("s", $origin);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $allow_origin = $origin;
    }
    $stmt->close();
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if ($allow_origin) {
        header("Access-Control-Allow-Origin: $allow_origin");
    }
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ar-origin, ar-real-ip, ar-session');
    http_response_code(200);
    exit(0);
}

if ($allow_origin) {
    header("Access-Control-Allow-Origin: $allow_origin");
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');
$serviceNowTimeFormatted = date('Y-m-d H:i:s');

// Main Query: Fetch only active games, grouped by vendor
$query = "
    SELECT 
        vendor_code,
        vendor_name,
        sort_order,
        game_id,
        game_name_en,
        game_image,
        vendor_id,
        custom_game_type
    FROM game_slot 
    WHERE status = 1 
    ORDER BY sort_order DESC, vendor_code, game_name_en
";

$result = $conn->query($query);

if (!$result) {
    echo json_encode([
        "code" => -1,
        "msg" => "Database query failed",
        "serviceNowTime" => $serviceNowTimeFormatted
    ], JSON_PRETTY_PRINT);
    exit;
}

// Group games by vendor_code
$vendors = [];
$seenVendors = [];

while ($row = $result->fetch_assoc()) {
    $vCode = $row['vendor_code'];

    if (!isset($seenVendors[$vCode])) {
        $seenVendors[$vCode] = true;
        $vendors[$vCode] = [
            "vendorCode" => $vCode,
            "sort" => (int)$row['sort_order'],
            "childList" => []
        ];
    }

    // Clean game object
    $game = [
        "gameID" => $row['game_id'],
        "gameNameEn" => $row['game_name_en'],
        "img" => $row['game_image'] ?? '',
        "vendorId" => $row['vendor_id'] !== null ? (int)$row['vendor_id'] : null,
        "vendorCode" => $vCode,
        "imgUrl2" => null,
        "customGameType" => (int)$row['custom_game_type']
    ];

    // Special handling for CQ9 & 5G (they don't have vendor_id in same format sometimes)
    if (in_array($vCode, ['CQ9', '5G'])) {
        unset($game['vendorId']);
        unset($game['vendorCode']); // optional based on your frontend
    }

    $vendors[$vCode]['childList'][] = $game;
}

// Convert to indexed array and sort by sort_order DESC
$dataArray = array_values($vendors);
usort($dataArray, function($a, $b) {
    return $b['sort'] <=> $a['sort']; // Higher sort first
});

// Final response
$response = [
    "data" => $dataArray,
    "code" => 0,
    "msg" => "Succeed",
    "msgCode" => 0,
    "serviceNowTime" => $serviceNowTimeFormatted
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>