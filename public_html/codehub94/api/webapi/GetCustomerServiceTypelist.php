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
$current_time = date("Y-m-d H:i:s");

$response = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $current_time,
];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $request_body = file_get_contents("php://input");
    $request_data = json_decode($request_body, true);

    if (isset($request_data['language'], $request_data['random'], $request_data['signature'], $request_data['timestamp'])) {
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $request_data['language']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $request_data['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $request_data['signature']));

        $data_string = '{"language":"' . $language . '","random":"' . $random . '"}';
        $calculated_signature = strtoupper(md5($data_string));

        if ($signature) {
            $data = [
                [
                    'typeID' => 3,
                    'typeName' => 'LiveChat',
                ]
            ];

            $response = [
                'code' => 0,
                'msg' => 'Succeed',
                'msgCode' => 0,
                'serviceNowTime' => $current_time,
                'data' => $data,
            ];
            http_response_code(200);
        } else {
            $response = [
                'code' => 5,
                'msg' => 'Wrong signature',
                'msgCode' => 3,
                'serviceNowTime' => $current_time,
            ];
            http_response_code(200);
        }
    } else {
        $response = [
            'code' => 7,
            'msg' => 'Param is Invalid',
            'msgCode' => 6,
            'serviceNowTime' => $current_time,
        ];
        http_response_code(200);
    }
} else {
    http_response_code(405);
}

echo json_encode($response);
?>
