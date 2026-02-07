<?php
// Include your database connection
include("../conn.php");

// Enable error reporting (for debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Step 1: Get all rows from 'thevani' where server_number is NULL or empty
$getQuery = "SELECT shonu, mula FROM thevani WHERE (server_number IS NULL OR server_number = '')";
$result = mysqli_query($conn, $getQuery);

if (!$result) {
    die("Error fetching thevani data: " . mysqli_error($conn));
}

$updatedCount = 0;
$errors = [];

while ($row = mysqli_fetch_assoc($result)) {
    $shonu = $row['shonu'];
    $method = strtolower(trim($row['mula'])); // method name like bkash, nagad, usdt, rocket, upay
    $server_number = '';

    // Step 2: Fetch server number according to method
    switch ($method) {
        case 'bkash':
            $query = "SELECT maulya FROM deyya WHERE sthiti = 1 LIMIT 1";
            $res = mysqli_query($conn, $query);
            if ($res && mysqli_num_rows($res) > 0) {
                $data = mysqli_fetch_assoc($res);
                $server_number = $data['maulya'];
            } else {
                $errors[] = "No active Bkash number found for shonu: $shonu";
            }
            break;

        case 'nagad':
            $query = "SELECT upi_id FROM nagad_pay WHERE status = '1' LIMIT 1";
            $res = mysqli_query($conn, $query);
            if ($res && mysqli_num_rows($res) > 0) {
                $data = mysqli_fetch_assoc($res);
                $server_number = $data['upi_id'];
            } else {
                $errors[] = "No active Nagad number found for shonu: $shonu";
            }
            break;

        case 'rocket':
            $query = "SELECT number FROM rocket_payment WHERE status = 'active' LIMIT 1";
            $res = mysqli_query($conn, $query);
            if ($res && mysqli_num_rows($res) > 0) {
                $data = mysqli_fetch_assoc($res);
                $server_number = $data['number'];
            } else {
                $errors[] = "No active Rocket number found for shonu: $shonu";
            }
            break;

        case 'upay':
            $query = "SELECT number FROM upay_payment WHERE status = 'active' LIMIT 1";
            $res = mysqli_query($conn, $query);
            if ($res && mysqli_num_rows($res) > 0) {
                $data = mysqli_fetch_assoc($res);
                $server_number = $data['number'];
            } else {
                $errors[] = "No active Upay number found for shonu: $shonu";
            }
            break;

        case 'usdt':
            $query = "SELECT maulya FROM deyyamrici WHERE sthiti = 1 LIMIT 1";
            $res = mysqli_query($conn, $query);
            if ($res && mysqli_num_rows($res) > 0) {
                $data = mysqli_fetch_assoc($res);
                $server_number = $data['maulya'];
            } else {
                $errors[] = "No active USDT address found for shonu: $shonu";
            }
            break;

        default:
            $server_number = 'N/A';
            $errors[] = "Unknown method '$method' for shonu: $shonu";
            break;
    }

    // Step 3: Update thevani table with fetched server_number
    if (!empty($server_number) && $server_number !== 'N/A') {
        $updateQuery = "UPDATE thevani SET server_number = '" . mysqli_real_escape_string($conn, $server_number) . "' WHERE shonu = '$shonu'";
        $updateResult = mysqli_query($conn, $updateQuery);
        
        if ($updateResult) {
            $updatedCount++;
            error_log("✅ Updated shonu $shonu with $method number: $server_number");
        } else {
            $errors[] = "Failed to update shonu $shonu: " . mysqli_error($conn);
        }
    } else {
        $errors[] = "Empty server number for shonu $shonu (method: $method)";
    }
}

// Step 4: Output for confirmation
echo "<h3>Server Number Update Report</h3>";
echo "✅ Successfully updated: $updatedCount records<br>";

if (!empty($errors)) {
    echo "<br>⚠️ Errors/Warnings:<br>";
    foreach ($errors as $error) {
        echo "- $error<br>";
    }
} else {
    echo "<br>🎉 All records updated successfully!";
}

// Additional: Show current active numbers for reference
echo "<br><br><h4>Current Active Numbers:</h4>";

$methods = [
    'Bkash' => "SELECT maulya FROM deyya WHERE sthiti = 1 LIMIT 1",
    'Nagad' => "SELECT upi_id FROM nagad_pay WHERE status = '1' LIMIT 1", 
    'Rocket' => "SELECT number FROM rocket_payment WHERE status = 'active' LIMIT 1",
    'Upay' => "SELECT number FROM upay_payment WHERE status = 'active' LIMIT 1",
    'USDT' => "SELECT maulya FROM deyyamrici WHERE sthiti = 1 LIMIT 1"
];

foreach ($methods as $methodName => $query) {
    $res = mysqli_query($conn, $query);
    if ($res && mysqli_num_rows($res) > 0) {
        $data = mysqli_fetch_assoc($res);
        $number = $methodName === 'Bkash' || $methodName === 'USDT' ? $data['maulya'] : 
                 ($methodName === 'Nagad' ? $data['upi_id'] : $data['number']);
        echo "• $methodName: $number<br>";
    } else {
        echo "• $methodName: No active number found<br>";
    }
}
?>