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

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
    $author = $bearer[1];                
    $is_jwt_valid = is_jwt_valid($author);
    $data_auth = json_decode($is_jwt_valid, 1);
    
    if($data_auth['status'] === 'Success') {
        $sesquery = "SELECT akshinak, owncode FROM shonu_subjects WHERE akshinak = '$author'";
        $sesresult = $conn->query($sesquery);
        $sesnum = mysqli_num_rows($sesresult);
        
        if($sesnum == 1){
            $sesarr = mysqli_fetch_array($sesresult);
            $invitation_code = $sesarr['owncode'];
            
            // Pagination parameters
            $page_no = isset($shonupost['pageNo']) ? (int)$shonupost['pageNo'] : 1;
            $page_size = isset($shonupost['pageSize']) ? (int)$shonupost['pageSize'] : 10;
            $offset = ($page_no - 1) * $page_size;

            // Get total count of invited users
            $total_query = "SELECT COUNT(*) as total FROM shonu_subjects WHERE code = '$invitation_code'";
            $total_result = $conn->query($total_query);
            $total_count = mysqli_fetch_assoc($total_result)['total'];
            $total_pages = ceil($total_count / $page_size);

            // Get invited users with pagination
            $users_query = "SELECT id, codechorkamukala, createdate, status 
                            FROM shonu_subjects 
                            WHERE code = '$invitation_code' 
                            ORDER BY createdate DESC 
                            LIMIT $offset, $page_size";
            $users_result = $conn->query($users_query);

            $list = [];
            $seven_days_ago = date("Y-m-d H:i:s", strtotime("-7 days"));

            while ($user = mysqli_fetch_assoc($users_result)) {
                $user_id = $user['id'];
                
                // Get deposits within last 7 days
                $dep_query = "SELECT motta, dinankavannuracisi 
                            FROM thevani 
                            WHERE balakedara = '$user_id' 
                            AND sthiti = '1' 
                            AND dinankavannuracisi >= '$seven_days_ago'
                            ORDER BY dinankavannuracisi ASC";
                $dep_result = $conn->query($dep_query);
                
                $deposits = [0, 0, 0]; // First, second, third deposit amounts
                $deposit_count = 0;
                while ($dep_row = mysqli_fetch_assoc($dep_result)) {
                    if ($deposit_count < 3) {
                        $deposits[$deposit_count] = (float)$dep_row['motta'];
                    }
                    $deposit_count++;
                }

                // Calculate total turnover (bets) within 7 days
                $bet_tables = [
                    'bajikattuttate', 'bajikattuttate_drei', 'bajikattuttate_funf', 'bajikattuttate_zehn',
                    'bajikattuttate_kemuru', 'bajikattuttate_kemuru_drei', 'bajikattuttate_kemuru_funf',
                    'bajikattuttate_kemuru_zehn', 'bajikattuttate_aidudi', 'bajikattuttate_aidudi_drei',
                    'bajikattuttate_aidudi_funf', 'bajikattuttate_aidudi_zehn'
                ];
                
                $turnover = 0;
                foreach ($bet_tables as $table) {
                    $bet_query = "SELECT SUM(ketebida) as total 
                                FROM `$table` 
                                WHERE byabaharkarta = '$user_id' 
                                AND tiarikala >= '$seven_days_ago'";
                    $bet_result = $conn->query($bet_query);
                    $turnover += (float)(mysqli_fetch_assoc($bet_result)['total'] ?? 0);
                }

                // Determine status based on deposits and turnover
                $status = 0; // 0: no deposit, 1: deposited but not enough turnover, 2: completed
                $status_second = 0;
                $status_third = 0;

                $reward_config = [
                    1 => [
                        ['recharge' => 100, 'bet' => 300],
                        ['recharge' => 500, 'bet' => 1500],
                        ['recharge' => 1200, 'bet' => 3600],
                        ['recharge' => 5000, 'bet' => 15000],
                        ['recharge' => 12000, 'bet' => 36000],
                        ['recharge' => 60000, 'bet' => 180000]
                    ],
                    2 => [
                        ['recharge' => 200, 'bet' => 900],
                        ['recharge' => 500, 'bet' => 3000],
                        ['recharge' => 1200, 'bet' => 7200],
                        ['recharge' => 5000, 'bet' => 30000],
                        ['recharge' => 12000, 'bet' => 72000],
                        ['recharge' => 60000, 'bet' => 360000]
                    ],
                    3 => [
                        ['recharge' => 1000, 'bet' => 8000],
                        ['recharge' => 5000, 'bet' => 45000],
                        ['recharge' => 12000, 'bet' => 108000],
                        ['recharge' => 60000, 'bet' => 540000]
                    ]
                ];

                // Check first deposit status
                if ($deposits[0] > 0) {
                    $status = 1;
                    foreach ($reward_config[1] as $level) {
                        if ($deposits[0] >= $level['recharge'] && $turnover >= $level['bet']) {
                            $status = 2;
                            break;
                        }
                    }
                }

                // Check second deposit status
                if ($deposits[1] > 0) {
                    $status_second = 1;
                    foreach ($reward_config[2] as $level) {
                        if ($deposits[1] >= $level['recharge'] && $turnover >= $level['bet']) {
                            $status_second = 2;
                            break;
                        }
                    }
                    if ($status_second == 1) $status_second = 3; // Not completed
                }

                // Check third deposit status
                if ($deposits[2] > 0) {
                    $status_third = 1;
                    foreach ($reward_config[3] as $level) {
                        if ($deposits[2] >= $level['recharge'] && $turnover >= $level['bet']) {
                            $status_third = 2;
                            break;
                        }
                    }
                    if ($status_third == 1) $status_third = 3; // Not completed
                }

                $list[] = [
                    'nickName' => $user['codechorkamukala'],
                    'userId' => (int)$user['id'],
                    'registerTime' => date("Y-m-d H:i:s", strtotime($user['createdate'])),
                    'status' => $status,
                    'firstAmount' => number_format($deposits[0], 4, '.', ''),
                    'secondAmount' => number_format($deposits[1], 4, '.', ''),
                    'thirdAmount' => number_format($deposits[2], 4, '.', ''),
                    'turnover' => number_format($turnover, 4, '.', ''),
                    'statusSecond' => $status_second,
                    'statusThird' => $status_third
                ];
            }

            $data = [
                'list' => $list,
                'pageNo' => $page_no,
                'totalPage' => $total_pages,
                'totalCount' => $total_count
            ];

            $res['data'] = $data;
            $res['code'] = 0;
            $res['msg'] = 'Succeed';
            $res['msgCode'] = 0;
            $res['serviceNowTime'] = $shnunc;
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
    http_response_code(405);
    echo json_encode($res);
}
?>