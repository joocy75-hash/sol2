<?php 
	include "../../conn.php";
	include "../../functions2.php";
	include "../../chub94_apiconfig.php"; 
	
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
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
        $shonustr = '{"language":'.$language.',"random":"'.$random.'"}';
        $shonusign = strtoupper(md5($shonustr));
        if ($shonusign == $signature) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, 1);
            if ($data_auth['status'] === 'Success') {
                $sesquery = "SELECT akshinak
                    FROM shonu_subjects
                    WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);
                if ($sesnum == 1) {
                    $balquery = "SELECT motta
                        FROM shonu_kaichila
                        WHERE balakedara = ".$data_auth['payload']['id'];
                    $balresult = $conn->query($balquery);
                    $balarr = mysqli_fetch_array($balresult);
                    $data['thidGameBalanceList'][0]['vendorCode'] = 'Lottery';
                    $data['thidGameBalanceList'][0]['balance'] = (int)$balarr['motta'];
                    $apiUrl = $API_BALANCE_URL;
                    $memberAccount = $API_MEMBER_PREFIX . $data_auth['payload']['id'] . $API_MEMBER_SUFFIX;
                    $payload = [
                        "agency_uid" => $API_AGENCY_UID,
                        "member_account" => $memberAccount,
                        "timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
                        "credit_amount" => 0,
                        "currency_code" => $API_CURRENCY,
                        "language" => $API_LANGUAGE,
                        "platform" => $API_PLATFORM,
                        "home_url" => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'],
                        "transfer_id" => uniqid("tx_")
                    ];
                    $ch = curl_init($apiUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                    $apiResponse = curl_exec($ch);
                    curl_close($ch);
                    $apiData = json_decode($apiResponse, true);
                    $huiduBal = 0;
                    if (isset($apiData['code']) && $apiData['code'] == 0 && isset($apiData['payload']['after_amount'])) {
                        $huiduBal = (float)$apiData['payload']['after_amount'];
                    }
                    $data['thidGameBalanceList'][1]['vendorCode'] = 'Games Wallet';
                    $data['thidGameBalanceList'][1]['balance'] = $huiduBal;
                    $data['totalWithdraw'] = 0;
                    $data['totalRecharge'] = 0;
                    $res['data'] = $data;
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    http_response_code(200);
                    echo json_encode($res);
                } else {
                    $res['code'] = 4;
                    $res['msg'] = 'No operation permission';
                    $res['msgCode'] = 2;
                    http_response_code(401);
                    echo json_encode($res);
                }
            } else {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
            }
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