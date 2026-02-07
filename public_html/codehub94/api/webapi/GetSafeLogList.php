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
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['month'])) {
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			$month = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['month']));
            $pageSize = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['pageSize']));

			$shonustr = '{"language":"'.$language.'","random":"'.$random.'"}';
			$shonusign = strtoupper(md5($shonustr));
			
			if ($signature) {
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				
				if ($data_auth['status'] === 'Success') {
					$sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
					$sesresult = $conn->query($sesquery);
					$sesnum = mysqli_num_rows($sesresult);
					
					if ($sesnum == 1) {					
						$userId = $data_auth['payload']['id'];
						$query_extend2 = "SELECT SUM(motta) as extend2 
										   FROM safe_rec 
										   WHERE user_id = '$userId' 
										   AND type = 18 
										   AND created_at LIKE '$month%'";
						$result_extend2 = mysqli_query($conn, $query_extend2);
						$extend2 = mysqli_fetch_assoc($result_extend2)['extend2'] ?: 0.00;

						
						$query_extend3 = "SELECT SUM(motta) as extend3 
										   FROM safe_rec 
										   WHERE user_id = '$userId' 
										   AND type = 19
                                           AND created_at LIKE '$month%'";
						$result_extend3 = mysqli_query($conn, $query_extend3);
						$extend3 = mysqli_fetch_assoc($result_extend3)['extend3'] ?: 0.00;

						
						$query_list = "SELECT id, user_id, motta, type, dayShareRate, orderNum, safeEarnings, earnings, created_at FROM safe_rec WHERE user_id = '$userId' AND DATE_FORMAT(created_at, '%Y-%m') = '$month' ORDER BY id DESC LIMIT $pageSize";
						$result_list = mysqli_query($conn, $query_list);
						
						$list = [];
						while ($row = mysqli_fetch_assoc($result_list)) {
							$list[] = [
								"addTime" => $row['created_at'],
								"type" => (int) $row['type'],
								"dayShareRate" => (float) $row['dayShareRate'],
								"orderNum" => $row['orderNum'],
								"safeEarnings" => $row['safeEarnings'] ? (float) $row['safeEarnings'] : null,
								"earnings" => (float) $row['earnings'],
								"amount" => (float) $row['motta']
							];
						}
						
						$res['data'] = [
							'extend1' => 0.00, 
							'extend2' => (float) $extend2,
							'extend3' => (float) $extend3,
							'list' => $list,
							'pageNo' => 1,
							'totalPage' => 1,
							'totalCount' => count($list),
						];
						$res['msg'] = 'Succeed';
						$res['msgCode'] = 0;
						$res['code'] = 0;
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
