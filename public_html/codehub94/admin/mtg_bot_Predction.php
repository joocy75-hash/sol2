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

  

  <style>
  .bg-purple {
    background-color: #6f42c1 !important;
    color: #fff;
  }
</style>






<!-- Prediction History -->
<div class="card shadow-sm mx-3">
  <div class="card-header bg-primary text-white fw-bold fs-5 text-center">
    📈 Prediction History
  </div>
  <div class="card-body">
    <div id="live-predictions" class="d-flex flex-column align-items-center gap-3">
      <!-- AJAX loaded content will appear here -->
 

<!-- Bot Status Form 
<div class="card shadow-sm my-3 mx-3">
  <div class="card-body d-flex justify-content-end align-items-center gap-2">
    <label class="form-label fw-semibold text-muted fs-6 mb-0">🤖 Bot Status:</label>
    <form method="post">
      <select name="bot_status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="ON" <?= ($current_status == 'ON') ? 'selected' : '' ?>>🟢 ON</option>
        <option value="OFF" <?= ($current_status == 'OFF') ? 'selected' : '' ?>>🔴 OFF</option>
      </select>
    </form>
  </div>
</div>-->

<!-- JavaScript -->
<script>
  function loadPredictionHistory() {
    fetch('fetch_prediction_history.php')
      .then(res => res.text())
      .then(data => {
        document.getElementById("live-predictions").innerHTML = data;
      });
  }

  // Initial load + auto refresh every 10s
  loadPredictionHistory();
  setInterval(loadPredictionHistory, 10000);
</script>

  
</html>
