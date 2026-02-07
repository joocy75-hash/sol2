<?php
header("Content-Type: application/json");

// ✅ Include DB connection
include "../../conn.php"; // Make sure this file defines $conn and sets charset to utf8
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
// ✅ Fetch latest site message
$sql = "SELECT title, siteMessage, sort, addtime FROM site_messages ORDER BY sort ASC, id DESC LIMIT 1";
$result = $conn->query($sql);

// ✅ Prepare response
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "data" => [$row],
        "code" => 0,
        "msg" => "Succeed",
        "msgCode" => 0,
        "serviceNowTime" => date("Y-m-d H:i:s")
    ]);
} else {
    echo json_encode([
        "data" => [],
        "code" => 1,
        "msg" => "No data found",
        "msgCode" => 1,
        "serviceNowTime" => date("Y-m-d H:i:s")
    ]);
}
?>
