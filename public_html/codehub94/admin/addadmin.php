<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}

include("conn.php");


// ----------------------------------------------------------
// ✅ DELETE ADMIN
// ----------------------------------------------------------
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);

    mysqli_query($conn, "DELETE FROM admin_roles WHERE admin_id = '$deleteId'");
    mysqli_query($conn, "DELETE FROM nirvahaka_shonu WHERE unohs = '$deleteId'");

    echo '<script>alert("Admin Deleted Successfully"); location.href="' . $_SERVER['PHP_SELF'] . '";</script>';
    exit;
}



// ----------------------------------------------------------
// ✅ ADD ADMIN FIXED LOGIC
// ----------------------------------------------------------
if (isset($_POST['serial']) && isset($_POST['maxusers'])) {

    $username = mysqli_real_escape_string($conn, $_POST['serial']);
    $password = mysqli_real_escape_string($conn, $_POST['maxusers']);  // correct field

    // Check duplicate username
    $chk = mysqli_query($conn, "SELECT 1 FROM nirvahaka_shonu WHERE nirvahaka_hesaru='$username'");
    if (mysqli_num_rows($chk) > 0) {
        echo '<script>alert("Username Already Exists");</script>';
        exit;
    }

    // Insert new admin
    $status = 1;
    $passHash = md5($password);

    $sql = "INSERT INTO nirvahaka_shonu (hesaru, nirvahaka_hesaru, guptapada, sthiti)
            VALUES ('$username', '$username', '$passHash', '$status')";
    $insert = mysqli_query($conn, $sql);

    if ($insert) {
        $adminId = mysqli_insert_id($conn);

        // Insert roles
        $roles    = $_POST['roles'] ?? [];
        $subroles = $_POST['subroles'] ?? [];

        foreach ($roles as $module => $v) {
            $moduleSafe = mysqli_real_escape_string($conn, $module);

            // Insert main module access
            mysqli_query($conn,
                "INSERT INTO admin_roles(admin_id,module,submodule,allowed,created_at)
                 VALUES ('$adminId','$moduleSafe','',1,NOW())"
            );

            // Insert submodules
            if (!empty($subroles[$module])) {
                foreach ($subroles[$module] as $sub => $val) {
                    $subSafe = mysqli_real_escape_string($conn, $sub);
                    mysqli_query($conn,
                        "INSERT INTO admin_roles(admin_id,module,submodule,allowed,created_at)
                         VALUES ('$adminId','$moduleSafe','$subSafe',1,NOW())"
                    );
                }
            }
        }

        echo '<script>alert("Admin Added Successfully"); location.href="' . $_SERVER['PHP_SELF'] . '";</script>';
    }
    else {
        echo '<script>alert("Failed to Add Admin");</script>';
    }
}

?>

<?php include 'header.php'; ?>

<div class="main-panel">
  <div class="content-wrapper">

    <h4 class="font-weight-bold text-dark">Add Admin</h4>

    <form method="post" autocomplete="off">

        <input name="serial" type="text" placeholder="Enter Username"
               required class="cool-input" style="height:40px;">
        <br><br>

        <input name="maxusers" type="text" placeholder="Enter Password"
               required class="cool-input" style="height:40px;">
        <br><br>

        <?php
        // Fetch modules & submodules
        $res = mysqli_query($conn, "SELECT module, submodule FROM navigation_structure ORDER BY module");

        $modules = [];
        while ($r = mysqli_fetch_assoc($res)) {
            $modules[$r['module']][] = $r['submodule'];
        }

        foreach ($modules as $module => $subs) {
            $id = strtolower(str_replace(" ", "_", $module));

            echo "
            <input type='checkbox' name='roles[$module]' onclick=\"toggleSub('$id')\">
            <label>$module</label>

            <div id='subs-$id' style='display:none;margin-left:20px;'>
            ";

            foreach ($subs as $s) {
                if ($s != "") {
                    echo "<input type='checkbox' name='subroles[$module][$s]' value='1'> $s <br>";
                }
            }

            echo "</div><br>";
        }
        ?>

        <button type="submit" class="btn btn-primary cool-button">Add</button>

    </form>



<!-- TABLE -->
<h4 class="font-weight-bold text-dark mt-5">All Admins</h4>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>ID</th>
      <th>Username</th>

      <?php
      $mn = mysqli_query($conn, "SELECT DISTINCT module FROM navigation_structure");
      $mods = [];

      while ($m = mysqli_fetch_assoc($mn)) {
          echo "<th>{$m['module']}</th>";
          $mods[] = $m['module'];
      }
      ?>

      <th>Action</th>
    </tr>
  </thead>

  <tbody>
  <?php
  $adminRes = mysqli_query($conn, "SELECT * FROM nirvahaka_shonu ORDER BY unohs DESC");

  while ($a = mysqli_fetch_assoc($adminRes)) {

      echo "<tr>
        <td>{$a['unohs']}</td>
        <td>{$a['nirvahaka_hesaru']}</td>";

      foreach ($mods as $m) {
          $ch = mysqli_query($conn, "SELECT 1 FROM admin_roles
                                     WHERE admin_id='{$a['unohs']}' AND module='$m' LIMIT 1");

          echo "<td>" . (mysqli_num_rows($ch) ? "✅" : "❌") . "</td>";
      }

      echo "<td>
              <a href='?delete={$a['unohs']}' onclick=\"return confirm('Delete?')\" 
                 class='btn btn-danger btn-sm'>Delete</a>
            </td>
      </tr>";
  }
  ?>
  </tbody>
</table>

</div>
</div>


<script>
function toggleSub(id) {
    const box = document.getElementById('subs-' + id);
    box.style.display = box.style.display === 'block' ? 'none' : 'block';
}
</script>
