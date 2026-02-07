<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Dhaka");

$response = [
    "data" => [],
    "code" => 0,
    "msg" => "Succeed",
    "msgCode" => 0,
    "serviceNowTime" => date("Y-m-d H:i:s")
];

echo json_encode($response);
?>
