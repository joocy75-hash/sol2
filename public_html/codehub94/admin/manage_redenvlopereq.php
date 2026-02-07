<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
    exit();
}

date_default_timezone_set("Asia/Dhaka");
include("conn.php");

// Approve or reject red envelope
function updateRedEnvelopeStatus($requestId, $action, $conn) {
    $res = $conn->query("SELECT * FROM red_envelope_requests WHERE id = $requestId AND status = 'pending'");
    if (!$res) return "❌ Query error: " . $conn->error;

    if ($row = $res->fetch_assoc()) {
        $userId = $row['user_id'];
        $amount = $row['amount'];
        $createdAt = $row['created_at'];
        $teacherRemark = $row['remark'];  // get teacher remark
        $serial = "AdminApproval";

        if ($action === 'approve') {
            // Insert into bonus table with teacher remark
            $stmt = $conn->prepare("INSERT INTO hodike_balakedara (userkani, price, serial, shonu, remark) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) return "❌ Bonus insert prepare failed: " . $conn->error;
            $stmt->bind_param("idsss", $userId, $amount, $serial, $createdAt, $teacherRemark);
            if (!$stmt->execute()) return "❌ Bonus insert failed: " . $stmt->error;
            $stmt->close();

            // Update wallet
            $conn->query("UPDATE shonu_kaichila SET motta = motta + $amount WHERE balakedara = $userId");

            // Update status only (no change to remark)
            $conn->query("UPDATE red_envelope_requests SET status = 'approved' WHERE id = $requestId");

            return "✅ Request ID $requestId approved and bonus applied.";
        } elseif ($action === 'reject') {
            $conn->query("UPDATE red_envelope_requests SET status = 'rejected' WHERE id = $requestId");
            return "❌ Request ID $requestId rejected.";
        }
    } else {
        return "⚠️ Request ID $requestId not found or already processed.";
    }
    return "";
}

// Handle POST action
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = intval($_POST['request_id']);
    $action = $_POST['action'];

    if ($action === 'approve' || $action === 'reject') {
        $message = updateRedEnvelopeStatus($requestId, $action, $conn);
    }
}
?>

<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Red Envelope Approval</title>
    <style>
        .badge-pending { background-color: #ffc107; }
        .badge-approved { background-color: #28a745; }
        .badge-rejected { background-color: #dc3545; }
        .table-wrapper { margin-top: 30px; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="my-4">👑 Admin Red Envelope Approval Panel</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Amount</th>
                <th>Quantity</th>
                <th>Grabbed</th>
                <th>Status</th>
                <th>Teacher Remark</th>
                <th>Teacher ID</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $res = $conn->query("SELECT * FROM red_envelope_requests ORDER BY created_at DESC");
            while ($row = $res->fetch_assoc()) {
                $id = $row['id'];
                $userId = $row['user_id'];
                $amount = $row['amount'];
                $quantity = $row['quantity'];
                $grabbed = $row['grabbed'];
                $teacherId = $row['teacher_id'];
                $status = $row['status'];
                $remark = htmlspecialchars($row['remark']); // teacher remark
                $created = $row['created_at'];

                $badgeClass = $status === 'approved' ? 'badge-approved' :
                              ($status === 'rejected' ? 'badge-rejected' : 'badge-pending');

                echo "<tr>
                        <td>$id</td>
                        <td>$userId</td>
                        <td>৳" . number_format($amount, 2) . "</td>
                        <td>$quantity</td>
                        <td>$grabbed</td>
                        <td><span class='badge $badgeClass'>$status</span></td>
                        <td>$remark</td>
                        <td>$teacherId</td>
                        <td>$created</td>
                        <td>";

                if ($status === 'pending') {
                    echo "<form method='POST' style='display:inline; margin-right:5px;'>
                            <input type='hidden' name='request_id' value='$id'>
                            <input type='hidden' name='action' value='approve'>
                            <button type='submit' class='btn btn-success btn-sm'>Approve</button>
                          </form>
                          <form method='POST' style='display:inline;'>
                            <input type='hidden' name='request_id' value='$id'>
                            <input type='hidden' name='action' value='reject'>
                            <button type='submit' class='btn btn-danger btn-sm'>Reject</button>
                          </form>";
                } else {
                    echo "<button class='btn btn-secondary btn-sm' disabled>$status</button>";
                }

                echo "</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
