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

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    $shonubody = file_get_contents("php://input");
    $shonupost = json_decode($shonubody, true);

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

        if ($shonusign == $signature) {
$data['agreement'] = "
<h4 style=\"color: #ffffff;\"><b>User Agreement</b></h4>
<p><br></p>
<p style=\"color: #ffffff;\">1. To avoid betting disputes, members must read the company's rules before entering the app. Once the player “I agree” By entering this company to bet, you will be considered to be in agreement with the company's User Agreement.</p>
<p><br></p>
<p style=\"color: #ffffff;\">2. It is the member's responsibility to ensure the confidentiality of their account and login information. Any online bets placed using your account number and member password will be considered valid. Please change your password from time to time. The company is not responsible for any compensation for bets made with a stolen account and password.</p>
<p><br></p>
<p style=\"color: #ffffff;\">3. The company reserves the right to change this agreement or the game rules or confidentiality rules from time to time. The modified terms will take effect on the date specified after the change occurs, and the right to make final decisions on all disputes is reserved by the company.</p>
<p><br></p>
<p style=\"color: #ffffff;\">4. Users must be of legal age according to the laws of the country of residence to use an online casino or application. Online bets that have not been successfully submitted will be considered void.</p>
<p><br></p>
<p style=\"color: #ffffff;\">5. When a player is automatically or forcibly disconnected from the game before the game result is announced, it will not affect the game result.</p>";


            $res['data'] = $data;
            $res['code'] = 0;
            $res['msg'] = 'Succeed';
            $res['msgCode'] = 0;
            http_response_code(200);
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
    }
} else {
    http_response_code(405);
}

echo json_encode($res);
?>
