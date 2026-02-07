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
    
    date_default_timezone_set("Asia/Dhaka");
    $shnunc = date("Y-m-d H:i:s");
    
    $res = [
        'code' => 0,
        'msg' => 'Succeed',
        'msgCode' => 0,
        'serviceNowTime' => $shnunc,
        'data' => [
            'list' => [],
            'pageNo' => 1,
            'totalPage' => 0,
            'totalCount' => 0
        ]
    ];
    
    http_response_code(200);
    echo json_encode($res);
?>
