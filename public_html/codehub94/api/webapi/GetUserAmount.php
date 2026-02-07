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

        $shonustr = '{"language":' . $language . ',"random":"' . $random . '"}';
        $shonusign = strtoupper(md5($shonustr));

        if ($shonusign == $signature) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, 1);

            if ($data_auth['status'] === 'Success') {
                $sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);

                if ($sesnum == 1) {
                    $userQuery = "SELECT id FROM shonu_subjects WHERE akshinak = '$author'";
                    $userResult = $conn->query($userQuery);

                    if ($userResult && $userResult->num_rows > 0) {
                        $userRow = $userResult->fetch_assoc();
                        $userName = $userRow['id'];

                        $balanceQuery = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$userName'";
                        $balanceResult = $conn->query($balanceQuery);

                        if ($balanceResult && $balanceResult->num_rows > 0) {
                            $withdrawApiUrl = $API_WITHDRAW_URL;
                            $memberAccount = $API_MEMBER_PREFIX . $userName . $API_MEMBER_SUFFIX;
                            $withdrawPayload = [
                                "agency_uid"    => $API_AGENCY_UID,
                                "member_account" => $memberAccount,
                                "timestamp"     => gmdate("Y-m-d\TH:i:s\Z"),
                                "credit_amount" => 0,
                                "currency_code" => $API_CURRENCY,
                                "language"      => $API_LANGUAGE,
                                "platform"      => $API_PLATFORM,
                                "home_url"      => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'],
                                "transfer_id"   => uniqid("tx_")
                            ];

                            $ch = curl_init($withdrawApiUrl);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($withdrawPayload));
                            $withdrawResponse = curl_exec($ch);
                            curl_close($ch);

                            $withdrawData = json_decode($withdrawResponse, true);
                            if (isset($withdrawData['status']) && $withdrawData['status'] === true) {
                                $withdrawAmount = (float)$withdrawData['amount'];
                                $updateBalanceQuery = "UPDATE shonu_kaichila SET motta = motta + $withdrawAmount WHERE balakedara = '$userName'";
                                $conn->query($updateBalanceQuery);

                                $res = [
                                    "data" => [
                                        "amount" => $withdrawAmount,
                                        "uRate" => 93,
                                        "uGold" => 0.25
                                    ],
                                    "code" => 0,
                                    "msg" => "Recovery of the balance is begin",
                                    "msgCode" => 0,
                                    "serviceNowTime" => date("Y-m-d H:i:s")
                                ];
                                http_response_code(200);
                                echo json_encode($res);
                                exit;
                            } else {
                                $res = [
                                    "status" => false,
                                    "message" => "Withdraw failed",
                                    "api_response" => $withdrawData
                                ];
                                http_response_code(400);
                                echo json_encode($res);
                            }
                        } else {
                            $res['code'] = 8;
                            $res['msg'] = 'Balance not found for user';
                            http_response_code(400);
                            echo json_encode($res);
                        }
                    } else {
                        $res['code'] = 4;
                        $res['msg'] = 'No operation permission';
                        http_response_code(401);
                        echo json_encode($res);
                    }
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
