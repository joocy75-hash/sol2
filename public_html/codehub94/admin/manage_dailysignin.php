<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");

include 'conn.php';
?>



<?php include 'header.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Signin Rewards</title>

</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-center">🎁 Manage Sign-In Recharge Rewards</h2>

<!-- Add New Reward Form -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">Add New Reward</div>
    <div class="card-body">
        <form method="post" action="">
            <div class="row g-3">
                <div class="col-md-2">
                    <input type="number" name="day" class="form-control" placeholder="Day" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount (INR)" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="bonus" class="form-control" placeholder="Bonus (INR)" required>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100" type="submit" name="add">Add</button>
                </div>
            </div>
        </form>

        <?php
        if (isset($_POST['add'])) {
            echo "<div class='alert alert-warning mt-3'>⚙️ Under Development - Adding Rewards is currently disabled.</div>";
            // Original working code commented for safety
            /*
            $day = mysqli_real_escape_string($conn, $_POST['day']);
            $amount = mysqli_real_escape_string($conn, $_POST['amount']);
            $bonus = mysqli_real_escape_string($conn, $_POST['bonus']);
            $status = mysqli_real_escape_string($conn, $_POST['status']);

            $insert = mysqli_query($conn, "INSERT INTO signin_recharge_rewards (rechargesID, day, amount, bonus, status) VALUES ('$day', '$day', '$amount', '$bonus', '$status')");
            if ($insert) {
                echo "<div class='alert alert-success mt-3'>Reward Added Successfully!</div>";
                echo "<script>setTimeout(function(){ location.href = 'manage_dailysignin.php'; }, 1000);</script>";
            } else {
                echo "<div class='alert alert-danger mt-3'>Error Adding Reward!</div>";
            }
            */
        }
        ?>
    </div>
</div>



    <!-- Table -->
<!-- Reward Card View with Inline Edit -->
<!-- Reward Card View with Toggle Edit -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white">🎁 Reward Card View (Click to Edit)</div>
    <div class="card-body">
        <div class="row text-center">
            <?php
            if (isset($_POST['update'])) {
                $id = $_POST['id'];
                $day = $_POST['day'];
                $amount = $_POST['amount'];
                $bonus = $_POST['bonus'];
                $status = $_POST['status'];
                mysqli_query($conn, "UPDATE signin_recharge_rewards SET day='$day', amount='$amount', bonus='$bonus', status='$status' WHERE id='$id'");
                echo "<script>location.href='manage_dailysignin.php';</script>";
            }

            $query = mysqli_query($conn, "SELECT * FROM signin_recharge_rewards ORDER BY day ASC");
            while ($row = mysqli_fetch_assoc($query)) {
                $statusClass = $row['status'] ? 'bg-success' : 'bg-secondary';
                $cardId = 'editCard' . $row['id'];
                ?>
                <div class="col-md-2 col-6 mb-4">
                    <div class="card shadow" style="border: 2px solid #00aaff; border-radius: 12px;">
                        <div class="card-body p-2 text-center">
                            <div class="fw-bold text-primary">DAY <?php echo $row['day']; ?></div>
                            <img src="https://Sol-0203.com/assets/png/coin-294b6998.png" alt="Gold" class="img-fluid my-2" style="height:40px;">
                            <div class="text-muted small">Min Recharge:</div>
                            <div class="fw-semibold text-dark">&#8377; <?php echo number_format($row['amount']); ?></div>
                            <div class="text-muted small mt-2">Bonus:</div>
                            <div class="fw-semibold text-warning"><?php echo number_format($row['bonus']); ?> GOLD</div>
                            <span class="badge <?php echo $statusClass; ?> mt-2"><?php echo $row['status'] ? 'Active' : 'Inactive'; ?></span>
                            <div class="mt-3 d-flex justify-content-center gap-1">
                                <button type="button" onclick="toggleEdit('<?php echo $cardId; ?>')" class="btn btn-sm btn-outline-warning">Edit</button>
                                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this reward?')" class="btn btn-sm btn-outline-danger">Delete</a>
                            </div>
                        </div>

                        <!-- Hidden Edit Form -->
                        <form method="post" id="<?php echo $cardId; ?>" style="display: none;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <div class="card-footer bg-light p-2">
                                <input type="number" name="day" class="form-control form-control-sm mb-1" value="<?php echo $row['day']; ?>" placeholder="Day">
                                <input type="number" name="amount" class="form-control form-control-sm mb-1" value="<?php echo $row['amount']; ?>" placeholder="Amount">
                                <input type="number" name="bonus" class="form-control form-control-sm mb-1" value="<?php echo $row['bonus']; ?>" placeholder="Bonus">
                                <select name="status" class="form-select form-select-sm mb-2">
                                    <option value="1" <?php if ($row['status']) echo 'selected'; ?>>Active</option>
                                    <option value="0" <?php if (!$row['status']) echo 'selected'; ?>>Inactive</option>
                                </select>
                                <button type="submit" name="update" class="btn btn-sm btn-success w-100">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>

<script>
function toggleEdit(id) {
    var el = document.getElementById(id);
    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
}
</script>





    <!-- User Sign-In Log Table -->
    <div class="card">
        <div class="card-header bg-success text-white">📋 Daily Sign-In Claims (User List)</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-success">
                <tr>
                    <th>User ID</th>
                    <th>Mobile</th>
                    <th>Day</th>
                    <th>Today's Blessing</th>
                    <th>Total Blessings</th>
                    <th>Claim Time</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $signin_log = mysqli_query($conn, "SELECT c.*, u.mobile FROM cihne c LEFT JOIN shonu_subjects u ON c.identity = u.id ORDER BY c.dearlord DESC");
                while ($row = mysqli_fetch_assoc($signin_log)) {
                    echo "<tr>
                        <td>{$row['identity']}</td>
                        <td>{$row['mobile']}</td>
                        <td>{$row['daysonearth']}</td>
                        <td>{$row['todayblessings']}</td>
                        <td>{$row['totalblessings']}</td>
                        <td>{$row['amen']}</td>
                    </tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
