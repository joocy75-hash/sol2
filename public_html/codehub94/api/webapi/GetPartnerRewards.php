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
        $jwt_id = $data_auth['payload']['id'];
        
        $sesquery = "SELECT id, owncode FROM shonu_subjects WHERE akshinak = '$author'";
        $sesresult = $conn->query($sesquery);
        $sesnum = mysqli_num_rows($sesresult);
        
        if($sesnum == 1){
            $sesarr = mysqli_fetch_array($sesresult);
            $referrer_id = $sesarr['id'];
            $invitation_code = $sesarr['owncode'];

            error_log("JWT ID: $jwt_id, DB ID: $referrer_id, Invitation Code: $invitation_code");

            if ($jwt_id != $referrer_id) {
                $res['code'] = 9;
                $res['msg'] = "JWT ID ($jwt_id) does not match shonu_subjects ID ($referrer_id)";
                $res['msgCode'] = 8;
                http_response_code(400);
                echo json_encode($res);
                exit;
            }

            $check_query = "SELECT id FROM shonu_subjects WHERE id = '$referrer_id'";
            $check_result = $conn->query($check_query);
            if (mysqli_num_rows($check_result) == 0) {
                $res['code'] = 9;
                $res['msg'] = "Referrer ID $referrer_id not found in shonu_subjects";
                $res['msgCode'] = 8;
                http_response_code(400);
                echo json_encode($res);
                exit;
            }

            $inv_query = "SELECT COUNT(*) as total FROM shonu_subjects WHERE code = '$invitation_code'";
            $inv_result = $conn->query($inv_query);
            $number_of_invitations = mysqli_fetch_assoc($inv_result)['total'];

            $ref_query = "SELECT id FROM shonu_subjects WHERE code = '$invitation_code'";
            $ref_result = $conn->query($ref_query);
            
            $effective_users = []; // Track unique referred_ids that contribute rewards
            $total_reward = 0;
            $config_items = [];
            $six_days_ago = date("Y-m-d H:i:s", strtotime("-6 days"));

            $reward_config = [
                1 => [
                    ['id' => 17, 'min_recharge' => 200, 'max_recharge' => 499.99, 'bet' => 1000, 'reward' => 48],
                    ['id' => 18, 'min_recharge' => 500, 'max_recharge' => 999.99, 'bet' => 2500, 'reward' => 98],
                    ['id' => 19, 'min_recharge' => 1000, 'max_recharge' => 2499.99, 'bet' => 5000, 'reward' => 148],
                    ['id' => 20, 'min_recharge' => 2500, 'max_recharge' => 4999.99, 'bet' => 12500, 'reward' => 198],
                    ['id' => 21, 'min_recharge' => 5000, 'max_recharge' => PHP_INT_MAX, 'bet' => 25000, 'reward' => 378]
                ],
                2 => [
                    ['id' => 22, 'min_recharge' => 300, 'max_recharge' => 999.99, 'bet' => 2000, 'reward' => 48],
                    ['id' => 23, 'min_recharge' => 1000, 'max_recharge' => 2499.99, 'bet' => 10000, 'reward' => 98],
                    ['id' => 24, 'min_recharge' => 2500, 'max_recharge' => 4999.99, 'bet' => 25000, 'reward' => 148],
                    ['id' => 25, 'min_recharge' => 5000, 'max_recharge' => 9999.99, 'bet' => 50000, 'reward' => 198],
                    ['id' => 26, 'min_recharge' => 10000, 'max_recharge' => PHP_INT_MAX, 'bet' => 75000, 'reward' => 378]
                ],
                3 => [
                    ['id' => 27, 'min_recharge' => 1000, 'max_recharge' => 2499.99, 'bet' => 15000, 'reward' => 48],
                    ['id' => 28, 'min_recharge' => 2500, 'max_recharge' => 4999.99, 'bet' => 37500, 'reward' => 98],
                    ['id' => 29, 'min_recharge' => 5000, 'max_recharge' => 9999.99, 'bet' => 75000, 'reward' => 148],
                    ['id' => 30, 'min_recharge' => 10000, 'max_recharge' => 19999.99, 'bet' => 125000, 'reward' => 198],
                    ['id' => 31, 'min_recharge' => 20000, 'max_recharge' => PHP_INT_MAX, 'bet' => 225000, 'reward' => 378]
                ]
            ];

            foreach ($reward_config as $type => $levels) {
                foreach ($levels as $level) {
                    $config_items[] = [
                        'id' => $level['id'],
                        'type' => $type,
                        'rechargeAmount' => number_format($level['min_recharge'], 2, '.', ''),
                        'betAmount' => number_format($level['bet'], 2, '.', ''),
                        'rewardAmount' => number_format($level['reward'], 2, '.', ''),
                        'createTime' => "2025-03-21 13:37:52",
                        'updateTime' => "2025-03-21 13:37:52",
                        'days' => 6
                    ];
                }
            }

            $conn->begin_transaction();
            
            try {
                $conn->query("SET FOREIGN_KEY_CHECKS = 0");

                while ($ref_row = mysqli_fetch_assoc($ref_result)) {
                    $referred_id = $ref_row['id'];

                    $check_ref_query = "SELECT id FROM shonu_subjects WHERE id = '$referred_id'";
                    $check_ref_result = $conn->query($check_ref_query);
                    if (mysqli_num_rows($check_ref_result) == 0) {
                        error_log("Referred ID $referred_id not found in shonu_subjects");
                        continue;
                    }

                    $dep_query = "SELECT motta, dinankavannuracisi FROM thevani 
                                WHERE balakedara = '$referred_id' 
                                AND sthiti = '1' 
                                AND dinankavannuracisi >= '$six_days_ago'
                                ORDER BY dinankavannuracisi ASC 
                                LIMIT 3";
                    $dep_result = $conn->query($dep_query);
                    
                    $deposits = [];
                    while ($dep_row = $dep_result->fetch_assoc()) {
                        $deposits[] = [
                            'amount' => (float)$dep_row['motta'],
                            'timestamp' => $dep_row['dinankavannuracisi']
                        ];
                    }
                    error_log("Deposits for user $referred_id: " . json_encode($deposits));

                    $bet_tables = [
                        'bajikattuttate', 'bajikattuttate_drei', 'bajikattuttate_funf', 'bajikattuttate_zehn',
                        'bajikattuttate_kemuru', 'bajikattuttate_kemuru_drei', 'bajikattuttate_kemuru_funf',
                        'bajikattuttate_kemuru_zehn', 'bajikattuttate_aidudi', 'bajikattuttate_aidudi_drei',
                        'bajikattuttate_aidudi_funf', 'bajikattuttate_aidudi_zehn'
                    ];
                    
                    $total_bet = 0;
                    foreach ($bet_tables as $table) {
                        $bet_query = "SELECT SUM(ketebida) as total FROM `$table` 
                                    WHERE byabaharkarta = '$referred_id' 
                                    AND tiarikala >= '$six_days_ago'";
                        $bet_result = $conn->query($bet_query);
                        $total_bet += (float)($bet_result->fetch_assoc()['total'] ?? 0);
                    }
                    error_log("Total bet for user $referred_id: $total_bet");

                    $user_has_reward = false; // Track if this user contributes a reward

                    foreach ($deposits as $i => $deposit) {
                        $deposit_amount = $deposit['amount'];
                        $deposit_type = $i + 1;
                        $levels = $reward_config[$deposit_type] ?? [];

                        $reward_check = "SELECT id FROM partner_rewards 
                                       WHERE referrer_id = '$referrer_id' 
                                       AND referred_id = '$referred_id' 
                                       AND deposit_type = '$deposit_type' 
                                       AND created_at >= '$six_days_ago'";
                        $reward_result = $conn->query($reward_check);
                        if ($reward_result->num_rows > 0) {
                            error_log("Reward already exists for user $referred_id, deposit $deposit_type");
                            $user_has_reward = true; // Count user if they have any reward
                            continue;
                        }

                        $reward_amount = 0;
                        $reward_level = 0;
                        $required_bet = 0;
                        foreach ($levels as $level) {
                            if ($deposit_amount >= $level['min_recharge'] && $deposit_amount <= $level['max_recharge']) {
                                $required_bet = $level['bet'];
                                if ($total_bet >= $required_bet) {
                                    $reward_amount = $level['reward'];
                                    $reward_level = $level['id'];
                                    $total_reward += $reward_amount;
                                    $user_has_reward = true; // Mark user as contributing
                                }
                                break;
                            }
                        }

                        if ($reward_amount > 0) {
                            error_log("Reward for user $referred_id, deposit $deposit_type: $reward_amount (deposit: $deposit_amount, turnover: $total_bet, required: $required_bet)");
                            $insert_query = "INSERT INTO partner_rewards 
                                           (referrer_id, referred_id, deposit_type, deposit_amount, 
                                           turnover_amount, reward_amount, reward_level, status, 
                                           created_at, updated_at) 
                                           VALUES 
                                           ('$referrer_id', '$referred_id', '$deposit_type', '$deposit_amount', 
                                           '$total_bet', '$reward_amount', '$reward_level', 2, 
                                           '$shnunc', '$shnunc')";
                            $conn->query($insert_query);
                            if ($conn->error) {
                                throw new Exception("Insert failed for partner_rewards: " . $conn->error);
                            }

                            $check_kaichila = "SELECT kramasankhye, motta FROM shonu_kaichila 
                                             WHERE balakedara = '$referrer_id' 
                                             ORDER BY dinankavannuracisi DESC 
                                             LIMIT 1";
                            $kaichila_result = $conn->query($check_kaichila);
                            
                            if ($kaichila_result->num_rows > 0) {
                                $row = $kaichila_result->fetch_assoc();
                                $existing_motta = (float)$row['motta'];
                                $new_motta = $existing_motta + $reward_amount;
                                $kramasankhye = $row['kramasankhye'];
                                $kaichila_query = "UPDATE shonu_kaichila 
                                                 SET motta = '$new_motta', 
                                                     dinankavannuracisi = '$shnunc' 
                                                 WHERE kramasankhye = '$kramasankhye'";
                                $conn->query($kaichila_query);
                                if ($conn->error) {
                                    throw new Exception("Update failed for shonu_kaichila: " . $conn->error);
                                }
                                error_log("Updated shonu_kaichila for referrer $referrer_id: motta = $new_motta");
                            } else {
                                $kaichila_query = "INSERT INTO shonu_kaichila 
                                                 (balakedara, motta, rebet, spin, bonus) 
                                                 VALUES 
                                                 ('$referrer_id', '$reward_amount', 0, 0, 0)";
                                $conn->query($kaichila_query);
                                if ($conn->error) {
                                    throw new Exception("Insert failed for shonu_kaichila: " . $conn->error);
                                }
                                error_log("Inserted new shonu_kaichila for referrer $referrer_id: motta = $reward_amount");
                            }
                        } else {
                            error_log("No reward assigned for user $referred_id, deposit $deposit_type (deposit: $deposit_amount, turnover: $total_bet, required: $required_bet)");
                        }
                    }

                    if ($user_has_reward) {
                        $effective_users[$referred_id] = true; // Add to unique users
                    }
                }

                $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                $conn->commit();

                $stored_rewards_query = "SELECT SUM(reward_amount) as total FROM partner_rewards 
                                       WHERE referrer_id = '$referrer_id' 
                                       AND created_at >= '$six_days_ago'";
                $stored_rewards_result = $conn->query($stored_rewards_query);
                $total_reward = (float)(mysqli_fetch_assoc($stored_rewards_result)['total'] ?? 0);

                $data = [
                    'configAmount' => "388.00",
                    'numberOfInvitations' => $number_of_invitations,
                    'effectiveQuantity' => count($effective_users), // Number of unique users
                    'totalAmount' => number_format($total_reward, 4, '.', ''),
                    'invitationCode' => $invitation_code,
                    'items' => $config_items,
                    'days' => 6
                ];

                $res['data'] = $data;
                $res['code'] = 0;
                $res['msg'] = 'Succeed';
                $res['msgCode'] = 0;
                $res['serviceNowTime'] = $shnunc;
                http_response_code(200);
                echo json_encode($res);

            } catch (Exception $e) {
                $conn->rollback();
                $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                $res['code'] = 8;
                $res['msg'] = 'Transaction failed: ' . $e->getMessage();
                $res['msgCode'] = 7;
                http_response_code(500);
                echo json_encode($res);
            }
        } else {
            $res['code'] = 4;
            $res['msg'] = "No user found with akshinak: $author";
            $res['msgCode'] = 2;
            http_response_code(401);
            echo json_encode($res);
        }
    } else {
        $res['code'] = 4;
        $res['msg'] = 'JWT validation failed';
        $res['msgCode'] = 2;
        http_response_code(401);
        echo json_encode($res);
    }
} else {
    http_response_code(405);
    echo json_encode($res);
}
?>