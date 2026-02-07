<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");
include 'conn.php';

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM game_category WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: update_category_banner.php?msg=deleted");
    exit;
}

// Toggle state
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $conn->prepare("UPDATE game_category SET state = 1 - state WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: update_category_banner.php?msg=toggled");
    exit;
}

// Edit mode
$editRow = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM game_category WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editRow = $result->fetch_assoc();
}

// Save update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_edit'])) {
    $id = (int)$_POST['id'];
    $typeNameCode = (int)$_POST['typeNameCode'];
    $categoryCode = trim($_POST['categoryCode']);
    $categoryName = trim($_POST['categoryName']);
    $state = (int)$_POST['state'];
    $sort = (int)$_POST['sort'];
    $newImageUrl = trim($_POST['newImageUrl']);

    // Get current image
    $current = $conn->query("SELECT categoryImg FROM game_category WHERE id = $id")->fetch_assoc();
    $currentImage = $current['categoryImg'];

    // Final image
    $finalImage = ($newImageUrl !== '') ? $newImageUrl : $currentImage;

    // Escape
    $categoryCode = $conn->real_escape_string($categoryCode);
    $categoryName = $conn->real_escape_string($categoryName);
    $finalImage = $conn->real_escape_string($finalImage);

    $sql = "UPDATE game_category SET 
            typeNameCode = $typeNameCode,
            categoryCode = '$categoryCode',
            categoryName = '$categoryName',
            state = $state,
            sort = $sort,
            categoryImg = '$finalImage'
            WHERE id = $id";

    if ($conn->query($sql)) {
        header("Location: update_category_banner.php?msg=updated");
    } else {
        header("Location: update_category_banner.php?msg=error");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Category Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root{
            --blue:#1e40af;--blue-light:#3b82f6;--gray:#f8fafc;--danger:#dc2626;--success:#16a34a;
        }
        *{box-sizing:border-box;margin:0;padding:0;}
        html,body{background:var(--gray);font-family:'Inter',sans-serif;overflow-x:hidden;}
        .main-content{padding:2rem;background:white;min-height:100vh;}
        .page-title{font-size:1.9rem;font-weight:900;color:var(--blue);margin-bottom:2.5rem;padding-bottom:1rem;border-bottom:6px solid var(--blue-light);text-align:center;}
        .card{border-radius:20px;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,0.12);border:1px solid #e2e8f0;margin-bottom:2.5rem;}
        .card-header{background:linear-gradient(135deg,var(--blue),var(--blue-light));color:white;padding:1.2rem 1.8rem;font-weight:700;font-size:1.25rem;}
        .card-body{padding:2rem;}
        .form-control,.form-select{
            border-radius:14px;
            padding:0.85rem 1.2rem;
            border:2px solid #cbd5e1;
            font-size:0.98rem;
            transition:border-color .3s;
        }
        .form-control:focus,.form-select:focus{
            border-color:var(--blue-light);
            box-shadow:0 0 0 .25rem rgba(59,130,246,.25);
        }
        .btn-custom{
            padding:0.8rem 2rem;
            border-radius:50px;
            font-weight:600;
            font-size:1rem;
            transition:all .3s;
        }
        .btn-save{background:linear-gradient(135deg,var(--success),#22c55e);color:white;}
        .btn-cancel{background:#6b7280;color:white;}
        .btn-edit{background:#0d6efd;color:white;}
        .btn-delete{background:#dc2626;color:white;}
        .btn-custom:hover{opacity:0.9;}
        .category-img{height:85px;width:155px;object-fit:cover;border-radius:12px;border:2px solid #e2e8f0;}
        .table{font-size:0.95rem;}
        .table thead{background:#f8f9fa;font-weight:700;}
        .table tbody tr:hover{background:#f0f7ff;}
        .badge{padding:0.5rem 1rem;border-radius:50px;font-weight:600;font-size:0.85rem;}
        .badge-enabled{background:#dcfce7;color:#166534;}
        .badge-disabled{background:#fee2e2;color:#991b1b;}
        .current-url{background:#f1f5f9;padding:0.8rem 1rem;border-radius:12px;font-size:0.9rem;color:#1e40af;margin-top:0.5rem;display:block;word-break:break-all;}
        @media (max-width:768px){
            .main-content{padding:1rem;}
            .page-title{font-size:1.5rem;}
            .card-header{font-size:1.1rem;padding:1rem;}
            .card-body{padding:1.5rem;}
            .btn-custom{padding:0.75rem 1.6rem;font-size:0.92rem;}
            .category-img{height:70px;width:120px;}
            .table{font-size:0.88rem;}
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container-fluid">
    <div class="main-content">

        <h2 class="page-title">Game Category Management</h2>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-<?= ($_GET['msg']=='updated'||$_GET['msg']=='toggled')?'success':($_GET['msg']=='deleted'?'warning':'danger') ?> alert-dismissible fade show">
                <strong>
                    <?= $_GET['msg']=='updated'?'Updated Successfully!':($_GET['msg']=='deleted'?'Deleted!':($_GET['msg']=='toggled'?'State Changed!':'Error')) ?>
                </strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($editRow): ?>
        <div class="card mb-4">
            <div class="card-header">Edit Category #<?= $editRow['id'] ?></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="save_edit" value="1">
                    <input type="hidden" name="id" value="<?= $editRow['id'] ?>">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">Type Name Code</label>
                            <input type="number" name="typeNameCode" class="form-control" value="<?= $editRow['typeNameCode'] ?>" required>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">Category Code</label>
                            <input type="text" name="categoryCode" class="form-control" value="<?= htmlspecialchars($editRow['categoryCode']) ?>" required>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">Category Name</label>
                            <input type="text" name="categoryName" class="form-control" value="<?= htmlspecialchars($editRow['categoryName']) ?>" required>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">State</label>
                            <select name="state" class="form-select" required>
                                <option value="1" <?= $editRow['state']==1?'selected':'' ?>>Enabled</option>
                                <option value="0" <?= $editRow['state']==0?'selected':'' ?>>Disabled</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort" class="form-control" value="<?= $editRow['sort'] ?>" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label fw-bold">
                                Category Image URL 
                                <small class="text-muted">(Leave blank = no change)</small>
                            </label>
                            <input type="url" name="newImageUrl" class="form-control" placeholder="https://example.com/image.png">
                            <div class="current-url">
                                Current: <strong><?= htmlspecialchars($editRow['categoryImg']) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-custom btn-save">Update Category</button>
                        <a href="update_category_banner.php" class="btn btn-custom btn-cancel ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                All Categories (<?= $conn->query("SELECT COUNT(*) FROM game_category")->fetch_row()[0] ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="6%">#</th>
                                <th width="18%">Image</th>
                                <th width="22%">Name</th>
                                <th width="12%">Type</th>
                                <th width="12%">Code</th>
                                <th width="10%">State</th>
                                <th width="8%">Sort</th>
                                <th width="12%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $all = $conn->query("SELECT * FROM game_category ORDER BY sort DESC");
                            while ($r = $all->fetch_assoc()):
                            ?>
                            <tr>
                                <td><strong><?= $i++ ?></strong></td>
                                <td>
                                    <img src="<?= htmlspecialchars($r['categoryImg']) ?>" 
                                         onerror="this.src='https://via.placeholder.com/160x90/1e40af/ffffff?text=No+Image'" 
                                         class="category-img" alt="">
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($r['categoryName']) ?></td>
                                <td><?= $r['typeNameCode'] ?></td>
                                <td><?= htmlspecialchars($r['categoryCode']) ?></td>
                                <td>
                                    <a href="?toggle=<?= $r['id'] ?>" class="badge badge-<?= $r['state']==1?'enabled':'disabled' ?>">
                                        <?= $r['state']==1?'ON':'OFF' ?>
                                    </a>
                                </td>
                                <td><strong><?= $r['sort'] ?></strong></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                        <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-delete" 
                                           onclick="return confirm('Delete?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>