<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['unohs']) || $_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
    exit;
}

include("conn.php");
include 'header.php'; 

$message = '';
$adminError = '';
$admins = [];
$columns = ['dashboard', 'wingomanager', 'k3manager', '5dmanager', 'finance', 'support', 'managegame', 'manageagent', 'setting', 'agents', 'marketing', 'admins', 'teachers', 'developers'];

// ✅ Handle toggle action
if (isset($_POST['toggle_action'])) {
    $toggle_admin_id = $_POST['toggle_admin_id'];
    $toggle_module = $_POST['toggle_module'];
    $toggle_submodule = $_POST['toggle_submodule'];
    $new_status = $_POST['new_status'];

    $stmt = mysqli_prepare($conn, "UPDATE admin_roles SET allowed = ? WHERE admin_id = ? AND module = ? AND submodule = ?");
    mysqli_stmt_bind_param($stmt, "iiss", $new_status, $toggle_admin_id, $toggle_module, $toggle_submodule);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// ✅ Handle edit submodule name
if (isset($_POST['edit_action'])) {
    $edit_admin_id = $_POST['edit_admin_id'];
    $edit_module = $_POST['edit_module'];
    $old_submodule = $_POST['old_submodule'];
    $new_submodule = $_POST['new_submodule'];

    $stmt = mysqli_prepare($conn, "UPDATE admin_roles SET submodule = ? WHERE admin_id = ? AND module = ? AND submodule = ?");
    mysqli_stmt_bind_param($stmt, "siss", $new_submodule, $edit_admin_id, $edit_module, $old_submodule);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// ✅ Fetch admins
$result = mysqli_query($conn, "SELECT * FROM nirvahaka_shonu");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $admins[] = $row;
    }
    if (count($admins) === 0) {
        $adminError = '<div class="alert alert-warning">⚠️ No admins found in the table <b>nirvahaka_shonu</b>.</div>';
    }
} else {
    $adminError = '<div class="alert alert-danger">❌ Error fetching admins: ' . mysqli_error($conn) . '</div>';
}

// ✅ Fetch admin roles
$adminRoles = [];
$roleResult = mysqli_query($conn, "SELECT admin_id, module, submodule, allowed FROM admin_roles");
if ($roleResult) {
    while ($row = mysqli_fetch_assoc($roleResult)) {
        $adminRoles[$row['admin_id']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Role Management</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .badge-custom { background: #e3f2fd; color: #1565c0; padding: 4px 8px; border-radius: 20px; display: inline-block; }
        .tick { color: #4caf50; }
        .cross { color: #f44336; }
        .btn-sm { font-size: 0.7rem; margin-left: 4px; }
        .form-inline input[type=text] { width: auto; display: inline-block; }
    </style>
</head>
<body>
<div class="container">
    <h2>Existing Admin Roles (with DB columns)</h2>
    <?php echo $adminError; ?>
    <table border="1" width="100%" cellpadding="8">
        <thead>
            <tr>
                <th>Admin Name</th>
                <?php foreach ($columns as $col) echo "<th>$col</th>"; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $admin): ?>
                <?php $admin_id = $admin['unohs']; ?>
                <tr>
                    <td><strong><?= htmlspecialchars($admin['hesaru']) ?></strong></td>
                    <?php foreach ($columns as $col): ?>
                        <td>
                            <?= ($admin[$col] == 1) ? '<span class="material-icons tick">check_circle</span>' : '<span class="material-icons cross">cancel</span>' ?>
                            <?php
                            $moduleKey = str_replace('manager', '', $col);
                            if (isset($adminRoles[$admin_id])) {
                                foreach ($adminRoles[$admin_id] as $role) {
                                    $mod = strtolower($role['module']);
                                    if ($mod == $moduleKey) {
                                        $sub = $role['submodule'];
                                        $allowed = $role['allowed'];
                            ?>
                            <div class="mt-2">
                                <form method="POST" class="form-inline d-inline">
                                    <span class="badge-custom"><?= htmlspecialchars($mod) ?> / </span>
                                    <input type="hidden" name="edit_action" value="1">
                                    <input type="hidden" name="edit_admin_id" value="<?= $admin_id ?>">
                                    <input type="hidden" name="edit_module" value="<?= $role['module'] ?>">
                                    <input type="hidden" name="old_submodule" value="<?= $sub ?>">
                                    <input type="text" name="new_submodule" value="<?= htmlspecialchars($sub) ?>">
                                    <button class="btn btn-sm btn-info">Save</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="toggle_action" value="1">
                                    <input type="hidden" name="toggle_admin_id" value="<?= $admin_id ?>">
                                    <input type="hidden" name="toggle_module" value="<?= $role['module'] ?>">
                                    <input type="hidden" name="toggle_submodule" value="<?= $sub ?>">
                                    <input type="hidden" name="new_status" value="<?= $allowed ? 0 : 1 ?>">
                                    <button class="btn btn-sm <?= $allowed ? 'btn-success' : 'btn-secondary' ?>">
                                        <?= $allowed ? 'Enabled' : 'Disabled' ?>
                                    </button>
                                </form>
                            </div>
                            <?php } } } ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
