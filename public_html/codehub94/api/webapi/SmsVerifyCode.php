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
		if (isset($shonupost['codeType']) && isset($shonupost['language']) && isset($shonupost['phone']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
			$codeType = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['codeType']));
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$phone = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['phone']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			$shonustr = '{"codeType":'.$codeType.',"language":'.$language.',"phone":"'.$phone.'","random":"'.$random.'"}';
			$shonusign = strtoupper(md5($shonustr));
			if($shonusign == $signature){
				if(substr($phone, 0, 2) == "91") {
					$mobile = substr($phone, 2);
				}
				else{
					$mobile = $phone;
				}
				$samasye = "SELECT id
				  FROM shonu_subjects WHERE mobile = $mobile";
				$samasyephalitansa = $conn->query($samasye);
				$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);
				if($samasyephalitansa_dhadi == 1){					
					function generateOTP(){
						$characters = '123456789';
						$charactersLength = strlen($characters);
						$randomString = '';
						for ($i = 0; $i < 6; $i++) {
							$randomString .= $characters[rand(0, $charactersLength - 1)];
						}
						return $pin=$randomString;
					}
					
					$otp=generateOTP();
					
					$createdate = date("Y-m-d H:i:s");
					$sql= mysqli_query($conn,"INSERT INTO `otp_record` (`mobile`, `otp`, `type`, `createdate`) VALUES ('".$mobile."','".$otp."','Reset PSWD','".$createdate."')");
					
					$fields = array(
						"sender_id" => "FSTS",
						"variables_values" => $otp,
                        "route" => "otp",
						"numbers" => $mobile,
					);

					$curl = curl_init();

					curl_setopt_array($curl, array(
					  CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_SSL_VERIFYHOST => 0,
					  CURLOPT_SSL_VERIFYPEER => 0,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "POST",
					  CURLOPT_POSTFIELDS => json_encode($fields),
					  CURLOPT_HTTPHEADER => array(
						"authorization: GdvuSo5pchyOMCRnSLZHG4zfBII0yduMqpGYINpWi9VEQJPhNPD8Qy0pKMny",
						"accept: */*",
						"cache-control: no-cache",
						"content-type: application/json"
					  ),
					));
					
					$response = curl_exec($curl);
					$err = curl_error($curl);
                    $apiResponse = json_decode($response, true);
					curl_close($curl);
                    
					if ($err) {
						$res['code'] = 1;
						$res['msg'] = 'SMS sending failed';
						$res['msgCode'] = 140;
						http_response_code(200);
						echo json_encode($res);
					} else {
						$res['code'] = 0;
						$res['msg'] = 'Succeed';
						$res['msgCode'] = 0;
                        $res['apiResponse'] = $apiResponse;
						http_response_code(200);
						echo json_encode($res);
					}										
				}
				else{
					$res['code'] = 1;
					$res['msg'] = 'User does not exist';
					$res['msgCode'] = 101;
					http_response_code(200);
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