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
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    if (
        isset($shonupost['language']) && 
        isset($shonupost['random']) && 
        isset($shonupost['signature']) && 
        isset($shonupost['timestamp'])
    ) {
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));

        $shonustr = '{"language":' . $language . ',"random":"' . $random . '"}';
        $shonusign = strtoupper(md5($shonustr));

        if ($shonusign === $signature) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1] ?? ''; // avoids undefined index
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, true);

            if ($data_auth['status'] === 'Success') {
                $userId = $data_auth['payload']['id'];
                $sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);

                if ($sesresult && mysqli_num_rows($sesresult) === 1) {
                    $vipquery = "SELECT expe, lvl FROM vip WHERE userid = $userId";
                    $vipresult = $conn->query($vipquery);
                    $viparr = mysqli_fetch_array($vipresult);

                    $vip_result = $conn->query("SELECT * FROM vip_money_management WHERE status = 1 ORDER BY vip_level ASC");

                    $data = [];
                    $i = 0;

                    while ($vip_row = mysqli_fetch_assoc($vip_result)) {
                        $level = $vip_row['vip_level'];
                        $data[$i] = [
                            'id' => (int)$vip_row['id'],
                            'vipName' => $vip_row['vip_name'],
                            'status' => (int)$vip_row['status'],
                            'currentExp' => (int)$viparr['expe'],
                            'upgrade' => (int)$vip_row['upgrade_exp'],
                            'relegationExp' => (int)$viparr['expe'],
                            'relegation' => (int)$vip_row['relegation'],
                            'deductExp' => (int)$vip_row['deduct_exp'],
                            'amount' => (int)$vip_row['amount'],
                            'upgradeStatus' => ($viparr['lvl'] >= $level ? 1 : 0),
                        ];
                        $i++;
                    }

                    $res['data'] = $data;
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    http_response_code(200);
                    echo json_encode($res);
                    exit;
                } else {
                    $res['code'] = 4;
                    $res['msg'] = 'No operation permission';
                    $res['msgCode'] = 2;
                    http_response_code(401);
                }
            } else {
                $res['code'] = 4;
                $res['msg'] = 'Invalid token';
                $res['msgCode'] = 2;
                http_response_code(401);
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(400);
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Invalid parameters';
        $res['msgCode'] = 6;
        http_response_code(400);
    }

    echo json_encode($res);
} else {
    http_response_code(405);
    echo json_encode($res);
}
?>
