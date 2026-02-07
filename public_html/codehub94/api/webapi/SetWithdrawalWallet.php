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
        if (isset($shonupost['withdrawId']) && isset($shonupost['mobileNo']) && isset($shonupost['bankId']) && isset($shonupost['beneficiaryName']) && isset($shonupost['type']) && isset($shonupost['codeType']) && isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {            
            $withdrawId = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['withdrawId']));
            $mobileNo = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['mobileNo']));
            $bankId = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['bankId']));
            $beneficiaryName = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['beneficiaryName']));
            $type = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['type']));
            $codeType = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['codeType']));
            $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));        
            $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
            $timestamp = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['timestamp']));
            
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];                 
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, 1);
            
            if ($is_jwt_valid === false) {
                $res['code'] = 4;
                $res['msg'] = 'Invalid JWT';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
                exit;
            }
            
            if ($data_auth['status'] !== 'Success') {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
                exit;
            }
            
            $sesquery = "SELECT akshinak, id
                      FROM shonu_subjects
                      WHERE akshinak = '$author'";
            $sesresult = $conn->query($sesquery);
            if ($sesresult === false) {
                $res['code'] = 9;
                $res['msg'] = 'Database query failed';
                $res['msgCode'] = 8;
                http_response_code(500);
                echo json_encode($res);
                exit;
            }
            
            $sesnum = mysqli_num_rows($sesresult);
            if ($sesnum != 1) {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
                exit;
            }
            
            $sesrow = $sesresult->fetch_assoc();
            $shonuid = $sesrow['id'];  // Retrieve the id from the shonu_subjects table
            
            // Check if the combination of userid and type already exists
            $checkQuery = "SELECT id FROM bankcard WHERE userid = '$shonuid' AND type = '$bankId'";
            $checkResult = $conn->query($checkQuery);
            if ($checkResult === false) {
                $res['code'] = 9;
                $res['msg'] = 'Database query failed';
                $res['msgCode'] = 8;
                http_response_code(500);
                echo json_encode($res);
                exit;
            }
            
            if (mysqli_num_rows($checkResult) > 0) {
                $res['code'] = 1;
                $res['msg'] = 'You have already bound the e-wallet, please contact customer service to modify';
                $res['msgCode'] = 208;
                http_response_code(400);
                echo json_encode($res);
                exit;
            }
            
            $insertQuery = "INSERT INTO bankcard (userid, account, name, type) VALUES ('$shonuid', '$mobileNo', '$beneficiaryName', '$bankId')";
            if ($conn->query($insertQuery) === TRUE) {
                $res['data'] = null;
                $res['code'] = 0;
                $res['msg'] = 'Succeed';
                $res['msgCode'] = 0;
                http_response_code(200);
                echo json_encode($res);                    
            } else {
                $res['code'] = 8;
                $res['msg'] = 'Failed to insert data: ' . $conn->error;
                $res['msgCode'] = 7;
                http_response_code(500);
                echo json_encode($res);                
            }
        } else {
            $res['code'] = 7;
            $res['msg'] = 'Param is Invalid';
            $res['msgCode'] = 6;
            http_response_code(400);
            echo json_encode($res);            
        }        
    } else {        
        http_response_code(405);
        echo json_encode($res);
    }
    
    mysqli_close($conn);
?>