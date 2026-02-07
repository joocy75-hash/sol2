<?php 
	header('Content-type: text/plain; charset=utf-8');
	include ("../serive/samparka.php");
?>
<?php 
if(isset($_GET['amount'])){
	$ramt = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['amount']));
} else{
	$ramt = 0;
}
$dot_pos = strpos($ramt, '.');
if ($dot_pos === false) {
    $ramt = $ramt . '.00';
}else {
    $after_dot = substr($ramt, $dot_pos + 1);
    $after_dot_length = strlen($after_dot);
    if ($after_dot_length > 2) {
        $after_dot = substr($after_dot, 0, 2);
        $ramt = substr($ramt, 0, $dot_pos + 1) . $after_dot;
    } elseif ($after_dot_length < 2) {
        $zeros_to_add = 2 - $after_dot_length;
        $ramt = $ramt . str_repeat('0', $zeros_to_add);
    }
}
$date = date("Ymd");
$time = time();
$serial = $date . $time . rand(1000,9999);

$tyid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['tyid']));
$uid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['uid']));
$sign = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['sign']));
$urlInfo = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['urlInfo']));
?>
<?php
	$res = [
		'code' => 405,
		'message' => 'Illegal access!',
	];
	if (isset($_GET['tyid']) && isset($_GET['amount']) && isset($_GET['uid']) && isset($_GET['sign']) && isset($_GET['urlInfo'])) {
		$userId = $uid;
		$userPhoto = '1';
		
		$numquery = "SELECT mobile, codechorkamukala
		  FROM shonu_subjects
		  WHERE id = ".$userId;
		$numresult = $conn->query($numquery);
		$numarr = mysqli_fetch_array($numresult);
		
		$userName = '91'.$numarr['mobile'];
		$nickName = $numarr['codechorkamukala'];
		
		$creaquery = "SELECT createdate
		  FROM shonu_subjects
		  WHERE id = ".$userId;
		$crearesult = $conn->query($creaquery);
		$creaarr = mysqli_fetch_array($crearesult);
		
		$knbdstr = '{"userId":'.$userId.',"userPhoto":"'.$userPhoto.'","userName":'.$userName.',"nickName":"'.$nickName.'","createdate":"'.$creaarr['createdate'].'"}';
		$shonusign = strtoupper(hash('sha256', $knbdstr));
		
		$urlarr = explode (",", $urlInfo);
		$theirurl = $urlarr[0];
		$myurl = 'https://sikkim999.site';
		
		if($shonusign == $sign && $theirurl == $myurl){
			$url = "https://51xbet.site/admin/paynow";
			
			$merchantid = 'INDIANPAY10045';
			$orderid = $serial;
			$amount = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['amount']));
			$name = 'TestName';
			$email = 'testemail@gmail.com';
			$mobile = $numarr['mobile'];
			$remark = 'remark';
			$type = 2;
			$redirect_url = 'https://sikkim999.site/pay/heyrtnl?order_id='.$serial;						
			
			$data = array(
				'merchantid' => $merchantid,
				'orderid' => $orderid,
				'amount' => $amount,
				'name' => $name,
				'email' => $email,
				'mobile' => $mobile,
				'remark' => $remark,
				'type' => $type,
				'redirect_url' => $redirect_url
			);
			
			$jsonData = json_encode($data);
			
			$ch = curl_init($url);
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
				'Content-Length: ' . strlen($jsonData)
			]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
			
			$response = curl_exec($ch);
			if ($response === false) {
				echo "cURL Error: " . curl_error($ch);
			} else {
				$un = json_decode($response, true);
				if($un['status'] == 'SUCCESS'){
					$amt = $amount;
					$srl = $serial;
					$source = 'IndianPay';
					$ref_num = $un['order_id'];					
					$emailQ = mysqli_query($conn , "SELECT mobile FROM `shonu_subjects` WHERE `id` = '".$uid."'");
					$emailA = mysqli_fetch_array($emailQ);
					$email = $emailA['mobile'];
					$upi = 'IndianPayUPI';
					$createdate = date("Y-m-d H:i:s");
					
					$deposit1 = mysqli_query($conn, "INSERT INTO `thevani`(`balakedara`, `motta`, `dharavahi`, `mula`, `ullekha`, `duravani`, `ekikrtapavati`, `dinankavannuracisi`, `madari`, `pavatiaidi`, `sthiti`) 
					VALUES('$uid', '$amount', '$srl', '$source','$ref_num', '$email', '$upi', '$createdate', '1005', '2', '0')");
					
					$payUrl = $un['payment_link'];
					header("Location: $payUrl");
					exit;
				}
				elseif($un['retCode'] == 'FAIL'){
					echo $un['msg'];
				}
			}			
		}
		else{
			$res['code'] = 10000;
			$res['success'] = 'false';
			$res['message'] = 'Sorry, The system is busy, please try again later!';
			
			header('Content-Type: text/html; charset=utf-8');
			http_response_code(200);
			echo json_encode($res);	
		}
	}
	else{
		header('Content-Type: application/json; charset=utf-8');
		http_response_code(200);
		echo json_encode($res);	
	}
?>
