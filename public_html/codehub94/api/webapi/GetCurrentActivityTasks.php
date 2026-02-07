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
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			$shonustr = '{"language":'.$language.',"random":"'.$random.'"}';
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
						$data['totalPeople'] = 0;
						$data['totalAmount'] = 0;
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
						
						$data['taskList'][0]['taskID'] = 0;
						$data['taskList'][0]['taskAmount'] = $taskAmount0;
						$data['taskList'][0]['rechargeAmount'] = $rechargeAmount0;
						$data['taskList'][0]['rechargeAmount_All'] = null;
						$data['taskList'][0]['taskPeople'] = $taskPeople0;
						$data['taskList'][0]['rechargePeople'] = 0;
						$data['taskList'][0]['taskRechargePeople'] = 0;
						$data['taskList'][0]['efficientPeople'] = 0;
						$data['taskList'][0]['title'] = null;
						$data['taskList'][0]['title2'] = null;						
						$data['taskList'][0]['isReceive'] = 0;
						$data['taskList'][0]['isFinshed'] = false;
						$data['taskList'][0]['beginDate'] = null;
						$data['taskList'][0]['endDate'] = null;
						
						$data['taskList'][1]['taskID'] = 2;
						$data['taskList'][1]['taskAmount'] = $taskAmount2;
						$data['taskList'][1]['rechargeAmount'] = $rechargeAmount2;
						$data['taskList'][1]['rechargeAmount_All'] = null;
						$data['taskList'][1]['taskPeople'] = $taskPeople2;
						$data['taskList'][1]['rechargePeople'] = 0;
						$data['taskList'][1]['taskRechargePeople'] = 0;
						$data['taskList'][1]['efficientPeople'] = 0;
						$data['taskList'][1]['title'] = null;
						$data['taskList'][1]['title2'] = null;
						$data['taskList'][1]['isReceive'] = 0;
						$data['taskList'][1]['isFinshed'] = false;
						$data['taskList'][1]['beginDate'] = null;
						$data['taskList'][1]['endDate'] = null;
						
						$data['taskList'][2]['taskID'] = 3;
						$data['taskList'][2]['taskAmount'] = $taskAmount3;
						$data['taskList'][2]['rechargeAmount'] = $rechargeAmount3;
						$data['taskList'][2]['rechargeAmount_All'] = null;
						$data['taskList'][2]['taskPeople'] = $taskPeople3;
						$data['taskList'][2]['rechargePeople'] = 0;
						$data['taskList'][2]['taskRechargePeople'] = 0;
						$data['taskList'][2]['efficientPeople'] = 0;
						$data['taskList'][2]['title'] = null;
						$data['taskList'][2]['title2'] = null;
						$data['taskList'][2]['isReceive'] = 0;
						$data['taskList'][2]['isFinshed'] = false;
						$data['taskList'][2]['beginDate'] = null;
						$data['taskList'][2]['endDate'] = null;
						
						$data['taskList'][3]['taskID'] = 4;
						$data['taskList'][3]['taskAmount'] = $taskAmount4;
						$data['taskList'][3]['rechargeAmount'] = $rechargeAmount4;
						$data['taskList'][3]['rechargeAmount_All'] = null;
						$data['taskList'][3]['taskPeople'] = $taskPeople4;
						$data['taskList'][3]['rechargePeople'] = 0;
						$data['taskList'][3]['taskRechargePeople'] = 0;
						$data['taskList'][3]['efficientPeople'] = 0;
						$data['taskList'][3]['title'] = null;
						$data['taskList'][3]['title2'] = null;
						$data['taskList'][3]['isReceive'] = 0;
						$data['taskList'][3]['isFinshed'] = false;
						$data['taskList'][3]['beginDate'] = null;
						$data['taskList'][3]['endDate'] = null;
						
						$data['taskList'][4]['taskID'] = 5;
						$data['taskList'][4]['taskAmount'] = $taskAmount4;
						$data['taskList'][4]['rechargeAmount'] = $rechargeAmount5;
						$data['taskList'][4]['rechargeAmount_All'] = null;
						$data['taskList'][4]['taskPeople'] = $taskPeople5;
						$data['taskList'][4]['rechargePeople'] = 0;
						$data['taskList'][4]['taskRechargePeople'] = 0;
						$data['taskList'][4]['efficientPeople'] = 0;
						$data['taskList'][4]['title'] = null;
						$data['taskList'][4]['title2'] = null;
						$data['taskList'][4]['isReceive'] = 0;
						$data['taskList'][4]['isFinshed'] = false;
						$data['taskList'][4]['beginDate'] = null;
						$data['taskList'][4]['endDate'] = null;
						
						$data['taskList'][5]['taskID'] = 6;
						$data['taskList'][5]['taskAmount'] = $taskAmount6;
						$data['taskList'][5]['rechargeAmount'] = $rechargeAmount6;
						$data['taskList'][5]['rechargeAmount_All'] = null;
						$data['taskList'][5]['taskPeople'] = $taskPeople6;
						$data['taskList'][5]['rechargePeople'] = 0;
						$data['taskList'][5]['taskRechargePeople'] = 0;
						$data['taskList'][5]['efficientPeople'] = 0;
						$data['taskList'][5]['title'] = null;
						$data['taskList'][5]['title2'] = null;
						$data['taskList'][5]['isReceive'] = 0;
						$data['taskList'][5]['isFinshed'] = false;
						$data['taskList'][5]['beginDate'] = null;
						$data['taskList'][5]['endDate'] = null;
						
						$data['taskList'][6]['taskID'] = 7;
						$data['taskList'][6]['taskAmount'] = $taskAmount7;
						$data['taskList'][6]['rechargeAmount'] = $rechargeAmount7;
						$data['taskList'][6]['rechargeAmount_All'] = null;
						$data['taskList'][6]['taskPeople'] = $taskPeople7;
						$data['taskList'][6]['rechargePeople'] = 0;
						$data['taskList'][6]['taskRechargePeople'] = 0;
						$data['taskList'][6]['efficientPeople'] = 0;
						$data['taskList'][6]['title'] = null;
						$data['taskList'][6]['title2'] = null;
						$data['taskList'][6]['isReceive'] = 0;
						$data['taskList'][6]['isFinshed'] = false;
						$data['taskList'][6]['beginDate'] = null;
						$data['taskList'][6]['endDate'] = null;
						
						$data['taskList'][7]['taskID'] = 8;
						$data['taskList'][7]['taskAmount'] = $taskAmount8;
						$data['taskList'][7]['rechargeAmount'] = $rechargeAmount8;
						$data['taskList'][7]['rechargeAmount_All'] = null;
						$data['taskList'][7]['taskPeople'] = $taskPeople8;
						$data['taskList'][7]['rechargePeople'] = 0;
						$data['taskList'][7]['taskRechargePeople'] = 0;
						$data['taskList'][7]['efficientPeople'] = 0;
						$data['taskList'][7]['title'] = null;
						$data['taskList'][7]['title2'] = null;
						$data['taskList'][7]['isReceive'] = 0;
						$data['taskList'][7]['isFinshed'] = false;
						$data['taskList'][7]['beginDate'] = null;
						$data['taskList'][7]['endDate'] = null;
						
						$data['taskList'][8]['taskID'] = 9;
						$data['taskList'][8]['taskAmount'] = $taskPeople9;
						$data['taskList'][8]['rechargeAmount'] = $rechargeAmount9;
						$data['taskList'][8]['rechargeAmount_All'] = null;
						$data['taskList'][8]['taskPeople'] = $taskPeople9;
						$data['taskList'][8]['rechargePeople'] = 0;
						$data['taskList'][8]['taskRechargePeople'] = 0;
						$data['taskList'][8]['efficientPeople'] = 0;
						$data['taskList'][8]['title'] = null;
						$data['taskList'][8]['title2'] = null;
						$data['taskList'][8]['isReceive'] = 0;
						$data['taskList'][8]['isFinshed'] = false;
						$data['taskList'][8]['beginDate'] = null;
						$data['taskList'][8]['endDate'] = null;
						
						$data['taskList'][9]['taskID'] = 10;
						$data['taskList'][9]['taskAmount'] = $taskPeople10;
						$data['taskList'][9]['rechargeAmount'] = $rechargeAmount10;
						$data['taskList'][9]['rechargeAmount_All'] = null;
						$data['taskList'][9]['taskPeople'] = $taskPeople10;
						$data['taskList'][9]['rechargePeople'] = 0;
						$data['taskList'][9]['taskRechargePeople'] = 0;
						$data['taskList'][9]['efficientPeople'] = 0;
						$data['taskList'][9]['title'] = null;
						$data['taskList'][9]['title2'] = null;
						$data['taskList'][9]['isReceive'] = 0;
						$data['taskList'][9]['isFinshed'] = false;
						$data['taskList'][9]['beginDate'] = null;
						$data['taskList'][9]['endDate'] = null;
						
						$data['taskList'][10]['taskID'] = 11;
						$data['taskList'][10]['taskAmount'] = $taskPeople11;
						$data['taskList'][10]['rechargeAmount'] = $rechargeAmount11;
						$data['taskList'][10]['rechargeAmount_All'] = null;
						$data['taskList'][10]['taskPeople'] = $taskPeople11;
						$data['taskList'][10]['rechargePeople'] = 0;
						$data['taskList'][10]['taskRechargePeople'] = 0;
						$data['taskList'][10]['efficientPeople'] = 0;
						$data['taskList'][10]['title'] = null;
						$data['taskList'][10]['title2'] = null;
						$data['taskList'][10]['isReceive'] = 0;
						$data['taskList'][10]['isFinshed'] = false;
						$data['taskList'][10]['beginDate'] = null;
						$data['taskList'][10]['endDate'] = null;
						
						$data['taskList'][11]['taskID'] = 12;
						$data['taskList'][11]['taskAmount'] = $taskPeople12;
						$data['taskList'][11]['rechargeAmount'] = $rechargeAmount12;
						$data['taskList'][11]['rechargeAmount_All'] = null;
						$data['taskList'][11]['taskPeople'] = $taskPeople12;
						$data['taskList'][11]['rechargePeople'] = 0;
						$data['taskList'][11]['taskRechargePeople'] = 0;
						$data['taskList'][11]['efficientPeople'] = 0;
						$data['taskList'][11]['title'] = null;
						$data['taskList'][11]['title2'] = null;
						$data['taskList'][11]['isReceive'] = 0;
						$data['taskList'][11]['isFinshed'] = false;
						$data['taskList'][11]['beginDate'] = null;
						$data['taskList'][11]['endDate'] = null;
						
						$data['taskList'][12]['taskID'] = 13;
						$data['taskList'][12]['taskAmount'] = $taskPeople13;
						$data['taskList'][12]['rechargeAmount'] = $rechargeAmount13;
						$data['taskList'][12]['rechargeAmount_All'] = null;
						$data['taskList'][12]['taskPeople'] = $taskPeople13;
						$data['taskList'][12]['rechargePeople'] = 0;
						$data['taskList'][12]['taskRechargePeople'] = 0;
						$data['taskList'][12]['efficientPeople'] = 0;
						$data['taskList'][12]['title'] = null;
						$data['taskList'][12]['title2'] = null;
						$data['taskList'][12]['isReceive'] = 0;
						$data['taskList'][12]['isFinshed'] = false;
						$data['taskList'][12]['beginDate'] = null;
						$data['taskList'][12]['endDate'] = null;
						
						
						$data['chirldrenListDataList'] = null;
						
						$res['data'] = $data;
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