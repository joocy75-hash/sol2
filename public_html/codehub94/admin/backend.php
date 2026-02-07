<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit();
}

include "conn.php"; // Database connection

// ✅ Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // ✅ Fetch All Payment Methods
    if ($action === 'fetch') {
        $sql = "SELECT * FROM payment_methods";
        $result = $conn->query($sql);
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
        exit();
    }

    // ✅ Fetch Specific Payment Method by ID
    elseif ($action === 'getDetails' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("SELECT * FROM payment_methods WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        header("Content-Type: application/json");
        echo json_encode($data);
        exit();
    }

    // ✅ Update Payment Method Securely
    elseif ($action === 'update') {
        // 📌 Securely Fetch Input Data
        $id = intval($_POST['id']);
        $payName = $conn->real_escape_string($_POST['payName']);
        $miniPrice = floatval($_POST['miniPrice']);
        $maxPrice = floatval($_POST['maxPrice']);
        $scope = $conn->real_escape_string($_POST['scope']);
        $startTime = $conn->real_escape_string($_POST['startTime']);
        $endTime = $conn->real_escape_string($_POST['endTime']);
        $rechargeRifts = floatval($_POST['rechargeRifts']);
        $status = $conn->real_escape_string($_POST['status']);

        // 📌 Secure Prepared Statement (Prevents SQL Injection)
        $sql = "UPDATE payment_methods SET 
                payName = ?, miniPrice = ?, maxPrice = ?, scope = ?, 
                startTime = ?, endTime = ?, rechargeRifts = ?, status = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sddsssdsd", $payName, $miniPrice, $maxPrice, $scope, $startTime, $endTime, $rechargeRifts, $status, $id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "Database update failed"]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "error" => "Database statement preparation failed"]);
        }
        exit();
    }

    // ✅ If Action is Unknown
    else {
        header("Content-Type: application/json");
        echo json_encode(["success" => false, "error" => "Invalid action"]);
        exit();
    }
}
?>
