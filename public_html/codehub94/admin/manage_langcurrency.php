<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");
include 'conn.php';

mysqli_query($conn, "SET NAMES 'utf8mb4'");
mysqli_query($conn, "SET CHARACTER SET utf8mb4");
mysqli_query($conn, "SET SESSION collation_connection = 'utf8mb4_unicode_ci'");

// Add Entry
if (isset($_POST['add'])) {
    $lang_code = $_POST['lang_code'];
    $lang_name = $_POST['lang_name'];
    $flag_url = $_POST['flag_url'];
    $currency_code = $_POST['currency_code'];
    $currency_symbol = $_POST['currency_symbol'];
    $currency_name = $_POST['currency_name'];
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;

    if ($is_default == 1) {
        mysqli_query($conn, "UPDATE langcurrency SET is_default = 0");
    }

    $sql = "INSERT INTO langcurrency 
            (lang_code, lang_name, flag_url, currency_code, currency_symbol, currency_name, is_default, status)
            VALUES 
            ('$lang_code', '$lang_name', '$flag_url', '$currency_code', '$currency_symbol', '$currency_name', $is_default, $status)";
    mysqli_query($conn, $sql);
    header("Location: manage_langcurrency.php");
    exit;
}

// Edit Entry
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $lang_code = $_POST['lang_code'];
    $lang_name = $_POST['lang_name'];
    $flag_url = $_POST['flag_url'];
    $currency_code = $_POST['currency_code'];
    $currency_symbol = $_POST['currency_symbol'];
    $currency_name = $_POST['currency_name'];
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;

    if ($is_default == 1) {
        mysqli_query($conn, "UPDATE langcurrency SET is_default = 0");
        $status = 1;
    }

    $sql = "UPDATE langcurrency SET 
            lang_code = '$lang_code', 
            lang_name = '$lang_name', 
            flag_url = '$flag_url', 
            currency_code = '$currency_code', 
            currency_symbol = '$currency_symbol', 
            currency_name = '$currency_name', 
            is_default = $is_default, 
            status = $status 
            WHERE id = $id";
    mysqli_query($conn, $sql);
    header("Location: manage_langcurrency.php");
    exit;
}

// Delete Entry
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $result = mysqli_query($conn, "SELECT is_default FROM langcurrency WHERE id = $id");
    $row = mysqli_fetch_assoc($result);
    if ($row['is_default'] == 1) {
        header("Location: manage_langcurrency.php?msg=cannot_delete_default");
        exit;
    }
    mysqli_query($conn, "DELETE FROM langcurrency WHERE id = $id");
    header("Location: manage_langcurrency.php");
    exit;
}

// Set Default
if (isset($_GET['set_default'])) {
    $id = $_GET['set_default'];
    mysqli_query($conn, "UPDATE langcurrency SET is_default = 0");
    mysqli_query($conn, "UPDATE langcurrency SET is_default = 1, status = 1 WHERE id = $id");
    header("Location: manage_langcurrency.php");
    exit;
}

// Toggle Status
if (isset($_GET['toggle_status'])) {
    $id = $_GET['toggle_status'];
    $result = mysqli_query($conn, "SELECT is_default FROM langcurrency WHERE id = $id");
    $row = mysqli_fetch_assoc($result);
    if ($row['is_default'] == 1) {
        header("Location: manage_langcurrency.php");
        exit;
    }
    mysqli_query($conn, "UPDATE langcurrency SET status = IF(status=1,0,1) WHERE id = $id");
    header("Location: manage_langcurrency.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM langcurrency ORDER BY id ASC");
?>

<?php include 'header.php'; ?>

<style>
  .toggle-small {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 22px;
  }
  .toggle-small input {
    opacity: 0;
    width: 0;
    height: 0;
  }
  .slider-small {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #e0e0e0;
    transition: .3s;
    border-radius: 22px;
  }
  .slider-small:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: #fff;
    transition: .3s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
  }
  input:checked + .slider-small {
    background-color: #007bff;
  }
  input:checked + .slider-small:before {
    transform: translateX(18px);
  }
  .container {
    max-width: 1200px;
  }
  .card {
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  }
  .form-control, .btn {
    border-radius: 6px;
    transition: all 0.2s;
  }
  .table {
    border-radius: 8px;
    overflow: hidden;
  }
  .table th, .table td {
    vertical-align: middle;
  }
  .btn-sm {
    padding: 0.3rem 0.6rem;
    font-size: 0.875rem;
  }
  .modal-content {
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  }
  .modal-header, .modal-footer {
    border: none;
  }
  .form-label {
    font-weight: 500;
    color: #333;
  }
  .btn-primary {
    background-color: #007bff;
    border-color: #007bff;
  }
  .btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
  }
  .btn-outline-success, .btn-outline-primary, .btn-outline-danger {
    transition: all 0.2s;
  }
  .btn-outline-danger:hover {
    background-color: #dc3545;
    color: #fff;
  }
  .table-hover tbody tr:hover {
    background-color: #f8f9fa;
  }
  @media (max-width: 576px) {
    .form-control {
      font-size: 0.9rem;
    }
    .btn-sm {
      font-size: 0.75rem;
      padding: 0.2rem 0.4rem;
    }
    .table {
      font-size: 0.85rem;
    }
    .modal-dialog {
      margin: 0.5rem;
    }
  }
</style>

<div class="container my-5">
  <h4 class="mb-4 fw-bold text-primary">🌍 Language & Currency Management</h4>

  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <form method="POST" class="row g-3">
        <div class="col-md-2 col-6">
          <label class="form-label">Lang Code</label>
          <input type="text" name="lang_code" class="form-control" required>
        </div>
        <div class="col-md-2 col-6">
          <label class="form-label">Lang Name</label>
          <input type="text" name="lang_name" class="form-control" required>
        </div>
        <div class="col-md-2 col-12">
          <label class="form-label">Flag URL</label>
          <input type="text" name="flag_url" class="form-control" placeholder="/flags/en.png" required>
        </div>
        <div class="col-md-2 col-6">
          <label class="form-label">Currency Code</label>
          <input type="text" name="currency_code" class="form-control" placeholder="USD" required>
        </div>
        <div class="col-md-1 col-6">
          <label class="form-label">Symbol</label>
          <input type="text" name="currency_symbol" class="form-control" placeholder="$" required>
        </div>
        <div class="col-md-3 col-12">
          <label class="form-label">Currency Name</label>
          <input type="text" name="currency_name" class="form-control" placeholder="US Dollar" required>
        </div>
        <div class="col-12 d-flex align-items-center gap-4 mt-3">
          <div class="d-flex align-items-center gap-2">
            <label class="toggle-small">
              <input type="checkbox" name="is_default">
              <span class="slider-small"></span>
            </label>
            <span class="text-muted" style="font-size: 14px;">Default</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <label class="toggle-small">
              <input type="checkbox" name="status" checked>
              <span class="slider-small"></span>
            </label>
            <span class="text-muted" style="font-size: 14px;">Active</span>
          </div>
        </div>
        <div class="col-12 mt-3">
          <button type="submit" name="add" class="btn btn-primary w-100">Add Entry</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Flag</th>
            <th scope="col">Lang</th>
            <th scope="col">Code</th>
            <th scope="col">Currency</th>
            <th scope="col">Symbol</th>
            <th scope="col">Default</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><img src="<?= htmlspecialchars($row['flag_url']) ?>" width="30" alt="Flag"></td>
              <td><?= htmlspecialchars($row['lang_name']) ?></td>
              <td><?= htmlspecialchars($row['lang_code']) ?></td>
              <td><?= htmlspecialchars($row['currency_name']) ?> (<?= htmlspecialchars($row['currency_code']) ?>)</td>
              <td><?= htmlspecialchars($row['currency_symbol'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= $row['is_default'] ? '<span class="text-success fw-bold">✅</span>' : '' ?></td>
              <td>
                <label class="toggle-small">
                  <input type="checkbox" onchange="location.href='?toggle_status=<?= $row['id'] ?>'" <?= $row['status'] ? 'checked' : '' ?> <?= $row['is_default'] ? 'disabled' : '' ?>>
                  <span class="slider-small"></span>
                </label>
              </td>
              <td>
                <a href="?set_default=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success me-1">Make Default</a>
                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>" <?= $row['is_default'] ? 'disabled' : '' ?>>Delete</button>
              </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['id'] ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?= $row['id'] ?>">Edit Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST">
                    <div class="modal-body">
                      <input type="hidden" name="id" value="<?= $row['id'] ?>">
                      <div class="mb-3">
                        <label class="form-label">Lang Code</label>
                        <input type="text" name="lang_code" class="form-control" value="<?= htmlspecialchars($row['lang_code']) ?>" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Lang Name</label>
                        <input type="text" name="lang_name" class="form-control" value="<?= htmlspecialchars($row['lang_name']) ?>" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Flag URL</label>
                        <input type="text" name="flag_url" class="form-control" value="<?= htmlspecialchars($row['flag_url']) ?>" placeholder="/flags/en.png" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Currency Code</label>
                        <input type="text" name="currency_code" class="form-control" value="<?= htmlspecialchars($row['currency_code']) ?>" placeholder="USD" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Symbol</label>
                        <input type="text" name="currency_symbol" class="form-control" value="<?= htmlspecialchars($row['currency_symbol']) ?>" placeholder="$" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Currency Name</label>
                        <input type="text" name="currency_name" class="form-control" value="<?= htmlspecialchars($row['currency_name']) ?>" placeholder="US Dollar" required>
                      </div>
                      <div class="d-flex align-items-center gap-4">
                        <div class="d-flex align-items-center gap-2">
                          <label class="toggle-small">
                            <input type="checkbox" name="is_default" <?= $row['is_default'] ? 'checked' : '' ?>>
                            <span class="slider-small"></span>
                          </label>
                          <span class="text-muted" style="font-size: 14px;">Default</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <label class="toggle-small">
                            <input type="checkbox" name="status" <?= $row['status'] ? 'checked' : '' ?> <?= $row['is_default'] ? 'checked disabled' : '' ?>>
                            <span class="slider-small"></span>
                          </label>
                          <span class="text-muted" style="font-size: 14px;">Active</span>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      <button type="submit" name="edit" class="btn btn-primary">Save Changes</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $row['id'] ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel<?= $row['id'] ?>">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST">
                    <div class="modal-body">
                      <input type="hidden" name="id" value="<?= $row['id'] ?>">
                      <p>Are you sure you want to delete <strong><?= htmlspecialchars($row['lang_name']) ?> (<?= htmlspecialchars($row['currency_code']) ?>)</strong>? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>