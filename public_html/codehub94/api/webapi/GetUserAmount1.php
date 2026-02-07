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
        $shonustr = '{"language":' . $language . ',"random":"' . $random . '"}';
        $shonusign = strtoupper(md5($shonustr));
        if ($shonusign == $signature) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, 1);
            if ($data_auth['status'] === 'Success') {
                $sesquery = "SELECT akshinak
					  FROM shonu_subjects
					  WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);
                if ($sesnum == 1) {
                    // Fetch user details from the database using the token
                    $userQuery = "SELECT id FROM shonu_subjects WHERE akshinak = '$author'";
                    $userResult = $conn->query($userQuery);

                    // Encrypt payload using AES256 (ECB mode)
                    function encryptAES256ECB($data, $key)
                    {
                        // Ensure the key length is 32 bytes (AES-256 requires 256-bit keys, i.e., 32 bytes)
                        $key = str_pad($key, 32, "\0"); // Pad key if it is less than 32 bytes

                        $encryptedData = openssl_encrypt($data, 'AES-256-ECB', $key, OPENSSL_RAW_DATA);
                        return base64_encode($encryptedData); // Return base64-encoded encrypted data
                    }

                    if ($userResult && $userResult->num_rows > 0) {
                        $userRow = $userResult->fetch_assoc();
                        $userName = $userRow['id']; // 'id' corresponds to 'userName'

                        // Fetch user balance from shonu_kaichila using the userName
                        $balanceQuery = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$userName'";
                        $balanceResult = $conn->query($balanceQuery);

                        if ($balanceResult && $balanceResult->num_rows > 0) {
                            $balanceRow = $balanceResult->fetch_assoc();
                            $userBalance = $balanceRow['motta']; // 'motta' is the balance column

                            // Proceed with withdrawal logic even if the balance is 0 or greater
                            $timestamp = round(microtime(true) * 1000); // Milliseconds timestamp
                            $memberAccount = "h7bfd4{$userName}new24gamebdg"; // Generate member account dynamically
                            $transferId = $memberAccount . '_' . bin2hex(random_bytes(3)); // Unique transfer ID
                            $aesKey = "b2a5ec5d13dd4248b330551d585440ba"; // AES Key for encryption
                            $serverUrl = "https://jsgame.live/game/v2"; // API URL

                            // Step 1: Initialize the payload with a balance of 0
                            $initPayload = [
                                'agency_uid' => "6670d2d096b3285fa8daa930c3cf2a1b",
                                'member_account' => $memberAccount,
                                'timestamp' => $timestamp,
                                'credit_amount' => "0", // Set balance to 0
                                'currency_code' => "BRL",
                                'language' => "en",
                                'platform' => "2",
                                'home_url' => $origin, // Using the origin header as referer
                                'transfer_id' => $transferId,
                            ];

                            // Encrypt the payload using AES256 (ECB mode)
                            $initEncryptedPayload = encryptAES256ECB(json_encode($initPayload), $aesKey);

                            // Step 2: Prepare the request payload for the initial API request
                            $initRequestPayload = [
                                'agency_uid' => "6670d2d096b3285fa8daa930c3cf2a1b",
                                'timestamp' => $timestamp,
                                'payload' => $initEncryptedPayload,
                            ];

                            // Send the initial request to the server (for balance deduction initialization)
                            $ch = curl_init($serverUrl);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($initRequestPayload));
                            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                            $initResponse = curl_exec($ch);
                            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);

                            // Handle the initial response
                            if ($httpCode === 200 && $initResponse !== false) {
                                $initResponseData = json_decode($initResponse, true);

                                // Check if the response is successful
                                if ($initResponseData['code'] === 0) {
                                    $initAdd = $initResponseData['payload']['after_amount']; // Amount to deduct

                                    // Step 3: Deduct the balance
                                    $deductPayload = [
                                        'agency_uid' => "6670d2d096b3285fa8daa930c3cf2a1b",
                                        'member_account' => $memberAccount,
                                        'credit_amount' => "-" . $initAdd, // Deduct the balance
                                        'currency_code' => "BRL",
                                        'language' => "en",
                                        'platform' => "2",
                                        'home_url' => $origin,
                                        'transfer_id' => $memberAccount . '_' . bin2hex(random_bytes(3)),
                                        'timestamp' => round(microtime(true) * 1000),
                                    ];

                                    // Encrypt the deduction payload using AES256 (ECB mode)
                                    $deductEncryptedPayload = encryptAES256ECB(json_encode($deductPayload), $aesKey);

                                    // Prepare the request payload for deduction
                                    $deductRequestPayload = [
                                        'agency_uid' => "6670d2d096b3285fa8daa930c3cf2a1b",
                                        'timestamp' => $deductPayload['timestamp'],
                                        'payload' => $deductEncryptedPayload,
                                    ];

                                    // Send the deduction request to the server
                                    $ch = curl_init($serverUrl);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_POST, true);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($deductRequestPayload));
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                                    $deductResponse = curl_exec($ch);
                                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    curl_close($ch);

                                    // Handle the deduction response
                                    if ($httpCode === 200 && $deductResponse !== false) {
                                        $deductResponseData = json_decode($deductResponse, true);

                                        if ($deductResponseData['code'] === 0) {
                                            // Deduction successful, now update the user's balance in the database
                                            $amountToDeduct = (float) $initAdd; // Amount to add back to the user's balance

                                            // Update the balance by adding the deducted amount
                                            $updateBalanceQuery = "UPDATE shonu_kaichila SET motta = motta + ? WHERE balakedara = ?";
                                            $stmt = $conn->prepare($updateBalanceQuery);
                                            $stmt->bind_param("ds", $amountToDeduct, $userName);
                                            $stmt->execute();

                                            if ($stmt->affected_rows > 0) {
                                                $res['code'] = 0;
                                                $res['msg'] = 'Withdrawal successful and balance updated';
                                                $res['data'] = ['new_balance' => $amountToDeduct];
                                                http_response_code(200);
                                                echo json_encode($res);
                                            } else {
                                                // Handle failure to update the balance in the database
                                                $res['code'] = 11;
                                                $res['msg'] = 'Failed to update user balance';
                                                http_response_code(500);
                                                echo json_encode($res);
                                            }
                                        } else {
                                            // Handle deduction failure
                                            $res['code'] = $deductResponseData['code'];
                                            $res['msg'] = 'Deduction error: ' . $deductResponseData['msg'];
                                            http_response_code(400);
                                            echo json_encode($res);
                                        }
                                    } else {
                                        // Handle failure in deduction request
                                        $res['code'] = 9;
                                        $res['msg'] = 'Failed to deduct money from the server';
                                        $res['error'] = 'Error in deduction API request';
                                        http_response_code(500);
                                        echo json_encode($res);
                                    }
                                } else {
                                    // Handle API error during balance initialization
                                    $res['code'] = $initResponseData['code'];
                                    $res['msg'] = 'API error during init: ' . $initResponseData['msg'];
                                    http_response_code(400);
                                    echo json_encode($res);
                                }
                            } else {
                                // Handle failure in the initial API request
                                $res['code'] = 9;
                                $res['msg'] = 'Failed to connect to the API server';
                                $res['error'] = 'Error in initialization API request';
                                http_response_code(500);
                                echo json_encode($res);
                            }
                        } else {
                            // Handle failure to fetch balance data
                            $res['code'] = 8;
                            $res['msg'] = 'Balance not found for user';
                            http_response_code(400);
                            echo json_encode($res);
                        }
                    } else {
                        // If session validation failed
                        $res['code'] = 4;
                        $res['msg'] = 'No operation permission';
                        http_response_code(401);
                        echo json_encode($res);
                    }
                }
            } else {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);
        }
    } else {
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
