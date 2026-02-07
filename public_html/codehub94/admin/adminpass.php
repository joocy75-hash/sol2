<?php
session_start();

if (!isset($_SESSION['unohs']) || $_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
    exit;
}

include("conn.php");

if (isset($_POST['newupi'])) {
    $upiid = mysqli_real_escape_string($conn, $_POST['newupi']);
    $admin_id = $_SESSION['unohs'];

    if (!empty($upiid)) {
        $hashed = md5($upiid);
        $sql_q = "UPDATE nirvahaka_shonu SET guptapada='$hashed' WHERE unohs='$admin_id'";
        $chk = mysqli_query($conn, $sql_q);

        if ($chk) {
            echo '<script>alert("✅ Password Updated Successfully.");</script>';
        } else {
            echo '<script>alert("❌ Failed to update password.");</script>';
        }
    } else {
        echo '<script>alert("⚠️ Password cannot be empty.");</script>';
    }
}
?>

<?php include 'header.php'; ?>

<div class="main-panel">
  <div class="content-wrapper">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="neon-card">
          <h3 class="neon-title">🔐 Update Admin Password</h3>
          <form action="#" id="upiform" method="post" autocomplete="off">
            <div class="form-group mt-4">
              <input name="newupi" type="password" placeholder="Enter New Password" class="neon-input" required />
            </div>
            <div class="text-center mt-3">
              <button type="submit" class="neon-button">Update Password</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>



<style>

/* === Neon Panel Card === */
.neon-card {
  background-color: #0e0e0e;
  border: 2px solid #00eaff;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 0 15px rgba(0, 238, 255, 0.2);
  transition: all 0.3s ease-in-out;
}

.neon-card:hover {
  box-shadow: 0 0 25px rgba(0, 238, 255, 0.7);
}

/* === Title === */
.neon-title {
  font-family: 'Segoe UI', sans-serif;
  text-align: center;
  font-size: 1.8rem;
  color: #00eaff;
  font-weight: bold;
  text-shadow: 0 0 5px #00eaff, 0 0 10px #00eaff;
}

/* === Input Field === */
.neon-input {
  width: 100%;
  padding: 12px 15px;
  background-color: #111;
  border: 1px solid #00eaff;
  border-radius: 6px;
  font-size: 16px;
  color: #00eaff;
  outline: none;
  transition: border-color 0.3s;
}

.neon-input::placeholder {
  color: #888;
}

.neon-input:focus {
  border-color: #00fff7;
  box-shadow: 0 0 10px #00fff7;
}

/* === Submit Button === */
.neon-button {
  padding: 12px 25px;
  background: linear-gradient(to right, #007bff, #00eaff);
  border: none;
  color: #000;
  font-weight: bold;
  border-radius: 6px;
  box-shadow: 0 0 10px #00eaff;
  transition: 0.3s ease;
}

.neon-button:hover {
  background: #00eaff;
  color: #000;
  box-shadow: 0 0 15px #00eaff, 0 0 30px #00eaff;
}

</style>

<script>
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
</script>
</body>
</html>
