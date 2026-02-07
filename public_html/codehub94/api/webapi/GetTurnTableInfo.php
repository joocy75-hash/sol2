<?php
    include "../../conn.php";
    
    
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
            if($shonusign == $signature){
                $data = [
    "rewardList" => [],
    "taskList" => [],
    "vipRating" => "0,1,2,3,4,5,6,7,8,9,10",
    "memberGroup" => "0,1",
    "bindingType" => 0
];

// 1️⃣ rewardList: get all rows from table
$rewardQuery = "SELECT reward_type, reward_setting, prize_picture_url FROM recharge_spin_rewards ORDER BY id ASC";
$rewardResult = $conn->query($rewardQuery);
if ($rewardResult && $rewardResult->num_rows > 0) {
    while ($row = $rewardResult->fetch_assoc()) {
        $data['rewardList'][] = [
            "rewardType" => (int)$row['reward_type'],
            "rewardSetting" => $row['reward_setting'],
            "prizePicturesUrl" => $row['prize_picture_url']
        ];
    }
}

// 2️⃣ taskList: get unique spin mapping
$taskQuery = "SELECT DISTINCT target_amount, rotate_num FROM recharge_spin_rewards ORDER BY target_amount ASC";
$taskResult = $conn->query($taskQuery);
if ($taskResult && $taskResult->num_rows > 0) {
    while ($row = $taskResult->fetch_assoc()) {
        $data['taskList'][] = [
            "taskType" => 1,
            "targetAmount" => (float)$row['target_amount'],
            "rotateNum" => (int)$row['rotate_num']
        ];
    }
}
                
                $res['data'] = $data;
                $res['code'] = 0;
                $res['msg'] = 'Succeed';
                $res['msgCode'] = 0;
                http_response_code(200);
                echo json_encode($res);
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
