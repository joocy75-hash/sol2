<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


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
		if (isset($shonupost['date']) && isset($shonupost['language']) && isset($shonupost['pageNo']) && isset($shonupost['pageSize']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['type'])) {
			$date = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['date']));
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$pageNo = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['pageNo']));
			$pageSize = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['pageSize']));			
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			//$startDate = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['startDate']));
			$type = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['type']));
			if($date == ''){
				$shonustr = '{"language":'.$language.',"pageNo":'.$pageNo.',"pageSize":'.$pageSize.',"random":"'.$random.'","type":'.$type.'}';	
			}
			else{
				$shonustr = '{"date":"'.$date.'","language":'.$language.',"pageNo":'.$pageNo.',"pageSize":'.$pageSize.',"random":"'.$random.'","type":'.$type.'}';	
			}						
			$shonusign = strtoupper(md5($shonustr));
			if($shonusign == $signature){
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				if($data_auth['status'] === 'Success') {
					$sesquery = "SELECT akshinak  
      FROM shonu_subjects  
      WHERE akshinak = '$author'
";
					$sesresult=$conn->query($sesquery);
					$sesnum = mysqli_num_rows($sesresult);
					if($sesnum == 1){
						$samatolana = ($pageNo - 1) * 10;
						$shonuid = $data_auth['payload']['id'];
						
						if($date == ''){
							if($type == -1){
								$samasye = "SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid 
                                  UNION ALL
								  SELECT kramasankhye as parichaya, bonus as ketebida, 'sb' as phalaphala, bonus as sesabida, dinankavannuracisi as tiarikala 
								  FROM shonu_kaichila WHERE balakedara = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_drei WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_trx WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_trx3 WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_trx5 WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_trx10 WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_funf WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_drei WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_funf WHERE byabaharkarta =$shonuid 
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta =$shonuid 
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_zehn WHERE byabaharkarta = $shonuid

								  UNION ALL
								  SELECT macau as parichaya, salary as ketebida, 'ds' as phalaphala, salary as sesabida, createdate as tiarikala 
								  FROM dailysalary WHERE userid = $shonuid
								  UNION ALL
								  SELECT shonu as parichaya, motta as ketebida, 'rc' as phalaphala, motta as sesabida, dinankavannuracisi as tiarikala 
								  FROM thevani WHERE balakedara = $shonuid AND sthiti = 1
								  UNION ALL
								  SELECT id as parichaya, sturgis as ketebida, 'frc' as phalaphala, sturgis as sesabida, time as tiarikala 
								  FROM egrahcer_sonub WHERE dr = $shonuid AND status = 1
								  UNION ALL
								  SELECT id as parichaya, prize as ketebida, 'rb' as phalaphala, prize as sesabida, time as tiarikala 
								  FROM spinrec WHERE user_id = $shonuid
								  UNION ALL
								  SELECT dearlord as parichaya, todayblessings as ketebida, 'atb' as phalaphala, todayblessings as sesabida, amen as tiarikala 
								  FROM cihne WHERE identity = $shonuid
								  UNION ALL
								  SELECT shonu as parichaya, motta as ketebida, 'wd' as phalaphala, remarks as sesabida, dinankavannuracisi as tiarikala 
								  FROM hintegedukolli WHERE balakedara = $shonuid 
								  UNION ALL
								  SELECT id as parichaya, motta as ketebida, 'orb' as phalaphala, motta as sesabida, created_at as tiarikala 
								  FROM rebetrec WHERE user_id = $shonuid
								  UNION ALL
								  SELECT id as parichaya, motta as ketebida, 'lvlup' as phalaphala, type as sesabida, created_at as tiarikala 
								  FROM viprec WHERE user_id = $shonuid
								  UNION ALL
								  SELECT id as parichaya, rebateAmount_Last as ketebida, 'cmd' as phalaphala, rebateAmount_Last as sesabida, created_timestamp as tiarikala 
								  FROM commission WHERE user_id = $shonuid
								  UNION ALL
								  SELECT id as parichaya, motta as ketebida, 'reftask' as phalaphala, motta as sesabida, time as tiarikala 
								  FROM noitativni_sonub WHERE arthur = $shonuid AND status = 1 
								  UNION ALL
								  SELECT kani as parichaya, price as ketebida, 're' as phalaphala, remark as sesabida, shonu as tiarikala 
								  FROM hodike_balakedara WHERE userkani = $shonuid 
								  ORDER BY tiarikala DESC LIMIT 
$pageSize OFFSET $samatolana";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT parichaya
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT kramasankhye as parichaya
								  FROM shonu_kaichila WHERE balakedara = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_drei WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_trx WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_trx3 WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_trx5 WHERE byabaharkarta = $shonuid
                                  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_trx10 WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_funf WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_drei WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_funf WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_zehn WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT macau as parichaya
								  FROM dailysalary WHERE userid = $shonuid
								  UNION ALL
								  SELECT shonu as parichaya
								  FROM thevani WHERE balakedara = $shonuid AND sthiti = 1
								  UNION ALL
								  SELECT shonu as parichaya
								  FROM hintegedukolli WHERE balakedara = $shonuid
								  UNION ALL
								  SELECT id as parichaya
								  FROM noitativni_sonub WHERE arthur = $shonuid AND status = 1
								  UNION ALL
								  SELECT id as parichaya
								  FROM rebetrec WHERE user_id = $shonuid
								  UNION ALL
								  SELECT dearlord as parichaya
								  FROM cihne WHERE identity = $shonuid
								  UNION ALL
								  SELECT id as parichaya
								  FROM spinrec WHERE user_id = $shonuid
								  UNION ALL
								  SELECT id as parichaya
								  FROM viprec WHERE user_id = $shonuid
								  UNION ALL
								  SELECT id as parichaya
								  FROM egrahcer_sonub WHERE dr = $shonuid
								  UNION ALL
								  SELECT id as parichaya
								  FROM rebetrec WHERE user_id = $shonuid
								  UNION ALL
								  SELECT kani as parichaya
								  FROM hodike_balakedara WHERE userkani = $shonuid";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
							else if($type == 0){
								$samasye = "SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_drei WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_funf WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_zehn WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_aidudi WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_aidudi_drei WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_aidudi_funf WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_aidudi_zehn WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_kemuru WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_kemuru_drei WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_kemuru_funf WHERE byabaharkarta = $shonuid
UNION ALL
SELECT parichhaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutatte_kemuru_zehn WHERE byabaharkarta = $shonuid
ORDER BY tiarikala DESC LIMIT $pageSize OFFSET $samatolana
";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT parichaya
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_drei WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_funf WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_drei WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_funf WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_zehn WHERE byabaharkarta = $shonuid";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
							else if($type == 1){
								$samasye = "SELECT macau, salary, createdate  
FROM dailysalary  
WHERE userid = $shonuid  
ORDER BY macau DESC  
LIMIT $pageSize OFFSET $samatolana
";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT macau  
FROM dailysalary  
WHERE userid = $shonuid";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
							else if($type == 4){
								$samasye = "SELECT shonu, motta, dinankavannuracisi  
FROM thevani  
WHERE balakedara = $shonuid  
AND sthiti = 1  
ORDER BY dinankavannuracisi DESC  
LIMIT $pageSize  
OFFSET $samatolana
";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT shonu, motta, dinankavannuracisi  
FROM thevani  
WHERE balakedara = $shonuid  
AND sthiti = 1
";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
					     	 else if ($type == 119) {
                               $samasye = "SELECT id, prize, time
                                      FROM spinrec
                                      WHERE user_id = $shonuid
                                      ORDER BY time DESC LIMIT $pageSize OFFSET $samatolana";
                               $samasyephalitansa = $conn->query($samasye);
    
                               $samasye_ondu = "SELECT id, prize, time
                                         FROM spinrec
                                        WHERE user_id = $shonuid";
                               $samasyephalitansa_ondu = $conn->query($samasye_ondu);
                               $samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
					     	    
					     	} else if ($type == 12) {
								$samasye = "SELECT kramasankhye, bonus, dinankavannuracisi
									   FROM shonu_kaichila
									   WHERE balakedara = $shonuid
									   ORDER BY time DESC LIMIT $pageSize OFFSET $samatolana";
								$samasyephalitansa = $conn->query($samasye);
	 
								$samasye_ondu = "SELECT kramasankhye, bonus, dinankavannuracisi
										  FROM shonu_kaichila
										 WHERE balakedara = $shonuid";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
								  
							  }

							else if($type == 5) {
                                $samasye = "SELECT shonu, motta, dinankavannuracisi, remarks
                                            FROM hintegedukolli 
                                            WHERE balakedara = $shonuid
                                            ORDER BY dinankavannuracisi DESC 
                                           LIMIT $pageSize OFFSET $samatolana";
                                $samasyephalitansa = $conn->query($samasye);
                            
                                $samasye_ondu = "SELECT shonu, motta, dinankavannuracisi, remarks
                                                 FROM hintegedukolli 
                                                 WHERE balakedara = $shonuid";
                                $samasyephalitansa_ondu = $conn->query($samasye_ondu);
                                $samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
                            }

							else if($type == 2){
								$samasye = "SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  ORDER BY tiarikala DESC LIMIT $pageSize OFFSET $samatolana";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT parichaya
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner'";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
							else if($type == 3){
								$samasye = "SELECT kani, price, shonu  
FROM hodike_balakedara  
WHERE userkani = $shonuid  
ORDER BY shonu DESC  
LIMIT $pageSize OFFSET $samatolana;
";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT kani  
FROM hodike_balakedara  
WHERE userkani = $shonuid;
";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
							else if($type == 14){
								$samasye = "SELECT id, sturgis, time  
FROM egrahcer_sonub  
WHERE dr = $shonuid  
ORDER BY time DESC  
LIMIT $pageSize OFFSET $samatolana;
";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT id  
FROM egrahcer_sonub  
WHERE dr = $shonuid;
";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
						}
						else{
							if($type == -1){
								$samasye = "SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutate_drei  
WHERE byabahakarta = $shonuid  
AND date(tiarikala) = date;
('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutate_funf  
WHERE byabahakarta = $shonuid  
AND date(tiarikala) = date;
('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
                                  UNION ALL
								  SELECT kramasankhye as parichaya, bonus as ketebida, 'sb' as phalaphala, bonus as sesabida, dinankavannuracisi as tiarikala 
								  FROM shonu_kaichila WHERE balakedara = $shonuid AND date(dinankavannuracisi) = date('".$date."')
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_trx WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_trx3 WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_trx5 WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
                                  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_trx10 WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutate_aidudi_drei  
WHERE byabahakarta = $shonuid  
AND date(tiarikala) = date;
('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutate_aidudi_funj  
WHERE byabahakarta = $shonuid  
AND date(tiarikala) = date;
('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."') 
								  UNION ALL
								  SELECT macau as parichaya, salary as ketebida, 'ds' as phalaphala, salary as sesabida, createdate as tiarikala 
								  FROM dailysalary WHERE userid = $shonuid AND date(createdate) = date('".$date."') 
								  UNION ALL
								  SELECT shonu as parichaya, motta as ketebida, 'rc' as phalaphala, motta as sesabida, dinankavannuracisi as tiarikala 
								  FROM thevani WHERE balakedara = $shonuid AND sthiti = 1 AND date(dinankavannuracisi) = date('".$date."') 
								  UNION ALL
								  SELECT id as parichaya, prize as ketebida, 'rb' as phalaphala, prize as sesabida, time as tiarikala 
								  FROM spinrec WHERE user_id = $shonuid AND date(time) = date('".$date."') 
								  UNION ALL
								  SELECT shonu as parichaya, motta as ketebida, 'wd' as phalaphala, remarks as sesabida, dinankavannuracisi as tiarikala 
								  FROM hintegedukolli WHERE balakedara = $shonuid AND date(dinankavannuracisi) = date('".$date."')
								  UNION ALL
								  SELECT dearlord as parichaya, todayblessings as ketebida, 'atb' as phalaphala, todayblessings as sesabida, amen as tiarikala 
								  FROM cihne WHERE identity = $shonuid  AND date(amen) = date('".$date."')
								  UNION ALL
								  SELECT id as parichaya, motta as ketebida, 'orb' as phalaphala, motta as sesabida, created_at as tiarikala 
								  FROM rebetrec WHERE user_id = $shonuid AND date(created_at) = date('".$date."')
								  UNION ALL
								  SELECT id as parichaya, motta as ketebida, 'lvlup' as phalaphala, type as sesabida, created_at as tiarikala 
								  FROM viprec WHERE user_id = $shonuid AND date(created_at) = date('".$date."')
								  UNION ALL
								  SELECT id as parichaya, sturgis as ketebida, 'frc' as phalaphala, status as sesabida, time as tiarikala 
								  FROM egrahcer_sonub WHERE dr = $shonuid AND date(time) = date('".$date."')
								  UNION ALL
								  SELECT id as parichaya, rebateAmount_Last as ketebida, 'cmd' as phalaphala, rebateAmount_Last as sesabida, created_timestamp as tiarikala 
								  FROM commission WHERE user_id = $shonuid AND date(created_timestamp) = date('".$date."')
								  UNION ALL
								  SELECT id as parichaya, motta as ketebida, 'reftask' as phalaphala, motta as sesabida, time as tiarikala 
								  FROM noitativni_sonub WHERE arthur = $shonuid AND date(time) = date('".$date."')
								  UNION ALL
								  SELECT kani as parichaya, price as ketebida, 're' as phalaphala, remark as sesabida, shonu as tiarikala 
								  FROM hodike_balakedara WHERE userkani = $shonuid AND date(shonu) = date('".$date."') 
								  ORDER BY tiarikala DESC LIMIT $pageSize OFFSET $samatolana";							
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT parichaya
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_drei WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
                                  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_trx WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
                                  UNION ALL
								  SELECT kramasankhye as parichaya
								  FROM shonu_kaichila WHERE balakedara = $shonuid AND date(dinankavannuracisi) = date('".$date."')
                                  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_trx3 WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
                                  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_trx5 WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
                                  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_trx10 WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_funf WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_drei WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_funf WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT macau as parichaya
								  FROM dailysalary WHERE userid = $shonuid AND date(createdate) = date('".$date."')
								  UNION ALL
								  SELECT shonu as parichaya
								  FROM thevani WHERE balakedara = $shonuid AND sthiti = 1 AND date(dinankavannuracisi) = date('".$date."')
								  UNION ALL
								  SELECT id as parichaya
								  FROM spinrec WHERE user_id = $shonuid AND date(time) = date('".$date."')
								  UNION ALL
								  SELECT shonu as parichaya
								  FROM hintegedukolli WHERE balakedara = $shonuid AND date(dinankavannuracisi) = date('".$date."')
								  UNION ALL
								  SELECT id as parichaya
								  FROM rebetrec WHERE user_id = $shonuid AND date(created_at) = date('".$date."')
								  UNION ALL
								  SELECT dearlord as parichaya
								  FROM cihne WHERE identity = $shonuid AND date(amen) = date('".$date."')
								  UNION ALL
								  SELECT id as parichaya
								  FROM viprec WHERE user_id = $shonuid AND date(created_at) = date('".$date."')
								  UNION ALL
								  SELECT id as parichaya
								  FROM commission WHERE user_id = $shonuid AND date(created_timestamp) = date('".$date."')
								  UNION ALL
								  SELECT id as parichaya
								  FROM egrahcer_sonub WHERE dr = $shonuid AND date(time) = date('".$date."')
								   UNION ALL
								  SELECT id as parichaya
								  FROM noitativni_sonub WHERE arthur = $shonuid AND date(time) = date('".$date."')
								  UNION ALL
								  SELECT kani as parichaya
								  FROM hodike_balakedara WHERE userkani = $shonuid AND date(shonu) = date('".$date."')";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
							else if($type == 0){
								$samasye = "SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_drei WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutate_funj  
WHERE byabahakarta = $shonuid;
 AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_drei WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_funf WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala  
FROM bajikattutate_kemuru_zehn  
WHERE byabahakarta = $shonuid;
 AND date(tiarikala) = date('".$date."')
								  ORDER BY tiarikala DESC LIMIT $pageSize OFFSET $samatolana";							
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT parichaya
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_drei WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_funf WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_drei WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_funf WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_zehn WHERE byabaharkarta = $shonuid AND date(tiarikala) = date('".$date."')";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
							else if($type == 1){
								$samasye = "SELECT macau, salary, createdate  
FROM dailysalary  
WHERE userid = $shonuid  
AND date(createdate) = date('".$date."')  
ORDER BY macau DESC  
LIMIT $pageSize OFFSET $samatolana;
";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT macau  
FROM dailysalary  
WHERE userid = $shonuid  
AND date(createdate) = date('".$date."');
";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
							else if($type == 4){
								$samasye = "SELECT shonu, motta, dinankavannuracisi  
FROM thevani  
WHERE balakedara = $shonuid  
AND sthiti = 1  
AND date(dinankavannuracisi) = date('".$date."')  
ORDER BY dinankavannuracisi DESC  
LIMIT $pageSize OFFSET $samatolana;
";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT shonu, motta, dinankavannuracisi  
FROM thevani  
WHERE balakedara = $shonuid  
AND sthiti = 1  
AND date(dinankavannuracisi) = date;
('".$date."')";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							
						   } else if ($type == 119) {
                              $samasye = "SELECT id, prize, time
                                  FROM spinrec
                                  WHERE user_id = $shonuid AND date(time) = date('" . $date . "')
                                  ORDER BY time DESC LIMIT $pageSize OFFSET $samatolana";
                              $samasyephalitansa = $conn->query($samasye);
    
                              $samasye_ondu = "SELECT id, prize, time
                                  FROM spinrec
                                WHERE user_id = $shonuid AND date(time) = date('" . $date . "')";
                             $samasyephalitansa_ondu = $conn->query($samasye_ondu);
                             $samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							} else if ($type == 12) {
								$samasye = "SELECT kramasankhye, bonus, dinankavannuracisi
									FROM shonu_kaichila
									WHERE balakedara = $shonuid AND date(dinankavannuracisi) = date('" . $date . "')
									ORDER BY time DESC LIMIT $pageSize OFFSET $samatolana";
								$samasyephalitansa = $conn->query($samasye);
	  
								$samasye_ondu = "SELECT kramasankhye, bonus, dinankavannuracisi
									FROM shonu_kaichila
								  WHERE balakedara = $shonuid AND date(dinankavannuracisi) = date('" . $date . "')";
							   $samasyephalitansa_ondu = $conn->query($samasye_ondu);
							   $samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
                           }else if($type == 5) {
                                   $samasye = "SELECT shonu, motta, dinankavannuracisi, remarks 
                                               FROM hintegedukolli 
                                               WHERE balakedara = $shonuid 
                                               AND date(dinankavannuracisi) = date('".$date."') 
                                               ORDER BY dinankavannuracisi DESC 
                                               LIMIT $pageSize OFFSET $samatolana";
                                   $samasyephalitansa = $conn->query($samasye);
                                   
                                   $samasye_ondu = "SELECT shonu, motta, dinankavannuracisi, remarks 
                                                    FROM hintegedukolli 
                                                    WHERE balakedara = $shonuid 
                                                    AND date(dinankavannuracisi) = date('".$date."')";
                                   $samasyephalitansa_ondu = $conn->query($samasye_ondu);
                                   $samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
                               }

							else if($type == 2){
								$samasye = "SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya, ketebida, phalaphala, sesabida, tiarikala
								  FROM bajikattuttate_kemuru_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  ORDER BY tiarikala DESC LIMIT $pageSize OFFSET $samatolana";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT parichaya
								  FROM bajikattuttate WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_aidudi_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_drei WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_funf WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')
								  UNION ALL
								  SELECT parichaya
								  FROM bajikattuttate_kemuru_zehn WHERE byabaharkarta = $shonuid AND phalaphala = 'gagner' AND date(tiarikala) = date('".$date."')";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
							else if($type == 3){
								$samasye = "SELECT kani, price, shonu
								  FROM hodike_balakedara WHERE userkani = $shonuid AND date(shonu) = date('".$date."')
								  ORDER BY shonu DESC LIMIT $pageSize OFFSET $samatolana";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT kani
								  FROM hodike_balakedara WHERE userkani = $shonuid AND date(shonu) = date('".$date."')";
								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
							else if($type == 14){
								$samasye = "SELECT id, sturgis, time  
FROM egrahcer_sonub  
WHERE dr = $shonuid  
AND date(time) = date('".$date."')  
ORDER BY time DESC  
LIMIT $pageSize  
OFFSET $samatolana;
";
								$samasyephalitansa = $conn->query($samasye);
								
								$samasye_ondu = "SELECT id  
FROM egrahcer_sonub  
WHERE dr = $shonuid  
AND date(time) = date;
('".$date."')";

// Reward Amounts ko fetch karna
$rewardAmounts = [];
$sql = "SELECT id, rewardAmount FROM tbl_firstdepositreward";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rewardAmounts[$row['id']] = $row['rewardAmount'];
    }
}

// Default values agar koi id na mile
for ($i = 1; $i <= 8; $i++) {
    if (!isset($rewardAmounts[$i])) {
        $rewardAmounts[$i] = 0;
    }
}

// Variables assign karna
$rewardAmount1 = $rewardAmounts[1];
$rewardAmount2 = $rewardAmounts[2];
$rewardAmount3 = $rewardAmounts[3];
$rewardAmount4 = $rewardAmounts[4];
$rewardAmount5 = $rewardAmounts[5];
$rewardAmount6 = $rewardAmounts[6];
$rewardAmount7 = $rewardAmounts[7];
$rewardAmount8 = $rewardAmounts[8];

// `phalaphala` == 'frc' hone par data assign karna
if ($row['phalaphala'] == 'frc') {
    $ketebida = (int) $row['ketebida']; // Ensure it's an integer

    // Reward Amount Mapping
    $rewardMap = [
        1 => $rewardAmount1,
        2 => $rewardAmount2,
        3 => $rewardAmount3,
        4 => $rewardAmount4,
        5 => $rewardAmount5,
        6 => $rewardAmount6,
        7 => $rewardAmount7,
        8 => $rewardAmount8
    ];

    // Assign amount based on ketebida
    $data['list'][$i]['amount'] = $rewardMap[$ketebida] ?? 0;
    $data['list'][$i]['type'] = 14;
    $data['list'][$i]['typeName'] = 'first recharge';
    $data['list'][$i]['typeNameCode'] = '8014';
    $data['list'][$i]['orderNum'] = $row['parichaya'];
    $data['list'][$i]['addTime'] = $row['tiarikala'];
    $data['list'][$i]['remark'] = '';
}

								$samasyephalitansa_ondu = $conn->query($samasye_ondu);
								$samasyephalitansa_sankhye = mysqli_num_rows($samasyephalitansa_ondu);
							}
						}						
						
						if($type == -1 || $type == 0 || $type == 1 || $type == 4 || $type == 119|| $type == 5 || $type == 2 || $type == 3 || $type == 14){
							if ($samasyephalitansa->num_rows > 0) {
								$i = 0;
								while ($row = $samasyephalitansa->fetch_assoc()) {
									if($type == 1){
										$data['list'][$i]['amount'] = $row['salary'];
										$data['list'][$i]['type'] = 1;
										$data['list'][$i]['typeName'] = 'Salary';
										$data['list'][$i]['typeNameCode'] = '8001';
										$data['list'][$i]['orderNum'] = $row['macau'];
										$data['list'][$i]['addTime'] = $row['createdate'];
										$data['list'][$i]['remark'] = '';
									}
									else if($type == 4){
										$data['list'][$i]['amount'] = $row['motta'];
										$data['list'][$i]['type'] = 4;
										$data['list'][$i]['typeName'] = 'Deposit';
										$data['list'][$i]['typeNameCode'] = '8004';
										$data['list'][$i]['orderNum'] = $row['shonu'];
										$data['list'][$i]['addTime'] = $row['dinankavannuracisi'];
										$data['list'][$i]['remark'] = '';
									}
									else if($type == 119){
										$data['list'][$i]['amount'] = $row['prize'];
										$data['list'][$i]['type'] = 119;
										$data['list'][$i]['typeName'] = 'spin';
										$data['list'][$i]['typeNameCode'] = '8119';
										$data['list'][$i]['orderNum'] = $row['id'];
										$data['list'][$i]['addTime'] = $row['time'];
										$data['list'][$i]['remark'] = '';
									}else if($type == 12){
										$data['list'][$i]['amount'] = $row['bonus'];
										$data['list'][$i]['type'] = 12;
										$data['list'][$i]['typeName'] = 'spin';
										$data['list'][$i]['typeNameCode'] = '8012';
										$data['list'][$i]['orderNum'] = $row['kramasankhye'];
										$data['list'][$i]['addTime'] = $row['dinankavannuracisi'];
										$data['list'][$i]['remark'] = '';
									}
									else if($type == 5){
										$data['list'][$i]['amount'] = $row['motta'];
										$data['list'][$i]['type'] = 5;
										$data['list'][$i]['typeName'] = 'Withdraw';
										$data['list'][$i]['typeNameCode'] = '8005';
										$data['list'][$i]['orderNum'] = $row['shonu'];
										$data['list'][$i]['addTime'] = $row['dinankavannuracisi'];
										$data['list'][$i]['remark'] = $row['remarks'];
									}
									else if($type == 2){
										$data['list'][$i]['amount'] = $row['sesabida'];
										$data['list'][$i]['type'] = 2;
										$data['list'][$i]['typeName'] = 'Jackpot increase';
										$data['list'][$i]['typeNameCode'] = '8002';
										$data['list'][$i]['orderNum'] = $row['parichaya'];
										$data['list'][$i]['addTime'] = $row['tiarikala'];
										$data['list'][$i]['remark'] = '';
									}
									else if($type == 3){
										$data['list'][$i]['amount'] = $row['price'];
										$data['list'][$i]['type'] = 3;
										$data['list'][$i]['typeName'] = 'Red Envelope';
										$data['list'][$i]['typeNameCode'] = '8003';
										$data['list'][$i]['orderNum'] = $row['kani'];
										$data['list'][$i]['addTime'] = $row['shonu'];
										$data['list'][$i]['remark'] = '';
									}
									else if($type == 14){
										$data['list'][$i]['amount'] = $row['sturgis'] == 1 ? 60 : ($row['sturgis'] == 2 ? 20 : ($row['sturgis'] == 3 ? 150 : ($row['sturgis'] == 4 ? 300 : 0)));
										$data['list'][$i]['amount'] = $row['sturgis'] == 5 ? 600 : ($row['sturgis'] == 6 ? 2000 : ($row['sturgis'] == 7 ? 5000 : ($row['sturgis'] == 8 ? 10000 : $data['list'][$i]['amount']))); 
										$data['list'][$i]['type'] = 14;
										$data['list'][$i]['typeName'] = 'First deposit bonus';
										$data['list'][$i]['typeNameCode'] = '8014';
										$data['list'][$i]['orderNum'] = $row['id'];
										$data['list'][$i]['addTime'] = $row['time'];
										$data['list'][$i]['remark'] = '';
									}
									else{
										if($row['phalaphala'] == 'gagner'){
											$data['list'][$i]['amount'] = $row['sesabida'];
											$data['list'][$i]['type'] = 2;
											$data['list'][$i]['typeName'] = 'Jackpot increase';
											$data['list'][$i]['typeNameCode'] = '8002';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = '';
										}
										else if($row['phalaphala'] == 'ds'){
											$data['list'][$i]['amount'] = $row['ketebida'];
											$data['list'][$i]['type'] = 1;
											$data['list'][$i]['typeName'] = 'Salary';
											$data['list'][$i]['typeNameCode'] = '8001';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = '';
										}
										else if($row['phalaphala'] == 'rc'){
											$data['list'][$i]['amount'] = $row['ketebida'];
											$data['list'][$i]['type'] = 4;
											$data['list'][$i]['typeName'] = 'Deposit';
											$data['list'][$i]['typeNameCode'] = '8004';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = '';
										}
										else if($row['phalaphala'] == 'rb'){
											$data['list'][$i]['amount'] = (int)$row['ketebida'];
											$data['list'][$i]['type'] = 119;
											$data['list'][$i]['typeName'] = 'spin';
											$data['list'][$i]['typeNameCode'] = '8119';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = '';
                                          }else if($row['phalaphala'] == 'sb'){
											$data['list'][$i]['amount'] = (int)$row['ketebida'];
											$data['list'][$i]['type'] = 12;
											$data['list'][$i]['typeName'] = 'signup';
											$data['list'][$i]['typeNameCode'] = '8012';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = '';
										}else if($row['phalaphala'] == 'orb'){
											$data['list'][$i]['amount'] = $row['ketebida'];
											$data['list'][$i]['type'] = 102;
											$data['list'][$i]['typeName'] = 'rebet';
											$data['list'][$i]['typeNameCode'] = '8102';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = '';
										}else if($row['phalaphala'] == 'reftask'){
											$data['list'][$i]['amount'] = $row['ketebida'];
											$data['list'][$i]['type'] = 20;
											$data['list'][$i]['typeName'] = 'refer';
											$data['list'][$i]['typeNameCode'] = '8020';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = '';
										}else if($row['phalaphala'] == 'lvlup'){
                                         if ($row['sesabida'] == 1) {
                                            $data['list'][$i]['type'] = 29;
                                            $data['list'][$i]['typeNameCode'] = '8029';
                                        } else if ($row['sesabida'] == 2) {
                                            $data['list'][$i]['type'] = 30; 
                                            $data['list'][$i]['typeNameCode'] = '8030';
                                        } else {
    
                                           $data['list'][$i]['type'] = null;
                                        }
                                          $data['list'][$i]['amount'] = $row['ketebida']; 
                                          $data['list'][$i]['typeName'] = 'VIP'; 
                                          $data['list'][$i]['orderNum'] = $row['parichaya'];
                                          $data['list'][$i]['addTime'] = $row['tiarikala'];
                                          $data['list'][$i]['remark'] = 'VIP' ;

										}else if($row['phalaphala'] == 'atb'){
											$data['list'][$i]['amount'] = $row['ketebida'];
											$data['list'][$i]['type'] = 7;
											$data['list'][$i]['typeName'] = 'attendence';
											$data['list'][$i]['typeNameCode'] = '8007';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = '';
										}else if($row['phalaphala'] == 'cmd'){
											$data['list'][$i]['amount'] = $row['ketebida'];
											$data['list'][$i]['type'] = 1;
											$data['list'][$i]['typeName'] = 'attendence';
											$data['list'][$i]['typeNameCode'] = '8001';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = '';
										}
										else if($row['phalaphala'] == 'wd'){
											$data['list'][$i]['amount'] = $row['ketebida'];
											$data['list'][$i]['type'] = 5;
											$data['list'][$i]['typeName'] = 'Withdraw';
											$data['list'][$i]['typeNameCode'] = '8005';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = $row['sesabida'];
										}
										else if($row['phalaphala'] == 're'){
											$data['list'][$i]['amount'] = $row['ketebida'];
											$data['list'][$i]['type'] = 3;
											$data['list'][$i]['typeName'] = 'Red Envelope';
											$data['list'][$i]['typeNameCode'] = '8003';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = $row['sesabida'];
											
											//Custom First Deposit Bonus section// 
											
										}else if($row['phalaphala'] == 'frc') {
                                        $ketebida = (int) $row['ketebida']; // Ensure it's an integer
                                         // Fetch reward amounts from tbl_firstdepositreward
                                              $rewardAmounts = [];
                                          $query = "SELECT id, rewardAmount FROM tbl_firstdepositreward";
                                                 $result = mysqli_query($conn, $query);
    
                                         while ($rewardRow = mysqli_fetch_assoc($result)) {
                                                $rewardAmounts[$rewardRow['id']] = $rewardRow['rewardAmount'];
                                                                                    }
                                               // Assign amount based on ketebida
                                               $data['list'][$i]['amount'] = $rewardAmounts[$ketebida] ?? 0; 

                                             // Other details
                                             $data['list'][$i]['type'] = 14;
                                             $data['list'][$i]['typeName'] = 'first recharge';
                                             $data['list'][$i]['typeNameCode'] = '8014';
                                             $data['list'][$i]['orderNum'] = $row['parichaya'];
                                             $data['list'][$i]['addTime'] = $row['tiarikala'];
                                                    $data['list'][$i]['remark'] = '';
                                            } 
                                            
                                            // Custom First Deposit Bonus section  End //

										else{
											$data['list'][$i]['amount'] = $row['ketebida'];
											$data['list'][$i]['type'] = 0;
											$data['list'][$i]['typeName'] = 'Bet amount reduced';
											$data['list'][$i]['typeNameCode'] = '8000';
											$data['list'][$i]['orderNum'] = $row['parichaya'];
											$data['list'][$i]['addTime'] = $row['tiarikala'];
											$data['list'][$i]['remark'] = '0';
										}
									}								
									$i++;
								}
								$data['pageNo'] = (int)$pageNo;
								$data['totalPage'] = ceil($samasyephalitansa_sankhye/10);
								$data['totalCount'] = $samasyephalitansa_sankhye;							
							}
							else{
								$data['list'] = [];
								$data['pageNo'] = (int)$pageNo;
								$data['totalPage'] = 0;
								$data['totalCount'] = 0;
							}
						}
						else{
							$data['list'] = [];
							$data['pageNo'] = (int)$pageNo;
							$data['totalPage'] = 0;
							$data['totalCount'] = 0;
						}
																		
												
						$res['data'] = $data;
						$res['code'] = 0;
						$res['msg'] = 'Succeed';
						$res['msgCode'] = 0;
						http_response_code(200);
						echo json_encode($res);					
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