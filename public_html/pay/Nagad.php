<?php
    header('Content-type: text/plain; charset=utf-8');
    include ("../serive/samparka.php");

if(isset($_GET['amount'])){
    $ramt = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['amount']));
    $payTypeID = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['tyid']));
} else{
    $ramt = 0;
}

if ($payTypeID == 1023) {
    $payName = 'bKash'; 
} elseif ($payTypeID == 1124) {
    $payName = 'TB-pay';
} elseif ($payTypeID == 1030) {
    $payName = 'LG-pay';
} elseif ($payTypeID == 1029) {
    $payName = 'FAST-UPIPay';
} elseif ($payTypeID == 1021) {
    $payName = 'YaYa-APPpay';
} elseif ($payTypeID == 1010) {
    $payName = 'Nagad';
} elseif ($payTypeID == 1012) {
    $payName = 'Super-ORpay';
} elseif ($payTypeID == 1013) {
    $payName = 'YaYa-ORpay';
} elseif ($payTypeID == 1014) {
    $payName = 'UPI x QR';
} elseif ($payTypeID == 1015) {
    $payName = 'SunPay';
} elseif ($payTypeID == 2123) {
    $payName = 'UPAY-USDT';
} elseif ($payTypeID == 2190) {
    $payName = 'UU-USDT';
} elseif ($payTypeID == 2191) {
    $payName = '7Day-PayTM';
} elseif ($payTypeID == 2192) {
    $payName = 'UPI-PayTM';
}

$dot_pos = strpos($ramt, '.');
if ($dot_pos === false) {
    $ramt = $ramt . '.00';
} else {
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
$serial = $date . $time . rand(100000, 999900);
$tyid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['tyid']));
$uid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['uid']));
$sign = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['sign']));
$urlInfo = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['urlInfo']));

// Check if `uid` exists in the `demo` table
$demoQuery = "SELECT 1 FROM demo WHERE balakedara = '$uid'";
$demoResult = $conn->query($demoQuery);
if ($demoResult->num_rows > 0) {
    // If uid exists in demo table, insert into `thevani` table and update `motta`
    $createdate = date("Y-m-d H:i:s");
   
    // Insert into `thevani`
    $insertQuery = "
        INSERT INTO `thevani` (`balakedara`, `motta`, `dharavahi`, `mula`, `ullekha`, `duravani`, `ekikrtapavati`, `dinankavannuracisi`, `madari`, `pavatiaidi`, `sthiti`)
        VALUES ('$uid', '$ramt', '$serial', '$payName', 'N/A', 'N/A', 'N/A', '$createdate', '1005', '2', '1')
    ";
    $conn->query($insertQuery);
    // Update `motta` field in `shonu_kaichila`
    $updateQuery = "
        UPDATE `shonu_kaichila`
        SET `motta` = `motta` + $ramt
        WHERE `balakedara` = '$uid'
    ";
    $conn->query($updateQuery);
}

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
    $myurl = 'https://Sol-0203.com';
    
    if($myurl){
        $orderid = $serial;
        $amount = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['amount']));
        $name = 'TestName';
        $email = 'testemail@gmail.com';
        $mobile = $numarr['mobile'];
        $remark = 'remark';
        $type = 2;
       
        // RupantorPay API Configuration - UPDATE THESE WITH YOUR CREDENTIALS
        $rupantorApiKey = "lRwWJYi4w2u9ioPaN37h22skt7DaXXcYEtXYOIcri7AXjUIHW2"; // Get from Brands section
        $rupantorApiUrl = "https://payment.rupantorpay.com/api/payment/checkout";
        
        // Get user details for RupantorPay
        $userQuery = "SELECT mobile, codechorkamukala FROM shonu_subjects WHERE id = '$uid'";
        $userResult = $conn->query($userQuery);
        $userData = mysqli_fetch_array($userResult);
        
        $customerName = $userData['codechorkamukala'] ?: 'Customer';
        $customerEmail = $userData['mobile'] . '@janu88.com';
        $customerMobile = $userData['mobile'];
        
        // RupantorPay API Parameters
        $successUrl = "https://Sol-0203.com/pay/success.php";
        $cancelUrl = "https://Sol-0203.com/pay/cancel.php";
        $webhookUrl = "https://Sol-0203.com/pay/webhook.php?i=$serial&type=nagad";
        
        $postData = json_encode([
            "fullname" => $serial,
            "email" => $customerEmail,
            "amount" => $amount,
            "success_url" => $successUrl,
            "cancel_url" => $cancelUrl,
            "webhook_url" => $webhookUrl,
            "meta_data" => [
                "order_id" => $serial,
                "user_id" => $uid,
                "mobile" => $customerMobile,
                "payment_type" => $payName
            ]
        ]);

        // Initialize cURL for RupantorPay
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $rupantorApiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'X-API-KEY: ' . $rupantorApiKey,
                'Content-Type: application/json',
                'X-CLIENT: janu88.com'
            ),
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response) {
            $responseData = json_decode($response, true);
            
            if ($responseData && $responseData['status'] == 1 && isset($responseData['payment_url'])) {
                $createdate = date("Y-m-d H:i:s");
                $depositQuery = mysqli_query($conn, "
                    INSERT INTO `thevani` (`payid`, `balakedara`, `motta`, `dharavahi`, `mula`, `ullekha`, `duravani`, `ekikrtapavati`, `dinankavannuracisi`, `madari`, `pavatiaidi`, `sthiti`)
                    VALUES ('1', '$uid', '$amount', '$serial', '$payName', 'RupantorPay', '$customerEmail', 'N/A', '$createdate', '1005', '2', '0')
                ");
                
                // Redirect to RupantorPay payment page
                header('Location: ' . $responseData['payment_url']);
                exit;
                
            } else {
                // Handle API error
                $errorMessage = $responseData['message'] ?? 'Unknown error occurred';
                echo "Error: " . $errorMessage;
                error_log("RupantorPay Error: " . $errorMessage . " - Response: " . $response);
            }
        } else {
            echo "Error: Unable to connect to payment gateway.";
            error_log("RupantorPay Connection Failed");
        }
    } else {
        $res['code'] = 10000;
        $res['success'] = 'false';
        $res['message'] = 'Sorry, The system is busy, please try again later!';
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(200);
        echo json_encode($res);
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);
    echo json_encode($res);
}
?>