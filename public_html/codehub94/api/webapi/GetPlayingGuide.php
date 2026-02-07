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
                        $data['playingGuide'] = '
                        <!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>CodeHub94.online Playing Guide</title>
                            <script src="https://cdn.tailwindcss.com"></script>
                            <link href="https://cdn.jsdelivr.net/npm/heroicons@2.0.13/outline/heroicons.min.css" rel="stylesheet">
                            <style>
                                .fade-in { 
                                    animation: fadeIn 0.5s ease-out; 
                                }
                                @keyframes fadeIn { 
                                    from { opacity: 0; transform: translateY(8px); } 
                                    to { opacity: 1; transform: translateY(0); } 
                                }
                                .gradient-bg { 
                                    background: linear-gradient(135deg, #4f46e5, #a855f7); 
                                }
                                .theme-dark { 
                                    background: #111827; 
                                }
                                .theme-dark .gradient-bg { 
                                    background: linear-gradient(135deg, #1e3a8a, #6b21a8); 
                                }
                                .theme-dark .bg-white { 
                                    background: #1f2937; 
                                    color: #d1d5db; 
                                }
                                .theme-dark .text-gray-600 { 
                                    color: #9ca3af; 
                                }
                                .theme-dark .text-gray-800 { 
                                    color: #e5e7eb; 
                                }
                                .list-point { 
                                    font-size: 16px; /* 12pt equivalent */
                                    font-weight: bold; 
                                    text-decoration: underline; 
                                }
                            </style>
                        </head>
                        <body class="bg-gray-50 font-sans transition-colors duration-300">
                            <div class="container mx-auto px-4 py-8 max-w-4xl">
                                <div class="space-y-6">
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                            1. Sign Up
                                        </h2>
                                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                                            <li class="list-point">Enter your phone number.</li>
                                            <li class="list-point">Create a secure password (8 characters, mix of letters and numbers).</li>
                                            <li class="list-point">Confirm your password.</li>
                                            <li class="list-point">Check "Remember Password" for convenience.</li>
                                            <li class="list-point">Click "Sign Up" to register.</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            2. Play Wingo
                                        </h2>
                                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                                            <li class="list-point">Access the Wingo game.</li>
                                            <li class="list-point">Pick a duration (1, 3, 5, or 10 minutes).</li>
                                            <li class="list-point">Know the options:
                                                <ul class="list-circle pl-5 mt-1 space-y-1">
                                                    <li class="list-point"><strong>Green</strong>: Numbers 1, 3, 7, 9.</li>
                                                    <li class="list-point"><strong>Red</strong>: Numbers 2, 4, 6, 8.</li>
                                                    <li class="list-point"><strong>Violet</strong>: Numbers 0, 5.</li>
                                                    <li class="list-point"><strong>Small</strong>: Numbers 0-4.</li>
                                                    <li class="list-point"><strong>Big</strong>: Numbers 5-9.</li>
                                                </ul>
                                            </li>
                                            <li class="list-point">Follow rules; avoid invalid bets (e.g., big and small, red and green, or over 7 numbers).</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                            3. Add Funds
                                        </h2>
                                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                                            <li class="list-point">Click the wallet icon.</li>
                                            <li class="list-point">Choose "Deposit" (UPIPAY or USDT).</li>
                                            <li class="list-point">Select your payment method.</li>
                                            <li class="list-point">Pick a payment channel.</li>
                                            <li class="list-point">Enter the amount.</li>
                                            <li class="list-point">Click "Deposit" and scan the barcode.</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            4. Withdraw Funds
                                        </h2>
                                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                                            <li class="list-point">Click the wallet icon.</li>
                                            <li class="list-point">Select "Withdraw".</li>
                                            <li class="list-point">Enter the withdrawal amount.</li>
                                            <li class="list-point">Ensure bets are settled (zero balance).</li>
                                            <li class="list-point">Choose or add a bank account.</li>
                                            <li class="list-point">Input the amount.</li>
                                            <li class="list-point">Enter your password.</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            5. Review Bets
                                        </h2>
                                        <p class="text-gray-600 text-sm">Check "My History" to view past bets. Use trend charts to plan future bets and see previous results.</p>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                            6. Track Transactions
                                        </h2>
                                        <p class="text-gray-600 text-sm">View all account activities via the "Account" icon.</p>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                            7. Earn Referrals
                                        </h2>
                                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                                            <li class="list-point">Share your referral link. Earn rebates on deposits (0.7% Level 1, 0.75% Level 2, credited daily at 1:00 AM). Check rates in "Promotion". Sports bets excluded.</li>
                                            <li class="list-point">View the barcode via the sharing poster.</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.1.9-2 2-2s2 .9 2 2-2 4-2 4m0 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-2-4-2-4m0 0H3m9 0h9"></path></svg>
                                            8. Update Password
                                        </h2>
                                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                                            <li class="list-point">Log in to CodeHub94.online.</li>
                                            <li class="list-point">Click "Account".</li>
                                            <li class="list-point">Go to "Settings".</li>
                                            <li class="list-point">Select "Edit Password".</li>
                                            <li class="list-point">Enter current password.</li>
                                            <li class="list-point">Create new password.</li>
                                            <li class="list-point">Confirm new password.</li>
                                            <li class="list-point">Click "Save".</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                            9. Link Bank Account
                                        </h2>
                                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                                            <li class="list-point">Log in to CodeHub94.online.</li>
                                            <li class="list-point">Click "Wallet".</li>
                                            <li class="list-point">Select "Withdraw".</li>
                                            <li class="list-point">Click "Add Bank".</li>
                                            <li class="list-point">Fill in details.</li>
                                            <li class="list-point">Click "Save".</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                            10. Reset Password
                                        </h2>
                                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                                            <li class="list-point">Visit CodeHub94.online.</li>
                                            <li class="list-point">Click "Account".</li>
                                            <li class="list-point">Select "Forgot Password".</li>
                                            <li class="list-point">Enter registered phone number.</li>
                                            <li class="list-point">Create new password.</li>
                                            <li class="list-point">Confirm password.</li>
                                            <li class="list-point">Click "Send" for OTP.</li>
                                            <li class="list-point">Enter OTP.</li>
                                            <li class="list-point">Accept "Privacy Agreement".</li>
                                            <li class="list-point">Click "Reset".</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                            11. Get the App
                                        </h2>
                                        <p class="text-gray-600 text-sm">Find the download button at the bottom center of the homepage.</p>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            12. About Us
                                        </h2>
                                        <p class="text-gray-600 text-sm">Click "About" for privacy policy and risk disclosure details.</p>
                                    </div>
                                    
                                    <div class="bg-white p-6 rounded-lg shadow-sm fade-in">
                                        <h2 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l4 4m-8-4h8m-4-8v4m-4 8H4m16 0h-4"></path></svg>
                                            13. Use Gift Codes
                                        </h2>
                                        <ul class="list-disc pl-5 text-gray-600 space-y-2">
                                            <li class="list-point">Log in to CodeHub94.online.</li>
                                            <li class="list-point">Click "Account".</li>
                                            <li class="list-point">Select "Gift".</li>
                                            <li class="list-point">Enter gift code.</li>
                                            <li class="list-point">Click "Receive".</li>
                                            <li class="list-point"><strong>Note:</strong> Get codes from your agent.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </body>
                        </html>
                        ';
                        
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