<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
}

include("conn.php");

$query = "SELECT rate FROM tbl_pg WHERE value = 'usdt' LIMIT 1";
$result = mysqli_query($conn, $query);
$currentRate = 0;
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $currentRate = $row['rate'];
}

if (isset($_POST['newupi'])) {
    $upiid = mysqli_real_escape_string($conn, $_POST['newupi']);
    $sql_q = "UPDATE tbl_pg SET rate='" . $upiid . "' WHERE value = 'usdt'";
    $chk = mysqli_query($conn, $sql_q);
    if ($chk) {
        echo '<script type="text/JavaScript"> alert("USDT rate updated"); </script>';
        header("Refresh:0");
    } else {
        echo '<script type="text/JavaScript"> alert("USDT rate Update Failed"); </script>';
    }
}
?>

<?php include 'header.php'; ?>

      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-sm-12 mb-4 mb-xl-0">
              <h4 class="font-weight-bold text-dark">Update USDT Rate</h4>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-3 mb-4 mb-xl-0">
              <div class="d-flex align-items-center mb-3">
                
                <input type="text" value="<?php echo htmlspecialchars($currentRate); ?>" class="flex-grow-1 cool-input" style="height: 40px;" readonly />
              </div>
              <form action="#" id="upiform" method="post" autocomplete="off">
                <div class="d-flex align-items-center">
                  <input name="newupi" type="text" placeholder="Enter New USDT Rate" class="flex-grow-1 cool-input" style="height: 40px;" />
                </div>
                <div class="d-flex align-items-center mt-3">
                  <button type="submit" class="btn btn-primary cool-button mr-2">Update</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © admin 2024</span>
          </div>
        </footer>
      </div>
    </div>
  </div>
  <script>
    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.href);
    }
  </script>
</body>

</html>
