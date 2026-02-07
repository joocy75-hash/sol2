<?php
	include "../../conn.php";
	mysqli_set_charset($conn, "utf8mb4");
			
	
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
	
	// Fetch data from web_setting (assuming id = 1)
$query = "SELECT telegram_active, telegram_link, web_name, web_logo, favicon_logo FROM web_setting WHERE id = 1 LIMIT 1";
$result = mysqli_query($conn, $query);

$telegram_active = 0;
$telegram_link = '';
$web_name = 'CodeHub94';
$web_logo = '';
$favicon_logo = 'default-favicon.ico';

if ($result && mysqli_num_rows($result) > 0) {
    $settingRow = mysqli_fetch_assoc($result);
    $telegram_active = $settingRow['telegram_active'];
    $telegram_link = $settingRow['telegram_link'];
    $web_name = $settingRow['web_name'];
    $web_logo = $settingRow['web_logo'];
    $favicon_logo = $settingRow['favicon_logo'];
}

$data = [];

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
			$data['isShowAppDownloadUp'] = false;
			$data['isShowAppDownloadDown'] = false;
			$data['isShowLotteryDragon'] = false;
			$data['isSplitLocalEWallet'] = true;
			$data['jackportMaxReswadAmount'] = 500;
			$data['projectName'] = $web_name;
			$data['projectLogo'] = $web_logo;
			$data['webIco'] = $favicon_logo;
			$data['headLogo'] = $web_logo;
			$data['upperOrLower'] = '0';
			
			// Language & Currency
			$languageData = [];
			$data['dollarSign'] = '₹';
			$data['defaultCurrentLanguage'] = 'bdt';

			$result = mysqli_query($conn, "SELECT * FROM langcurrency WHERE status = 1");
			if ($result) {
				while ($row = mysqli_fetch_assoc($result)) {
					if ($row && isset($row['lang_code'])) {
						$languageData[] = $row['lang_code'];
						if (isset($row['is_default']) && $row['is_default'] == 1) {
							$data['dollarSign'] = $row['currency_symbol'] ?? '₹';
							$data['defaultCurrentLanguage'] = $row['lang_code'];
						}
					}
				}
			}
			$data['languages'] = implode('|', $languageData);

			$data['registerMobile'] = '1';
			$data['registerEmail'] = '0';
			$data['registerSms'] = '0';
			$data['isOpenLoginChangeLanguage'] = '1';
			$data['rewardValidityTime'] = 30;
			$data['electronicWinRateExternalLink'] = '';
			$data['electronicWinRateImgUrl'] = 'https://Mazawin.io/sikkim';
			$data['isShowElectronicWinRateExternalLink'] = false;
			$data['isShowAppHandCodeWashingSwitch'] = true;
			$data['isShowHotGameWinOdds'] = true;
			$data['ossUrl'] = 'https://CodeHub94.online';
			$data['bigTurntableLink'] = null;
			$data['telegramExternalLink'] = ($telegram_active == 1);
			$data['isOpenActivityAward'] = true;
			$data['isOpenTurntable'] = false;
			$data['isPartnerReward'] = true;
			$data['firstDepositRewardCodeAmount'] = "1";
			$data['isOpenAdjustEvent'] = true;

			// ========================================
			// COUNTRY CODES - HARD CODED (AS REQUESTED)
			// ========================================
			$areaPhoneLenList = [];

			// 1. Bangladesh (Top)
			$areaPhoneLenList[] = ['area' => '+880', 'len' => '10'];

			// 2. India
			$areaPhoneLenList[] = ['area' => '+91', 'len' => '10'];

			// 3. UK
			$areaPhoneLenList[] = ['area' => '+44', 'len' => '9-12'];

			// 4. Pakistan
			$areaPhoneLenList[] = ['area' => '+92', 'len' => '10'];

			// 5-20: Additional Countries (Total 16)
			$areaPhoneLenList[] = ['area' => '+1',   'len' => '10'];     // USA/Canada
			$areaPhoneLenList[] = ['area' => '+61',  'len' => '9-10'];   // Australia
			$areaPhoneLenList[] = ['area' => '+60',  'len' => '9-11'];   // Malaysia
			$areaPhoneLenList[] = ['area' => '+65',  'len' => '8'];      // Singapore
			$areaPhoneLenList[] = ['area' => '+66',  'len' => '9-10'];   // Thailand
			$areaPhoneLenList[] = ['area' => '+62',  'len' => '9-13'];   // Indonesia
			$areaPhoneLenList[] = ['area' => '+977', 'len' => '10'];     // Nepal
			$areaPhoneLenList[] = ['area' => '+94',  'len' => '9'];      // Sri Lanka
			$areaPhoneLenList[] = ['area' => '+971', 'len' => '9'];      // UAE
			$areaPhoneLenList[] = ['area' => '+966', 'len' => '9'];      // Saudi Arabia
			$areaPhoneLenList[] = ['area' => '+974', 'len' => '8'];      // Qatar
			$areaPhoneLenList[] = ['area' => '+968', 'len' => '8'];      // Oman

			// Assign to data
			$data['areaPhoneLenList'] = $areaPhoneLenList;
			// ========================================

			$res['data'] = $data;
			$res['code'] = 0;
			$res['msg'] = 'Succeed';
			$res['msgCode'] = 0;
			http_response_code(200);
			echo json_encode($res, JSON_UNESCAPED_UNICODE);
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