<?php
include("conn.php");

// Fetch all admins
$admins = mysqli_query($conn, "SELECT unohs, hesaru FROM nirvahaka_shonu");

// Define role structure
$roles = [
  'dashboard' => [],
  'wingo' => ['30s','1min','3min','5min'],
  'k3' => ['1min','3min','5min','10min'],
  '5d' => ['1min','3min','5min','10min'],
  'admin' => ['password','addadmin'],
  'finance' => ['addupi','usdtkids','addusdt','addupiimg','addimgusdt_2','deposit_update','manage_withdraw','withdraw_accept_list','withdraw_reject_list','update_with_pay'],
  'support' => ['deposite','withprob','ifscm','bankm','gamep'],
  'managegame' => ['userbonus','manage_bankcard','autobanuser','banbybet','manage_user','addgiftcode','demouser'],
  'setting' => ['update_banner','websetting','update_commission','update_firstdepositbonus','update_invitebonus','manage_paymentgateway','mtg_bot_Predction','manage_walletuser','update_activitybanner','manage_custom_activitybanner','manage_site_message','manage_webmessage'],
  'marketing' => ['user_subtree','users_trunover','manage_turntable','manage_dailysignin','osscontens','manage_rank'],
  'agents' => ['agentuser','agents_management','fetch_agent_recharge'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = intval($_POST['admin_id']);
    $selected = $_POST['roles'] ?? [];

    mysqli_query($conn, "DELETE FROM admin_roles WHERE admin_id = $admin_id");

    foreach ($selected as $role) {
        list($module, $submodule) = explode("|", $role);
        $module = mysqli_real_escape_string($conn, $module);
        $submodule = mysqli_real_escape_string($conn, $submodule);
        mysqli_query($conn, "INSERT INTO admin_roles (admin_id, module, submodule, allowed) VALUES ($admin_id, '$module', '$submodule', 1)");
    }
    echo '<div class="alert alert-success text-center mt-3">✅ Roles updated successfully!</div>';
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Admin Roles</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: #f8f9fa;
    }
    .form-check-label {
      text-transform: capitalize;
    }
    .accordion-button::after {
      font-family: 'FontAwesome';
      content: "\f107";
      transition: transform 0.3s ease;
    }
    .accordion-button:not(.collapsed)::after {
      transform: rotate(180deg);
    }
    .admin-header {
      background-color: #0d6efd;
      color: white;
      padding: 15px;
      border-radius: 6px 6px 0 0;
    }
    .admin-header h3 {
      margin: 0;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="card shadow-lg">
      <div class="admin-header">
        <h3><i class="fas fa-user-shield"></i> Admin Role Manager</h3>
      </div>
      <div class="card-body">
        <form method="post">
          <div class="mb-4">
            <label class="form-label fw-bold">Select Admin User</label>
            <select name="admin_id" class="form-select" required onchange="this.form.submit()">
              <option value="">-- Choose Admin --</option>
              <?php while($row = mysqli_fetch_assoc($admins)) {
                $selected = (isset($_POST['admin_id']) && $_POST['admin_id'] == $row['unohs']) ? 'selected' : '';
                echo "<option value='{$row['unohs']}' $selected>{$row['hesaru']}</option>";
              } ?>
            </select>
          </div>

          <?php
          if (isset($_POST['admin_id'])) {
            $admin_id = intval($_POST['admin_id']);
            $existing_roles = [];
            $check = mysqli_query($conn, "SELECT module, submodule FROM admin_roles WHERE admin_id = $admin_id");
            while($r = mysqli_fetch_assoc($check)) {
              $existing_roles[] = $r['module'].'|'.$r['submodule'];
            }
            echo '<div class="accordion" id="roleAccordion">';
            $i = 0;
            foreach ($roles as $module => $submodules) {
              $module_id = 'module_'.$i++;
              echo "<div class='accordion-item mb-2'>";
              echo "<h2 class='accordion-header'><button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#$module_id'>$module</button></h2>";
              echo "<div id='$module_id' class='accordion-collapse collapse'><div class='accordion-body row g-3'>";
              if (empty($submodules)) {
                $checked = in_array("$module|", $existing_roles) ? 'checked' : '';
                echo "<div class='col-md-4'><div class='form-check'><input class='form-check-input' type='checkbox' name='roles[]' value='$module|' $checked><label class='form-check-label'>Allow $module</label></div></div>";
              } else {
                foreach ($submodules as $sub) {
                  $key = "$module|$sub";
                  $checked = in_array($key, $existing_roles) ? 'checked' : '';
                  echo "<div class='col-md-4'><div class='form-check form-switch'><input class='form-check-input' type='checkbox' name='roles[]' value='$key' $checked><label class='form-check-label'>$sub</label></div></div>";
                }
              }
              echo "</div></div></div>";
            }
            echo '</div><button type="submit" class="btn btn-success mt-4 w-100"><i class="fas fa-save"></i> Save Roles</button>';
          }
          ?>
        </form>
      </div>
    </div>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
