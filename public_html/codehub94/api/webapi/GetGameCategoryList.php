<?php
include "../../conn.php"; // Database connection file

// Set response headers

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

// Set default timezone
date_default_timezone_set("Asia/Dhaka");
$current_time = date("Y-m-d H:i:s");

// Default response
$response = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $current_time,
];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_body = file_get_contents("php://input");
    $input_data = json_decode($input_body, true);

    // Check required fields
    if (isset($input_data['language']) && isset($input_data['random']) && 
        isset($input_data['signature']) && isset($input_data['timestamp'])) {
        
        // Sanitize inputs
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $input_data['language']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $input_data['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $input_data['signature']));
        
        // Verify signature
        $input_string = '{"language":' . $language . ',"random":"' . $random . '"}';
        $generated_signature = strtoupper(md5($input_string));
        
        if ($generated_signature === $signature) {
            // Fetch enabled game categories from database
            $query = "SELECT id, typeNameCode, categoryCode, categoryName, state, sort, categoryImg FROM game_category WHERE state = 1";
            $result = mysqli_query($conn, $query);

            if ($result) {
                $categories = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $categories[] = [
                        'id' => (int)$row['id'],
                        'typeNameCode' => (int)$row['typeNameCode'],
                        'categoryCode' => $row['categoryCode'],
                        'categoryName' => $row['categoryName'],
                        'state' => (int)$row['state'],
                        'sort' => (int)$row['sort'],
                        'categoryImg' => $row['categoryImg']
                    ];
                }

                // Success response
                $response['data'] = $categories;
                $response['code'] = 0;
                $response['msg'] = 'Succeed';
                $response['msgCode'] = 0;
                http_response_code(200);
            } else {
                // Database error
                $response['code'] = 8;
                $response['msg'] = 'Database Error';
                $response['msgCode'] = 4;
                http_response_code(500);
            }
        } else {
            // Invalid signature
            $response['code'] = 5;
            $response['msg'] = 'Wrong signature';
            $response['msgCode'] = 3;
            http_response_code(200);
        }
    } else {
        // Invalid parameters
        $response['code'] = 7;
        $response['msg'] = 'Param is Invalid';
        $response['msgCode'] = 6;
        http_response_code(200);
    }
} else {
    // Method not allowed
    http_response_code(405);
}

// Output JSON response
echo json_encode($response);
?>