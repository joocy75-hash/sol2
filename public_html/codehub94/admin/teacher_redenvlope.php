<?php
// SHOW PHP ERRORS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'conn.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: teacher_login.php?msg=true');
    exit();
}

$teacherId = intval($_SESSION['teacher_id']);
date_default_timezone_set("Asia/Dhaka");

// Calculate bonus balance
$bonusRes = $conn->query("SELECT SUM(amount) as total FROM red_envelope_requests WHERE teacher_id = $teacherId AND status='approved'");
if (!$bonusRes) {
    die("Bonus query failed: " . $conn->error);
}
$bonusRow = $bonusRes->fetch_assoc();
$bonusBalance = $bonusRow['total'] ?? 0;

// Insert new red envelope → status pending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_envelope'])) {
    $userId = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);
    $quantity = intval($_POST['quantity']);
    $remark = $conn->real_escape_string($_POST['remark']);
    $createdAt = date('Y-m-d H:i:s');

    if ($userId > 0 && $amount > 0 && $quantity > 0) {
        $stmt = $conn->prepare("INSERT INTO red_envelope_requests (teacher_id, user_id, amount, quantity, grabbed, status, remark, created_at) VALUES (?, ?, ?, ?, 0, 'pending', ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iiddss", $teacherId, $userId, $amount, $quantity, $remark, $createdAt);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<?php include 'teacher_nav.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Red Envelope Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-pending { background-color: #ffc107; }
        .badge-approved { background-color: #28a745; }
        .badge-rejected { background-color: #dc3545; }
        .table-wrapper { margin-top: 30px; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="my-4">🎁 Teacher Red Envelope Panel</h2>

    <div class="row mb-3 align-items-center">
        <div class="col-md-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Red Envelope Code">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" onclick="searchTable()">Search</button>
            <button class="btn btn-secondary" onclick="resetTable()">Reset</button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newModal">+ New</button>
        </div>
        <div class="col text-end fw-bold text-primary">
            Bonus Balance ৳<?= number_format($bonusBalance, 2) ?>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Red Envelope Code</th>
                <th>User ID</th>
                <th>Amount</th>
                <th>Quantity</th>
                <th>Remaining Qty</th>
                <th>Already Grabbed</th>
                <th>Status</th>
                <th>Remark</th>
                <th>Created At</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $res = $conn->query("SELECT * FROM red_envelope_requests WHERE teacher_id = $teacherId ORDER BY created_at DESC");
            if (!$res) {
                die("Table query failed: " . $conn->error);
            }
            while ($row = $res->fetch_assoc()) {
                $id = $row['id'];
                $code = "RE-$id";
                $userId = $row['user_id'];
                $amount = $row['amount'];
                $qty = $row['quantity'] ?? 1;
                $remaining = $qty - ($row['grabbed'] ?? 0);
                $grabbed = $row['grabbed'] ?? 0;
                $status = $row['status'];
                $remark = htmlspecialchars($row['remark'] ?? '');
                $created = $row['created_at'];

                $badgeClass = $status === 'approved' ? 'badge-approved' :
                              ($status === 'rejected' ? 'badge-rejected' : 'badge-pending');

                echo "<tr>
                        <td>$id</td>
                        <td>$code</td>
                        <td>$userId</td>
                        <td>৳" . number_format($amount, 2) . "</td>
                        <td>$qty</td>
                        <td>$remaining</td>
                        <td>$grabbed</td>
                        <td><span class='badge $badgeClass'>$status</span></td>
                        <td>$remark</td>
                        <td>$created</td>
                      </tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Modal -->
<div class="modal fade" id="newModal" tabindex="-1" aria-labelledby="newModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="">
            <div class="modal-header">
                <h5 class="modal-title" id="newModalLabel">Create New Red Envelope</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="user_id" class="form-label">User ID</label>
                    <input type="number" class="form-control" id="user_id" name="user_id" min="1" required>
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="number" class="form-control" id="amount" name="amount" min="1" required>
                </div>
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                </div>
                <div class="mb-3">
                    <label for="remark" class="form-label">Remark</label>
                    <textarea class="form-control" id="remark" name="remark" rows="2" placeholder="Enter a remark (optional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" name="create_envelope">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    function searchTable() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let rows = document.querySelectorAll('table tbody tr');
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
        });
    }

    function resetTable() {
        document.getElementById('searchInput').value = '';
        searchTable();
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
