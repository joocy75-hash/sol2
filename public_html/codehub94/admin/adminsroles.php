<?php
ob_start(); // Start output buffering
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

// ✅ Handle form submission (Add Role)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['toggle_action'])) {
    $module = $_POST['module'];
    $submodule = $_POST['submodule'];
    $admin_ids = isset($_POST['admin_ids']) ? $_POST['admin_ids'] : [];

    if (empty($admin_ids)) {
        $message = '<div class="alert alert-danger">⚠️ Please select at least one admin.</div>';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO navigation_structure (module, submodule) VALUES (?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $module, $submodule);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $stmt2 = mysqli_prepare($conn, "INSERT INTO admin_roles (admin_id, module, submodule, allowed, created_at) VALUES (?, ?, ?, 1, NOW())");
            if ($stmt2) {
                foreach ($admin_ids as $admin_id) {
                    mysqli_stmt_bind_param($stmt2, "iss", $admin_id, $module, $submodule);
                    mysqli_stmt_execute($stmt2);
                }
                mysqli_stmt_close($stmt2);
                $message = '<div class="alert alert-success">✅ Navigation role and permissions added successfully!</div>';
                header("Refresh:0");
                exit;
            } else {
                $message = '<div class="alert alert-danger">❌ Error preparing admin_roles insert: ' . mysqli_error($conn) . '</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">❌ Error preparing navigation_structure insert: ' . mysqli_error($conn) . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Navigation Role</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
    
    body {
  background-color: #0c0c0c;
  color: #e0f7fa;
  font-family: 'Segoe UI', sans-serif;
}

h2, h4 {
  color: #00eaff;
  text-shadow: 0 0 5px #00eaff;
}

.card {
  background-color: #fff;
  border: 1px solid #00eaff;
  box-shadow: 0 0 10px rgba(0, 238, 255, 0.3);
  border-radius: 10px;
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 0 20px rgba(0, 238, 255, 0.6);
}

label.form-label {
  color: #00eaff;
  font-weight: 500;
}

.form-control {
  background-color: #fff;
  border: 1px solid #00eaff;
  color: #00eaff;
  border-radius: 6px;
}

.form-control::placeholder {
  color: #777;
}

.form-control:focus {
  background-color: #0e0e0e;
  border-color: #00fff7;
  box-shadow: 0 0 10px #00fff7;
}

.form-check-input {
  background-color: #222;
  border: 1px solid #00eaff;
}

.form-check-input:checked {
  background-color: #00eaff;
  box-shadow: 0 0 5px #00eaff;
}

.form-check-label {
  color: #000;
}

.btn-primary {
  background: linear-gradient(90deg, #007bff, #00eaff);
  border: none;
  color: #000;
  font-weight: bold;
  border-radius: 6px;
  box-shadow: 0 0 10px #00eaff;
  transition: 0.3s ease;
}

.btn-primary:hover {
  background: #00eaff;
  color: #000;
  box-shadow: 0 0 15px #00eaff, 0 0 30px #00eaff;
}

/* Table Styling */
.table {
  background-color: #fff;
  color: #00eaff;
  border: 1px solid #00eaff;
}

.table thead th {
  background-color: #0e0e0e;
  color: #00eaff;
  border-bottom: 1px solid #00eaff;
  text-align: center;
}

.table-bordered td, .table-bordered th {
  border: 1px solid #00eaff;
}

.text-danger {
  color: #ff5f5f !important;
}

    
        body { background-color: #f8f9fa; }
        .badge-custom {
            background-color: #e3f2fd;
            color: #1565c0;
            margin: 2px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        .material-icons {
            vertical-align: middle;
            font-size: 20px;
        }
        .tick { color: #4caf50; }
        .cross { color: #f44336; }
        table thead { background-color: #1976d2; color: #fff; }
        table tbody tr:hover { background-color: #f1f1f1; }
        h2, h4 { color: #1976d2; }
        .toggle-btn {
            border: none;
            background-color: transparent;
            font-size: 12px;
            color: #007bff;
            cursor: pointer;
        }
        
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Add Navigation Role</h2>
    <?php echo $message; ?>
    <?php echo $adminError; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Module Name</label>
                    <input type="text" name="module" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Submodule Name (file name)</label>
                    <input type="text" name="submodule" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assign to Admins</label>
                    <div class="row">
                        <?php
                        if (is_array($admins) && count($admins)) {
                            foreach ($admins as $admin) {
                                $admin_id = htmlspecialchars($admin['unohs']);
                                $admin_name = htmlspecialchars($admin['hesaru']);
                                echo '<div class="col-md-4 mb-2">';
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="checkbox" name="admin_ids[]" value="' . $admin_id . '" id="admin' . $admin_id . '">';
                                echo '<label class="form-check-label" for="admin' . $admin_id . '">' . $admin_name . '</label>';
                                echo '</div></div>';
                            }
                        } else {
                            echo "<p class='text-danger'>⚠️ No admins found or error in DB connection.</p>";
                        }
                        ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Navigation Role</button>
            </form>
        </div>
    </div>

    <h4 class="mb-3">Existing Admin Roles (with DB columns)</h4>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Admin Name</th>
                    <?php foreach ($columns as $col) echo "<th class='text-center'>$col</th>"; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if (is_array($admins) && count($admins)) {
                    foreach ($admins as $admin) {
                        $admin_id = $admin['unohs'];
                        $admin_name = $admin['hesaru'];
                        echo '<tr>';
                        echo '<td><strong>' . htmlspecialchars($admin_name) . '</strong></td>';

                        foreach ($columns as $col) {
                            echo '<td class="text-center">';
                            echo ($admin[$col] == 1)
                                ? '<span class="material-icons tick">check_circle</span>'
                                : '<span class="material-icons cross">cancel</span>';

                            $moduleRoles = [];
                            if (isset($adminRoles[$admin_id])) {
                                foreach ($adminRoles[$admin_id] as $role) {
                                    $mod = strtolower($role['module']);
                                    $sub = $role['submodule'];
                                    $allowed = $role['allowed'];
                                    $moduleRoles[$mod][] = [
                                        'sub' => $sub,
                                        'allowed' => $allowed,
                                        'raw_module' => $role['module']
                                    ];
                                }
                            }

                            $moduleKey = str_replace('manager', '', $col);
                            if (isset($moduleRoles[$moduleKey])) {
                                echo '<div class="mt-2 text-start">';
                                foreach ($moduleRoles[$moduleKey] as $r) {
                                    $status = $r['allowed'] == 1 ? 'Enabled' : 'Disabled';
                                    $buttonClass = $r['allowed'] == 1 ? 'btn-success' : 'btn-secondary';
                                    echo '<div class="mb-1 d-flex align-items-center justify-content-between">';
                                    echo '<span class="badge badge-custom me-1">' . htmlspecialchars($moduleKey . ' / ' . $r['sub']) . '</span>';
                                    echo '<form method="POST" class="d-inline">';
                                    echo '<input type="hidden" name="toggle_action" value="1">';
                                    echo '<input type="hidden" name="toggle_admin_id" value="' . $admin_id . '">';
                                    echo '<input type="hidden" name="toggle_module" value="' . $r['raw_module'] . '">';
                                    echo '<input type="hidden" name="toggle_submodule" value="' . $r['sub'] . '">';
                                    echo '<input type="hidden" name="new_status" value="' . ($r['allowed'] ? '0' : '1') . '">';
                                    echo '<button type="submit" class="btn btn-sm ' . $buttonClass . '">' . $status . '</button>';
                                    echo '</form>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }

                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                } else {
                    echo "<tr><td colspan='" . (count($columns) + 1) . "' class='text-center text-danger'>⚠️ No admin data to display.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

<?php ob_end_flush(); ?>
