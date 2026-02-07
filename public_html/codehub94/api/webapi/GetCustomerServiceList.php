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

$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch from DB
    $query = "SELECT name, url, sort_order AS sort FROM support_links WHERE is_active = 1 ORDER BY sort_order ASC, id DESC";
    $result = mysqli_query($conn, $query);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'typeID' => 3,
            'name'   => $row['name'],
            'url'    => $row['url'],
            'sort'   => (int)$row['sort']
        ];
    }

    if (!empty($data)) {
        $res['data'] = $data;
        $res['code'] = 0;
        $res['msg'] = 'Succeed';
        $res['msgCode'] = 0;
    } else {
        // Fallback if no active link
        $data[] = [
            'typeID' => 3,
            'name'   => 'Telegram Support',
            'url'    => 'https://t.me/CodeHub94',
            'sort'   => 2
        ];
        $res['data'] = $data;
        $res['code'] = 0;
        $res['msg'] = 'Succeed (fallback)';
    }
    http_response_code(200);
    echo json_encode($res, JSON_UNESCAPED_SLASHES);
    exit;
}

// POST validation (same as before)
$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['typeId']) && isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
        $typeId = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['typeId']));
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
        
        $shonustr = '{"language":'.$language.',"random":"'.$random.'","typeId":'.$typeId.'}';
        $shonusign = strtoupper(md5($shonustr));
        
        $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION'] ?? '');
        $author = $bearer[1] ?? '';

        if ($shonusign == $signature) {
            // Same DB logic as GET
            $query = "SELECT name, url, sort_order AS sort FROM support_links WHERE is_active = 1 ORDER BY sort_order ASC";
            $result = mysqli_query($conn, $query);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    'typeID' => 3,
                    'name'   => $row['name'],
                    'url'    => $row['url'],
                    'sort'   => (int)$row['sort']
                ];
            }
            if (empty($data)) {
                $data[] = ['typeID'=>3, 'name'=>'Telegram Support', 'url'=>'https://t.me/CodeHub94', 'sort'=>2];
            }
            $res['data'] = $data;
            $res['code'] = 0;
            $res['msg'] = 'Succeed';
            $res['msgCode'] = 0;
            http_response_code(200);
            echo json_encode($res, JSON_UNESCAPED_SLASHES);
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);
    }
} else {
    http_response_code(405);
    echo json_encode($res);
}
?>