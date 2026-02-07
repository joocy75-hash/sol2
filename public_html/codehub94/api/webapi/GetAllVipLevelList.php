<?php 
header('Content-Type: application/json; charset=utf-8');

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
$serviceNowTime = date("Y-m-d H:i:s");

$response = [
    'data' => [
        [
            'id' => 5,
            'electronic' => 0.00100,
            'realPerson' => 0.00100,
            'physicalEducation' => 0.00100,
            'lottery' => 0.00100,
            'chess' => 0.00100
        ],
        [
            'id' => 3,
            'electronic' => 0.00100,
            'realPerson' => 0.00100,
            'physicalEducation' => 0.00100,
            'lottery' => 0.00100,
            'chess' => 0.00100
        ],
        [
            'id' => 0,
            'electronic' => 0.00050,
            'realPerson' => 0.00050,
            'physicalEducation' => 0.00050,
            'lottery' => 0.00050,
            'chess' => 0.00050
        ],
        [
            'id' => 4,
            'electronic' => 0.00100,
            'realPerson' => 0.00100,
            'physicalEducation' => 0.00100,
            'lottery' => 0.00100,
            'chess' => 0.00100
        ],
        [
            'id' => 1,
            'electronic' => 0.00050,
            'realPerson' => 0.00050,
            'physicalEducation' => 0.00050,
            'lottery' => 0.00050,
            'chess' => 0.00050
        ],
        [
            'id' => 8,
            'electronic' => 0.00150,
            'realPerson' => 0.00150,
            'physicalEducation' => 0.00150,
            'lottery' => 0.00150,
            'chess' => 0.00150
        ],
        [
            'id' => 6,
            'electronic' => 0.00150,
            'realPerson' => 0.00150,
            'physicalEducation' => 0.00150,
            'lottery' => 0.00150,
            'chess' => 0.00150
        ],
        [
            'id' => 2,
            'electronic' => 0.00050,
            'realPerson' => 0.00050,
            'physicalEducation' => 0.00050,
            'lottery' => 0.00050,
            'chess' => 0.00050
        ],
        [
            'id' => 10,
            'electronic' => 0.00300,
            'realPerson' => 0.00300,
            'physicalEducation' => 0.00300,
            'lottery' => 0.00300,
            'chess' => 0.00300
        ],
        [
            'id' => 7,
            'electronic' => 0.00150,
            'realPerson' => 0.00150,
            'physicalEducation' => 0.00150,
            'lottery' => 0.00150,
            'chess' => 0.00150
        ],
        [
            'id' => 9,
            'electronic' => 0.00200,
            'realPerson' => 0.00200,
            'physicalEducation' => 0.00200,
            'lottery' => 0.00200,
            'chess' => 0.00200
        ]
    ],
    'code' => 0,
    'msg' => 'Succeed',
    'msgCode' => 0,
    'serviceNowTime' => $shnunc,
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
