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
				$data[0]['url'] = '';
				$data[0]['bannerUrl'] = 'https://ossimg.92rpay.com/92r/banner/Banner_202507042236019vuh.png';
				
				$data[1]['url'] = '';
				$data[1]['bannerUrl'] = 'https://ossimg.92rpay.com/92r/banner/Banner_20250103011000pvnx.jpg';
				
				$data[2]['url'] = '';
				$data[2]['bannerUrl'] = 'https://ossimg.92rpay.com/92r/banner/Banner_20250103010748rny5.jpg';
				
				$data[3]['url'] = '';
				$data[3]['bannerUrl'] = 'https://ossimg.92rpay.com/92r/banner/Banner_202501030113566w99.png';
				
				$data[4]['url'] = '';
				$data[4]['bannerUrl'] = 'https://ossimg.92rpay.com/92r/banner/Banner_20250103011430ft29.jpg';
				
				$data[5]['url'] = '';
				$data[5]['bannerUrl'] = 'https://ossimg.92rpay.com/92r/banner/Banner_20250103011554ssr9.jpg';
				
				$data[6]['url'] = '';
				$data[6]['bannerUrl'] = 'https://ossimg.92rpay.com/92r/banner/Banner_202501030116431qhs.jpg';
				
				$data[7]['url'] = '';
			    $data[7]['bannerUrl'] = 'https://ossimg.92rpay.com/92r/banner/Banner_20250104000926e37j.jpg';
			    
				$data[8]['url'] = '';
				$data[8]['bannerUrl'] = 'https://ossimg.92rpay.com/92r/banner/Banner_20250105020720jong.jpg';
			
				
				$res['data'] = $data;
				$res['code'] = 0;
				$res['msg'] = 'Succeed';
				$res['msgCode'] = 0;
				http_response_code(200);
				echo json_encode($res);
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