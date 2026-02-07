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
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['withdrawid'])) {
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			$withdrawid = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['withdrawid']));
			$shonustr = '{"language":'.$language.',"random":"'.$random.'","withdrawid":'.$withdrawid.'}';
			$shonusign = strtoupper(md5($shonustr));
			if($shonusign == $signature){
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author); // Assuming this function validates the JWT
				$data_auth = json_decode($is_jwt_valid, 1);
				if($data_auth['status'] === 'Success') {
					$sesquery = "SELECT akshinak
					  FROM shonu_subjects
					  WHERE akshinak = '$author'";
					$sesresult=$conn->query($sesquery);
					$sesnum = mysqli_num_rows($sesresult);
					if($sesnum == 1){
						http_response_code(200);
						
						// Fetch banks from the database based on withdrawid or other criteria
						$banklist = [];
						$sql = "SELECT bankID, bankName, bankLogo, reserved FROM banks WHERE status = 'Active'";
						
						// You might want to add a condition based on $withdrawid if it maps to a 'reserved' value or other bank categories
						// For example, if withdrawid 3 means banks with reserved = '3'
						if ($withdrawid == 4) {
						    
						    echo '{
                                    "data": {
                                        "banklist": [
                                            {
                "bankID": 175,
                "bankLogo": "https://ossimg.dkwinpicture.com/dkwin",
                "bankName": "bKash",
                "reserved": "4",
                "ifscCode": ""
            },
            {
                "bankID": 178,
                "bankLogo": "https://ossimg.dkwinpicture.com/dkwin",
                "bankName": "NAGAD",
                "reserved": "4",
                "ifscCode": ""
            },
            {
                "bankID": 181,
                "bankLogo": "https://ossimg.dkwinpicture.com/dkwin",
                "bankName": "Upay",
                "reserved": "4",
                "ifscCode": ""
            },
            {
                "bankID": 182,
                "bankLogo": "https://ossimg.dkwinpicture.com/dkwin",
                "bankName": "Rocket Pay",
                "reserved": "4",
                "ifscCode": ""
            }
                                            
                                        ]
                                    },
                                    "code": 0,
                                    "msg": "Succeed",
                                    "msgCode": 0,
                                    "serviceNowTime": "2025-10-10 10:43:51"
                                }' ;
						} else {
						    echo '{
                                            "data": {
                                                "banklist": [
                                                    {
                                                        "bankID": 3,
                                                        "bankLogo": "https:\/\/ossimg.bdg123456.com\/BDGWin\/payNameIcon\/payNameIcon_20240323192848q2ac.png",
                                                        "bankName": "TRC",
                                                        "reserved": "3"
                                                    }
                                                ]
                                            },
                                            "code": 0,
                                            "msg": "Succeed",
                                            "msgCode": 0,
                                            "serviceNowTime": "2025-10-10 10:14:58"
                                        }';
						}

					
					}
					else{
						$res['code'] = 4;
						$res['msg'] = 'No operation permission';
						$res['msgCode'] = 2;
						http_response_code(401);
						echo json_encode($res);
					}					
				}
				else{					
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
					echo json_encode($res);					
				}
			}
			else{
				$res['code'] = 5;
				$res['msg'] = 'Wrong signature';
				$res['msgCode'] = 3;
				http_response_code(200);
				echo json_encode($res);				
			}
		}
		else{
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
