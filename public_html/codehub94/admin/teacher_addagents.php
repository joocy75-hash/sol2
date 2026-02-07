<?php
session_start();
include("conn.php");

// 🔐 Check login
if (!isset($_SESSION['teacher_id'])) {
    header("location:teacher_login.php?msg=unauthorized");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$message = '';
$message_type = 'info';

// ➕ Add Agent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userid'])) {
    $userid = mysqli_real_escape_string($conn, $_POST['userid']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $createdate = date("Y-m-d H:i:s");

    // Check if agent exists under any teacher
    $check = mysqli_query($conn, "SELECT * FROM tb_agent WHERE userid='$userid'");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        if ($row['teacherid'] != $teacher_id) {
            $_SESSION['message'] = "⚠️ This ID is not under your line (belongs to teacher ID {$row['teacherid']})";
            $_SESSION['message_type'] = 'warning';
        } else {
            $_SESSION['message'] = "⚠️ Agent already exists under you.";
            $_SESSION['message_type'] = 'warning';
        }
    } else {
        $insert = mysqli_query($conn, "
            INSERT INTO tb_agent (userid, mobile, salary, salarypercent, type, createdate, status, teacherid)
            VALUES ('$userid', '$mobile', 0, 0, 'day', '$createdate', 1, '$teacher_id')
        ");
        $_SESSION['message'] = $insert ? "✅ Agent Added Successfully." : "❌ Failed to add agent.";
        $_SESSION['message_type'] = $insert ? 'success' : 'danger';
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 🗑 Delete Agent
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM tb_agent WHERE id='$del_id' AND teacherid='$teacher_id'");
    $_SESSION['message'] = "🗑️ Agent deleted successfully.";
    $_SESSION['message_type'] = 'danger';
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 📥 Fetch Agents
$agents = mysqli_query($conn, "SELECT * FROM tb_agent WHERE teacherid='$teacher_id' ORDER BY id DESC");

// Load session message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Add Agents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-4">
    <h3><span class="material-icons text-primary">group_add</span> Teacher Add Agents</h3>
    <p><b><span class="material-icons">badge</span> Teacher ID:</b> <?= $teacher_id ?></p>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> d-flex align-items-center" role="alert">
            <span class="material-icons me-2"><?= $message_type == 'success' ? 'check_circle' : ($message_type == 'warning' ? 'warning' : 'error') ?></span>
            <div><?= $message ?></div>
        </div>
    <?php endif; ?>

    <!-- ➕ Add Agent Form -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label"><span class="material-icons">person</span> User ID</label>
            <input type="number" name="userid" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label"><span class="material-icons">phone</span> Mobile</label>
            <input type="text" name="mobile" class="form-control" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><span class="material-icons">add</span> Add Agent</button>
        </div>
    </form>

    <!-- 📋 Agents Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Mobile</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($agents) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($agents)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['userid'] ?></td>
                            <td><?= $row['mobile'] ?></td>
                            <td><?= $row['status'] == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                            <td><?= $row['createdate'] ?></td>
                            <td>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this agent?')">
                                    <span class="material-icons">delete</span>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No agents found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'teacher_nav.php'; ?>
</body>
</html>
