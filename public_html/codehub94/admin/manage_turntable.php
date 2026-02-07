<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");

include 'conn.php';

// Update spin_prizevalue only
if (isset($_POST['update_spin_prize'])) {
    $spin_prizevalue = $_POST['spin_prizevalue'];
    mysqli_query($conn, "UPDATE web_setting SET spin_prizevalue='$spin_prizevalue' WHERE id=1");
    header("Location: ?success=Spin Prize Value Updated");
    exit;
}

// Handle Add/Edit/Delete for rewards
if (isset($_POST['add_or_edit_reward'])) {
    $id = $_POST['id'];
    $target_amount = $_POST['target_amount'];
    $rotate_num = $_POST['rotate_num'];
    $reward_type = $_POST['reward_type'];
    $reward_setting = $_POST['reward_setting'];
    $prize_picture_url = $_POST['prize_picture_url'];

    if ($id == 0) {
        mysqli_query($conn, "INSERT INTO recharge_spin_rewards (target_amount, rotate_num, reward_type, reward_setting, prize_picture_url) VALUES ('$target_amount','$rotate_num','$reward_type','$reward_setting','$prize_picture_url')");
    } else {
        mysqli_query($conn, "UPDATE recharge_spin_rewards SET target_amount='$target_amount', rotate_num='$rotate_num', reward_type='$reward_type', reward_setting='$reward_setting', prize_picture_url='$prize_picture_url' WHERE id='$id'");
    }
    header("Location: ?success=Reward Saved");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM recharge_spin_rewards WHERE id='$id'");
    header("Location: ?success=Reward Deleted");
    exit;
}

$webSetting = mysqli_fetch_assoc(mysqli_query($conn, "SELECT spin_prizevalue FROM web_setting LIMIT 1"));
$rewardResult = mysqli_query($conn, "SELECT * FROM recharge_spin_rewards ORDER BY id DESC");
$editReward = ["id"=>0, "target_amount"=>"", "rotate_num"=>"", "reward_type"=>"", "reward_setting"=>"", "prize_picture_url"=>""];

if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $editReward = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM recharge_spin_rewards WHERE id='$editId'"));
}
?>



<?php include 'header.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Spin Prize & Rewards</title>

  <style>
  body {
  font-family: 'Segoe UI', sans-serif;
  background: #f5f7fa;
  color: #222;
}

.container {
  max-width: 1200px;
  margin: auto;
  padding: 30px 15px;
}

.card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.05);
  margin-bottom: 30px;
  border: 1px solid #eee;
}

.card-header {
  background: #0d6efd;
  color: #fff;
  padding: 12px 20px;
  font-weight: 600;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}

.card-body {
  padding: 20px;
}

input[type=text], input[type=number] {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

label {
  font-weight: 500;
  margin-bottom: 6px;
  display: block;
}

button {
  padding: 10px 18px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.btn-primary {
  background-color: #007bff;
  color: #fff;
}

.btn-success {
  background-color: #28a745;
  color: #fff;
}

.btn-warning {
  background-color: #ffc107;
  color: #000;
}

.btn-danger {
  background-color: #dc3545;
  color: #fff;
}

.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.table th,
.table td {
  padding: 10px 12px;
  border: 1px solid #ddd;
  text-align: center;
}

.table th {
  background: #333;
  color: #fff;
}

    body { background-color: #f4f6f9; }
    .card-header { font-weight: bold; background: #0d6efd; color: #fff; }
    .btn-primary, .btn-success { min-width: 120px; }
    .table img { border-radius: 6px; border: 1px solid #ccc; }
    .form-label { font-weight: 500; }
    
    body {
  background-color: #0d1117;
  color: #e6edf3;
  font-family: 'Segoe UI', sans-serif;
}

h4.text-primary {
  color: #58a6ff !important;
}

/* Card Styling */
.card {
  background-color: #161b22;
  border: 1px solid #30363d;
  border-radius: 8px;
  box-shadow: 0 0 8px rgba(0,0,0,0.2);
}
.card-header {
  background-color: #001d6e;
  color: white;
  font-weight: bold;
}

/* Inputs & Buttons */
input[type="text"],
input[type="number"] {
  background-color: #0d1117;
  border: 1px solid #30363d;
  color: #e6edf3;
  padding: 6px;
  border-radius: 4px;
}

button, .btn {
  border: none;
  padding: 6px 12px;
  font-size: 14px;
  border-radius: 4px;
  cursor: pointer;
}
.btn-primary {
  background-color: #1a73e8;
  color: white;
}
.btn-success {
  background-color: #238636;
  color: white;
}
.btn-warning {
  background-color: #c69000;
  color: white;
}
.btn-danger {
  background-color: #da3633;
  color: white;
}
.btn:hover {
  opacity: 0.9;
}

/* Table */
.table {
  background-color: #0d1117;
  color: #e6edf3;
}
.table-bordered th, .table-bordered td {
  border: 1px solid #30363d;
}
.table-dark {
  background-color: #001d6e;
  color: #ffffff;
}
.table-hover tbody tr:hover {
  background-color: #21262d;
}

/* Alert */
.alert-success {
  background-color: #04260f;
  border: 1px solid #1a7f37;
  color: #58d38e;
}

/* Responsive Tweak */
@media screen and (max-width: 768px) {
  .card-body .row.g-3 {
    display: block;
  }
}
  </style>
</head>
<body>
<div class="container py-4">
  <h4 class="mb-4 text-center text-primary">🎯 Spin Prize Value + Recharge Spin Rewards Management</h4>
  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success text-center">✅ <?= htmlspecialchars($_GET['success']) ?></div>
  <?php endif; ?>

  <!-- Spin Prize Value Update -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header">Spin Prize Value</div>
    <div class="card-body">
      <form method="post">
        <div class="row g-3 align-items-center">
          <div class="col-md-4">
            <label class="form-label">Prize Amount (৳)</label>
            <input type="text" class="form-control" name="spin_prizevalue" value="<?= $webSetting['spin_prizevalue'] ?>">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button name="update_spin_prize" class="btn btn-primary">Update</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Reward Add/Edit Form -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header">Add / Edit Reward</div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="id" value="<?= $editReward['id'] ?>">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Target Amount</label>
            <input type="number" name="target_amount" class="form-control" value="<?= $editReward['target_amount'] ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Rotate</label>
            <input type="number" name="rotate_num" class="form-control" value="<?= $editReward['rotate_num'] ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Type</label>
            <input type="number" name="reward_type" class="form-control" value="<?= $editReward['reward_type'] ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Reward Text</label>
            <input type="text" name="reward_setting" class="form-control" value="<?= $editReward['reward_setting'] ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Prize Image URL</label>
            <input type="text" name="prize_picture_url" class="form-control" value="<?= $editReward['prize_picture_url'] ?>">
          </div>
        </div>
        <button name="add_or_edit_reward" class="btn btn-success mt-3">Save Reward</button>
      </form>
    </div>
  </div>

  <!-- Reward List -->
  <div class="card shadow-sm">
    <div class="card-header">Reward List</div>
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Target</th>
            <th>Rotate</th>
            <th>Type</th>
            <th>Setting</th>
            <th>Image</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($r = mysqli_fetch_assoc($rewardResult)) { ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= $r['target_amount'] ?></td>
            <td><?= $r['rotate_num'] ?></td>
            <td><?= $r['reward_type'] ?></td>
            <td><?= $r['reward_setting'] ?></td>
            <td><img src="<?= $r['prize_picture_url'] ?>" width="40" height="40"></td>
            <td>
              <a href="?edit=<?= $r['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
              <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete?')" class="btn btn-danger btn-sm">Delete</a>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
