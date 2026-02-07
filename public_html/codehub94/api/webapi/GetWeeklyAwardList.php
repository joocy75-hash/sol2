<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Dhaka");

$now = date("Y-m-d H:i:s");

$response = [
    "data" => [
        [
            "userId" => 104126,
            "configId" => 77,
            "schedule" => 0.0,
            "status" => 1,
            "taskTitle" => "Daily top-up rewards",
            "taskDescribe" => "Daily top-up rewards",
            "taskId" => "D20",
            "taskTarget" => 300.00,
            "taskAwardAmount" => 30.00,
            "createDate" => "2025-04-18 00:00:00",
            "targetTwo" => 900.00,
            "targetItem" => 3,
            "targetSubItem" => null,
            "rechargeCategories" => "",
            "scheduleTwo" => 0.0
        ],
        [
            "userId" => 104126,
            "configId" => 78,
            "schedule" => 0.0,
            "status" => 1,
            "taskTitle" => "Daily top-up rewards",
            "taskDescribe" => "Daily top-up rewards",
            "taskId" => "D20",
            "taskTarget" => 1000.00,
            "taskAwardAmount" => 40.00,
            "createDate" => "2025-04-18 00:00:00",
            "targetTwo" => 3000.00,
            "targetItem" => 3,
            "targetSubItem" => null,
            "rechargeCategories" => "",
            "scheduleTwo" => 0.0
        ],
        [
            "userId" => 104126,
            "configId" => 79,
            "schedule" => 0.0,
            "status" => 1,
            "taskTitle" => "Daily top-up rewards",
            "taskDescribe" => "Daily top-up rewards",
            "taskId" => "D20",
            "taskTarget" => 1500.00,
            "taskAwardAmount" => 50.00,
            "createDate" => "2025-04-18 00:00:00",
            "targetTwo" => 4500.00,
            "targetItem" => 3,
            "targetSubItem" => null,
            "rechargeCategories" => "",
            "scheduleTwo" => 0.0
        ],
        [
            "userId" => 104126,
            "configId" => 80,
            "schedule" => 0.0,
            "status" => 1,
            "taskTitle" => "Daily top-up rewards",
            "taskDescribe" => "Daily top-up rewards",
            "taskId" => "D20",
            "taskTarget" => 3000.00,
            "taskAwardAmount" => 120.00,
            "createDate" => "2025-04-18 00:00:00",
            "targetTwo" => 9000.00,
            "targetItem" => 3,
            "targetSubItem" => null,
            "rechargeCategories" => "",
            "scheduleTwo" => 0.0
        ],
        [
            "userId" => 104126,
            "configId" => 81,
            "schedule" => 0.0,
            "status" => 1,
            "taskTitle" => "Daily top-up rewards",
            "taskDescribe" => "Daily top-up rewards",
            "taskId" => "D20",
            "taskTarget" => 500.00,
            "taskAwardAmount" => 10.00,
            "createDate" => "2025-04-18 00:00:00",
            "targetTwo" => 2500.00,
            "targetItem" => 0,
            "targetSubItem" => null,
            "rechargeCategories" => "",
            "scheduleTwo" => 0.0
        ],
        [
            "userId" => 104126,
            "configId" => 82,
            "schedule" => 0.0,
            "status" => 1,
            "taskTitle" => "Daily top-up rewards",
            "taskDescribe" => "Daily top-up rewards",
            "taskId" => "D20",
            "taskTarget" => 1000.00,
            "taskAwardAmount" => 20.00,
            "createDate" => "2025-04-18 00:00:00",
            "targetTwo" => 5000.00,
            "targetItem" => 0,
            "targetSubItem" => null,
            "rechargeCategories" => "",
            "scheduleTwo" => 0.0
        ],
        [
            "userId" => 104126,
            "configId" => 83,
            "schedule" => 0.0,
            "status" => 1,
            "taskTitle" => "Daily top-up rewards",
            "taskDescribe" => "Daily top-up rewards",
            "taskId" => "D20",
            "taskTarget" => 1500.00,
            "taskAwardAmount" => 30.00,
            "createDate" => "2025-04-18 00:00:00",
            "targetTwo" => 7500.00,
            "targetItem" => 0,
            "targetSubItem" => null,
            "rechargeCategories" => "",
            "scheduleTwo" => 0.0
        ],
        [
            "userId" => 104126,
            "configId" => 84,
            "schedule" => 0.0,
            "status" => 1,
            "taskTitle" => "Daily top-up rewards",
            "taskDescribe" => "Daily top-up rewards",
            "taskId" => "D20",
            "taskTarget" => 3000.00,
            "taskAwardAmount" => 90.00,
            "createDate" => "2025-04-18 00:00:00",
            "targetTwo" => 15000.00,
            "targetItem" => 0,
            "targetSubItem" => null,
            "rechargeCategories" => "",
            "scheduleTwo" => 0.0
        ]
    ],
    "code" => 0,
    "msg" => "Succeed",
    "msgCode" => 0,
    "serviceNowTime" => $now
];

echo json_encode($response);
?>
