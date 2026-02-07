<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
    exit;
}
include("conn.php");

$msg = '';

function todaySum($conn, $table, $column, $userids) {
    $date = date('Y-m-d');
    if (empty($userids)) return 0;
    $ids = implode(',', $userids);
    $res = mysqli_query($conn, "SELECT SUM($column) FROM $table WHERE balakedara IN ($ids) AND DATE(dinankavannuracisi) = '$date'");
    $row = mysqli_fetch_row($res);
    return $row[0] ?? 0;
}

// Add Teacher
if (isset($_POST['add_teacher'])) {
    $user_id = intval($_POST['user_id']);
    $user_check = mysqli_query($conn, "SELECT * FROM shonu_subjects WHERE id='$user_id'");
    if (mysqli_num_rows($user_check) == 0) {
        $msg = "error|❌ User ID not found!";
    } else {
        $teacher_check = mysqli_query($conn, "SELECT * FROM teacher_profile WHERE user_id='$user_id'");
        if (mysqli_num_rows($teacher_check) > 0) {
            $msg = "warning|⚠️ This user is already a teacher!";
        } else {
            $balance_q = mysqli_query($conn, "SELECT motta FROM shonu_kaichila WHERE balakedara='$user_id'");
            $balance_row = mysqli_fetch_assoc($balance_q);
            $balance = $balance_row ? $balance_row['motta'] : 0;
            $createdate = date('Y-m-d H:i:s');

            $owncode_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT owncode FROM shonu_subjects WHERE id='$user_id'"));
            $teacher_code = $owncode_row['owncode'];

            $insert = mysqli_query($conn, "INSERT INTO teacher_profile (user_id, teacher_code, commission_percent, total_agents, total_balance, created_at)
            VALUES ('$user_id', '$teacher_code', 10.00, 0, '$balance', '$createdate')");

            if ($insert) {
                $msg = "success|✅ User ID $user_id is now a teacher!";
            } else {
                $mysql_error = mysqli_error($conn);
                $msg = "error|❌ Insert failed: $mysql_error";
            }
        }
    }
    header("Location: manage_teachers.php?msg=$msg");
    exit;
}

// Delete Teacher
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM teacher_profile WHERE user_id='$id'");
    header("Location: manage_teachers.php?msg=success|✅ Teacher deleted!");
    exit;
}

$alert = '';
if (isset($_GET['msg'])) {
    list($type, $text) = explode('|', $_GET['msg'], 2);
    $alert_class = $type == 'success' ? 'success' : ($type == 'warning' ? 'warning' : 'danger');
    $alert = "<div class='alert alert-$alert_class'>$text</div>";
}

$teachers = mysqli_query($conn, "
    SELECT s.id, s.mobile, s.email, s.owncode, t.commission_percent, IFNULL(k.motta, 0) AS motta, t.total_agents, s.createdate
    FROM teacher_profile t
    JOIN shonu_subjects s ON t.user_id = s.id
    LEFT JOIN shonu_kaichila k ON s.id = k.balakedara
    ORDER BY s.id DESC
");

$total_teachers = mysqli_num_rows($teachers);
$total_agents = 0;
$total_balance = 0;
$teacher_data = [];

mysqli_data_seek($teachers, 0);
while ($row = mysqli_fetch_assoc($teachers)) {
    $teacher_id = $row['id'];
    $agent_ids = [];
    $res_agents = mysqli_query($conn, "SELECT userid FROM tb_agent WHERE teacherid='$teacher_id'");
    while ($agent = mysqli_fetch_assoc($res_agents)) {
        $agent_ids[] = $agent['userid'];
    }

    $downline_userids = [];
    if (!empty($agent_ids)) {
        $ids_str = implode(",", $agent_ids);
        $res_owncodes = mysqli_query($conn, "SELECT owncode FROM shonu_subjects WHERE id IN ($ids_str)");
        $owncodes = [];
        while ($oc = mysqli_fetch_assoc($res_owncodes)) {
            $owncodes[] = "'" . $oc['owncode'] . "'";
        }

        if (!empty($owncodes)) {
            $owncodes_str = implode(",", $owncodes);
            $res_downline = mysqli_query($conn, "
                SELECT id FROM shonu_subjects
                WHERE code IN ($owncodes_str)
                   OR code1 IN ($owncodes_str)
                   OR code2 IN ($owncodes_str)
                   OR code3 IN ($owncodes_str)
                   OR code4 IN ($owncodes_str)
                   OR code5 IN ($owncodes_str)
            ");
            while ($u = mysqli_fetch_assoc($res_downline)) {
                $downline_userids[] = $u['id'];
            }
        }
    }

    $today_recharge = $downline_userids ? todaySum($conn, 'thevani', 'motta', $downline_userids) : 0;
    $today_withdrawal = $downline_userids ? todaySum($conn, 'hintegedukolli', 'motta', $downline_userids) : 0;
    $total_users = count($downline_userids);

    $teacher_data[] = [
        'row' => $row,
        'today_recharge' => $today_recharge,
        'today_withdrawal' => $today_withdrawal,
        'total_users' => $total_users
    ];

    $total_agents += $row['total_agents'];
    $total_balance += $row['motta'];
}
?>



<?php include 'header.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Teachers</title>
    <style>
        .info-box { display: flex; align-items: center; padding: 15px; border-radius: 8px; color: white; margin-bottom: 15px; }
        .info-box .material-icons { font-size: 36px; margin-right: 15px; }
        .bg-teacher { background-color: #007bff; }
        .bg-agent { background-color: #28a745; }
        .bg-balance { background-color: #ffc107; color: #333; }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="mb-4 text-center">Teacher Management Dashboard</h3>

    <div class="row">
        <div class="col-md-4"><div class="info-box bg-teacher"><span class="material-icons">person</span><div><h5>Total Teachers</h5><h4><?= $total_teachers ?></h4></div></div></div>
        <div class="col-md-4"><div class="info-box bg-agent"><span class="material-icons">group</span><div><h5>Total Teacher Agents</h5><h4><?= $total_agents ?></h4></div></div></div>
        <div class="col-md-4"><div class="info-box bg-balance"><span class="material-icons">account_balance_wallet</span><div><h5>Total Balance</h5><h4>৳ <?= number_format($total_balance, 2) ?></h4></div></div></div>
    </div>

    <div class="card shadow-lg mb-4">
        <div class="card-header bg-primary text-white"><h4 style="color: black;">Give Teacher Access to Existing User</h4></div>
        <div class="card-body"><?= $alert ?><form method="POST">
                <input type="hidden" name="add_teacher" value="1">
                <div class="form-group"><input type="number" name="user_id" class="form-control" placeholder="Enter User ID" required></div>
                <button type="submit" class="btn btn-success btn-block mt-2">Make Teacher</button>
            </form>
        </div>
    </div>

    <div class="card shadow-lg">
        <div class="card-header bg-secondary text-white"><h4 style="color: black;">Teacher List + Agent Downline Summary (Today)</h4></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                <tr>
                    <th>User ID</th><th>Mobile</th><th>Email</th><th>Owncode</th><th>Referral Link</th>
                    <th>Balance</th><th>Today Recharge</th><th>Today Withdrawal</th>
                    <th>Total Downline Users</th><th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($total_teachers > 0) {
                    foreach ($teacher_data as $item) {
                        $row = $item['row']; ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['mobile'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['owncode'] ?></td>
                            <td><a href="https://Sol-0203.com/#/register?invitationCode=<?= $row['owncode'] ?>" target="_blank">link</a></td>
                            <td>৳ <?= number_format($row['motta'], 2) ?></td>
                            <td>৳ <?= number_format($item['today_recharge'], 2) ?></td>
                            <td>৳ <?= number_format($item['today_withdrawal'], 2) ?></td>
                            <td><?= $item['total_users'] ?></td>
                            <td><a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this teacher?')">Delete</a></td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr><td colspan="10" class="text-center">No teachers found.</td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
