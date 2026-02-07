<?php
include "../../conn.php";
include "../../functions2.php";

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
    if ($stmt->num_rows > 0) $allow_origin = $origin;
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if ($allow_origin) header("Access-Control-Allow-Origin: $allow_origin");
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ar-origin, ar-real-ip, ar-session');
    exit(0);
}

if ($allow_origin) header("Access-Control-Allow-Origin: $allow_origin");

date_default_timezone_set("Asia/Dhaka");
$shnunc = date("Y-m-d H:i:s");

$input = file_get_contents("php://input");
$payload = json_decode($input, true);

$response = [
    "code" => 1,
    "msg" => "Invalid Request",
    "data" => []
];

if (!isset($payload["type"])) {
    $response["msg"] = "Type not specified.";
    echo json_encode($response, JSON_UNESCAPED_SLASHES);
    exit;
}

$type = intval($payload["type"]);

// JSON file mapping
$fileMap = [
    1 => './codehub94/3oks-row.json',
    2 => './codehub94/5ggaming.json',
    3 => './codehub94/btgaming.json',
    4 => './codehub94/cq9.json',
    5 => './codehub94/expanse.json',
    6 => './codehub94/fachaigaming.json',
    7 => './codehub94/fastspin.json',
    8 => './codehub94/galaxsys.json',
    9 => './codehub94/hacksaw-asia.json',
    10 => './codehub94/inout.json',
    11 => './codehub94/jdb.json',
    12 => './codehub94/jili.json',
    13 => './codehub94/mini.json',
    14 => './codehub94/pgsoft.json',
    15 => './codehub94/rg.json',
    16 => './codehub94/sabasports.json',
    17 => './codehub94/spribe.json',
    18 => './codehub94/turbogames-asia.json',
    19 => './codehub94/turbogames-world.json',
    20 => './codehub94/v8.json',
    21 => './codehub94/wonwon.json',
    22 => './codehub94/bng-asia.json',
    23 => './codehub94/pragmaticplay.json',
    37 => './codehub94/microgaming.json',
     222 => './codehub94/hot_game.json'
];

// Fallback file
$fallbackFile = "./codehub94/unavilable.json";

// STEP 1: Filename match
if (array_key_exists($type, $fileMap) && file_exists($fileMap[$type])) {
    $response["data"] = json_decode(file_get_contents($fileMap[$type]), true);
}
// STEP 2: If file missing → Use fallback pg.json
else {
    if (file_exists($fallbackFile)) {
        $response["data"] = json_decode(file_get_contents($fallbackFile), true);
    } else {
        $response["msg"] = "Neither provider file nor fallback file exists.";
        echo json_encode($response, JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// FINAL success response
$response["code"] = 0;
$response["msg"] = "Succeed";
$response["msgCode"] = 0;
$response["serviceNowTime"] = $shnunc;

echo json_encode($response, JSON_UNESCAPED_SLASHES);
