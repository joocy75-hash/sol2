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
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['taskId']) && isset($shonupost['timestamp'])) {
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$taskId = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['taskId']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			$shonustr = '{"language":'.$language.',"random":"'.$random.'","taskId":'.$taskId.'}';
			$shonusign = strtoupper(md5($shonustr));
			if($shonusign == $signature){
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				if($data_auth['status'] === 'Success') {
					$sesquery = "SELECT akshinak
					  FROM shonu_subjects
					  WHERE akshinak = '$author'";
					$sesresult=$conn->query($sesquery);
					$sesnum = mysqli_num_rows($sesresult);
					if($sesnum == 1){
						$shonuid = $data_auth['payload']['id'];
						
						// SQL query to fetch data for taskID from 0 to 13
$sql = "SELECT taskID, taskAmount, rechargeAmount, taskPeople 
        FROM tbl_invitebonus 
        WHERE taskID BETWEEN 0 AND 13";

// Execute the query
$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    // Loop through the result and assign to individual variables for each taskID
    while ($row = $result->fetch_assoc()) {
        $taskID = $row['taskID'];
        $taskAmount = $row['taskAmount'];
        $rechargeAmount = $row['rechargeAmount'];
        $taskPeople = $row['taskPeople'];

        // Create dynamic variable names based on taskID
        ${'taskAmount' . $taskID} = $taskAmount;
        ${'rechargeAmount' . $taskID} = $rechargeAmount;
        ${'taskPeople' . $taskID} = $taskPeople;

        
    }
} else {
    echo "No data found";
}
						
						if($taskId == 1){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','1','".$taskAmount0."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount0 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 2){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','2','".$taskAmount2."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount2 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 3){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','3','".$taskAmount3."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount3 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 4){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','4','".$taskAmount4."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount4 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 5){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','5','".$taskAmount5."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount5 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 6){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','6','".$taskAmount6."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount6 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 7){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','7','".$taskAmount7."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount7 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 8){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','8','".$taskAmount8."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount8 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 9){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','9','".$taskAmount9."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount9 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 10){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','10','".$taskAmount10."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount10 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 11){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','11','".$taskAmount11."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount11 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 12){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','12','".$taskAmount12."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount12 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
						else if($taskId == 13){
							$tathya = mysqli_query($conn,"INSERT INTO `noitativni_sonub` (`arthur`,`fleck`,`motta`,`status`,`time`) VALUES ('".$shonuid."','13','".$taskAmount13."','1','".$shnunc."')");
							
							$nabikarana = "UPDATE shonu_kaichila set motta = motta + $taskAmount13 where balakedara='$shonuid'";
							$conn->query($nabikarana);
						}
								
						$res['code'] = 0;
						$res['msg'] = 'Succeed';
						$res['msgCode'] = 0;
						http_response_code(200);
						echo json_encode($res);			
					}
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