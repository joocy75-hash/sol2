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
        if (isset($shonupost['language']) && isset($shonupost['logintype']) && isset($shonupost['phonetype']) && isset($shonupost['pwd'])
            && isset($shonupost['random']) && isset($shonupost['timestamp']) && isset($shonupost['username']) && isset($shonupost['captchaId']) && isset($shonupost['track'])) {
            $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
            $logintype = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['logintype']));
            $phonetype = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['phonetype']));
            $pwd = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['pwd']));
            $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
            $username = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['username']));
            $captchaId = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['captchaId']));
            $trackData = $shonupost['track'];
            
            // Captcha verification logic
            $captchaQuery = "SELECT correctPositionx FROM captcha_data WHERE captchaId='$captchaId'";
            $captchaResult = $conn->query($captchaQuery);
            if ($captchaResult && mysqli_num_rows($captchaResult) == 1) {
                $captchaRow = mysqli_fetch_assoc($captchaResult);
                $correctPositionx = $captchaRow['correctPositionx'];
                $userTracks = $trackData['tracks'];
                $lastTrack = end($userTracks);
                $userPositionx = $lastTrack['x'];
                
                if (abs($userPositionx - $correctPositionx) > 5) {
                    $res['data'] = null;
                    $res['code'] = 1;
                    $res['msg'] = 'Verification failed, please try again';
                    $res['msgCode'] = 31;
                    http_response_code(200);
                    echo json_encode($res);
                    exit;
                }
            } else {
                $res['data'] = null;
                $res['code'] = 1;
                $res['msg'] = 'Captcha data not found';
                $res['msgCode'] = 32;
                http_response_code(200);
                echo json_encode($res);
                exit;
            }
            
            // Continue with the rest of the login logic
            if(substr($username, 0, 2) == "91") {
                $username = substr($username, 2);
            }
                
            if($logintype == 'mobile'){
                $shonusql="Select id, password, status, ishonup, codechorkamukala from shonu_subjects where mobile='$username'";
            }
            else if($logintype == 'email'){
                $shonusql="Select id, password, status, ishonup, codechorkamukala from shonu_subjects where email='$username'";
            }
            else{
                $shonusql="Select id, password, status, ishonup, codechorkamukala from shonu_subjects where mobile='$username'";
            }
            $shonuresult=$conn->query($shonusql);
            $shonunum = mysqli_num_rows($shonuresult);


if($shonunum == 1){
                $shonurow = mysqli_fetch_array($shonuresult);
                $password = $shonurow['password'];
                if($password == md5($pwd)){
                    if($shonurow['status'] == 1){
                        $data['expiresIn'] = time() + 86400;
                        $shnutkn_head = array('alg'=>'HS256','typ'=>'JWT');
                        $shnutkn_load = array('id'=>$shonurow['id'],'mobile'=>$username, 'status'=>$shonurow['status'], 'expire'=>$data['expiresIn'], 'ishonup'=>$shonurow['ishonup'], 'codechorkamukala'=>$shonurow['codechorkamukala']);
                        $data['tokenHeader'] = 'Bearer ';
                        $data['token'] = generate_jwt($shnutkn_head, $shnutkn_load);                            
                        $shnutkn_head_rfsh = array('alg'=>'HS256','typ'=>'JWT');
                        $shnutkn_load_rfsh = array('id'=>$shonurow['id'],'mobile'=>$username, 'status'=>$shonurow['status'], 'expire'=>$data['expiresIn']);
                        $data['refreshToken'] = generate_jwt($shnutkn_head_rfsh, $shnutkn_load_rfsh);
                        $data['passwordErrorNum'] = 0;
                        $data['passwordErrorMaxNum'] = 30;
                        
                        //shonullgnt - last login time ishonup - last login ip no multiple login
                        //ar-real-ip doesn't match last login ip
                        $ipaddress = '';
                        if (isset($_SERVER['HTTP_CLIENT_IP']))
                            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
                        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
                        else if(isset($_SERVER['HTTP_X_FORWARDED']))
                            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
                        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
                            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
                        else if(isset($_SERVER['HTTP_FORWARDED']))
                            $ipaddress = $_SERVER['HTTP_FORWARDED'];
                        else if(isset($_SERVER['REMOTE_ADDR']))
                            $ipaddress = $_SERVER['REMOTE_ADDR'];
                        else
                            $ipaddress = 'UNKNOWN';    
                        $user_agent = $_SERVER['HTTP_USER_AGENT'];
                        
                        $pwderrsql="UPDATE shonu_subjects set shonupwderr=0, ishonup='$ipaddress', shonullgnt='$shnunc', akshinak='".$data['token']."', tnegaresunohs='$user_agent' where mobile='$username'";
              $conn->query($pwderrsql);
              
              $idQuery = "SELECT id FROM shonu_subjects WHERE mobile = '$username'";
                            $idResult = $conn->query($idQuery);

                                if ($idResult && $idResult->num_rows > 0) {
                            $row = $idResult->fetch_assoc();
                            $id = $row['id'];
                            $title = "Login jankaari";
                            $state = 0;
                            $insertNotificationQuery = "INSERT INTO notification (state, title, user_id, created_at) VALUES ($state, '$title', $id, '$shnunc')";
                            $insertNotificationQuery = "INSERT INTO notification (state, title, user_id, created_at) VALUES ($state, '$title', $id, '$shnunc')";
    
                            $conn->query($insertNotificationQuery);
                             }

$res['data'] = $data;
              $res['code'] = 0;
              $res['msg'] = 'Succeed';
              $res['msgCode'] = 0;
              http_response_code(200);
              echo json_encode($res);
            }
            
                    else{
                        $res['data'] = null;
                        $res['code'] = 1;
                        $res['msg'] = 'User suspended';
                        $res['msgCode'] = 1010;
                        http_response_code(200);
                        echo json_encode($res);
                    }                        
                }
                else{
                    $pwderrsql="UPDATE shonu_subjects set shonupwderr=shonupwderr+1 where mobile='$username'";
                    $conn->query($pwderrsql);
                    $pwderr="Select shonupwderr from shonu_subjects where mobile='$username'";
                    $pwderrresult=$conn->query($pwderr);
                    $pwderrrow = mysqli_fetch_array($pwderrresult);
                    $pwderrvalue = $pwderrrow['shonupwderr'];
                    
                    $data['tokenHeader'] = 'Bearer ';
                    $data['token'] = null;
                    $data['expiresIn'] = 0;
                    $data['refreshToken'] = null;
                    $data['passwordErrorNum'] = $pwderrvalue;
                    $data['passwordErrorMaxNum'] = 30;
                    
                    $res['data'] = $data;
                    $res['code'] = 1;
                    $res['msg'] = 'Password does not correct';
                    $res['msgCode'] = 117;
                    http_response_code(200);
                    echo json_encode($res);
                }                    
            }
            else{
                $res['data'] = null;
                $res['code'] = 1;
                $res['msg'] = 'User not exists';
                $res['msgCode'] = 101;
                http_response_code(200);
                echo json_encode($res);
            }                
        } else {
            $res['code'] = 1;
            $res['msg'] = 'Missing required parameters';
            $res['msgCode'] = 100;
            http_response_code(200);
            echo json_encode($res);
        }    
    } else {    
        http_response_code(405);
        echo json_encode($res);
    }
?>