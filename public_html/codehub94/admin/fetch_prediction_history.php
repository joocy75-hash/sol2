<?php
include("conn.php");

$query = "SELECT * FROM bot_prediction_history ORDER BY id DESC LIMIT 50";
$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_assoc($result)) {
?>
  <div class="w-100 p-3 mb-2 rounded shadow-sm bg-white border d-flex flex-wrap justify-content-between align-items-center" style="max-width: 1200px;">
    <div><strong>#<?= $row['id'] ?></strong></div>
    <div><strong>🆔 Period ID:</strong> <?= $row['period_id'] ?></div>
    <div><strong>🔢 Number:</strong> <?= $row['prediction_number'] ?></div>
    <div><strong>🎨 Color:</strong>
      <?php
        $colors = explode(',', $row['prediction_color']);
        foreach ($colors as $color) {
          $color = strtolower(trim($color));
          if ($color == 'green') {
            $badgeClass = 'success';
          } elseif ($color == 'red') {
            $badgeClass = 'danger';
          } elseif ($color == 'violet') {
            $badgeClass = 'purple';
          } else {
            $badgeClass = 'secondary';
          }
          echo "<span class='badge bg-$badgeClass me-1'>" . ucfirst($color) . "</span>";
        }
      ?>
    </div>
    <div><strong>📦 Type:</strong> <?= ucfirst($row['type']) ?></div>
    <div><strong>💰 Amount:</strong> ৳<?= $row['amount'] ?></div>
    <div><strong>📌 Status:</strong>
      <span class="badge bg-<?= ($row['status'] == 'win') ? 'success' : (($row['status'] == 'fail') ? 'danger' : 'secondary') ?>">
        <?= ucfirst($row['status']) ?>
      </span>
    </div>
    <div><strong>📶 Step:</strong> <?= $row['step'] ?></div>
    <div><strong>🕒 Time:</strong> <?= $row['created_at'] ?></div>
  </div>
<?php
}
?>
