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
		if (isset($shonupost['giftCode']) && isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
			$giftCode = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['giftCode']));	
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));		
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));			
			$shonustr = '{"giftCode":"'.$giftCode.'","language":'.$language.',"random":"'.$random.'"}';	
			$shonusign = strtoupper(md5($shonustr));

			if($shonusign == $signature){
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);

				if($data_auth['status'] === 'Success') {
					$sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
					$sesresult = $conn->query($sesquery);
					$sesnum = mysqli_num_rows($sesresult);

					if($sesnum == 1){
						$shonuid = $data_auth['payload']['id'];

						$checkcode = mysqli_query($conn, "SELECT `identite`, `utilisateurmax`, `prix`, `remark`, `nombredutilisateurs`, `recharge_required` FROM `hodike_nirvahaka` WHERE `enserie`='".$giftCode."' AND `shonu`='1'");
						$checkcoderow = mysqli_num_rows($checkcode);
						$checkuser = mysqli_query($conn, "SELECT `kani` FROM `hodike_balakedara` WHERE `serial`='".$giftCode."' AND `userkani`='".$shonuid."'");
						$checkuserrow = mysqli_num_rows($checkuser);

						if($checkcoderow > 0){
							$checkcodearray = mysqli_fetch_array($checkcode);
							$utilisateurmax = $checkcodearray['utilisateurmax'];
							$nombredutilisateurs = $checkcodearray['nombredutilisateurs'];
							$prix = $checkcodearray['prix'];
							$remark = $checkcodearray['remark'];
							$recharge_required = isset($checkcodearray['recharge_required']) ? (float)$checkcodearray['recharge_required'] : 0;

							// Recharge check if required
							if ($recharge_required > 0) {
								$recharge = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(motta) as total FROM thevani WHERE balakedara = '$shonuid' AND sthiti = 1"));
								$totalRecharge = (float)($recharge['total'] ?? 0);

								if ($totalRecharge < $recharge_required) {
									$res['data'] = null;
									$res['code'] = 1;
									$res['msg'] = "Recharge ₹" . number_format($recharge_required, 2) . " required. Your recharge: ₹" . number_format($totalRecharge, 2);
									$res['msgCode'] = 231;
									http_response_code(200);
									echo json_encode($res);
									exit;
								}
							}

							if($nombredutilisateurs < $utilisateurmax){
								if($checkuserrow == 0){
									$nombredutilisateurs += 1;
									mysqli_query($conn, "UPDATE `hodike_nirvahaka` SET `nombredutilisateurs` = '".$nombredutilisateurs."' WHERE `enserie` = '".$giftCode."'");
									$crdt = date("Y-m-d H:i:s");

									mysqli_query($conn, "INSERT INTO `hodike_balakedara` (`userkani`, `serial`, `price`, `remark`, `shonu`) VALUES ('".$shonuid."','".$giftCode."','".$prix."','".$remark."','".$crdt."')");
									mysqli_query($conn, "UPDATE shonu_kaichila SET motta = ROUND(motta + '".$prix."', 2) WHERE balakedara = '".$shonuid."'");

									$res['data'] = null;
									$res['code'] = 0;
									$res['msg'] = 'Succeed';
									$res['msgCode'] = 0;
									http_response_code(200);
									echo json_encode($res);
								}
								else{
									$res['data'] = null;
									$res['code'] = 1;
									$res['msg'] = 'Already claimed';
									$res['msgCode'] = 230;
									http_response_code(200);
									echo json_encode($res);
								}
							}
							else{
								$res['data'] = null;
								$res['code'] = 1;
								$res['msg'] = 'Code usage limit reached';
								$res['msgCode'] = 230;
								http_response_code(200);
								echo json_encode($res);
							}
						}
						else{
							$res['data'] = null;
							$res['code'] = 1;
							$res['msg'] = 'Invalid gift code';
							$res['msgCode'] = 230;
							http_response_code(200);
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
