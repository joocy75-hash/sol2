
<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("Location: index.php?msg=unauthorized");
    exit;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'conn.php';

// Function to sanitize filenames
function sanitizeFilename($filename) {
    return preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($filename));
}

// Initialize notification variables
$notification = '';
$notification_type = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $notification = 'Invalid CSRF token.';
        $notification_type = 'danger';
    } else {
        try {
            $conn->begin_transaction();

            switch ($_POST['action']) {
                case 'add_usdt':
                    $upiid = trim($_POST['newupi'] ?? '');
                    if (empty($upiid)) {
                        throw new Exception('USDT ID cannot be empty.');
                    }
                    $stmt = $conn->prepare("INSERT INTO deyyamrici (maulya, sthiti) VALUES (?, '0')");
                    $stmt->bind_param('s', $upiid);
                    if ($stmt->execute()) {
                        $notification = 'USDT ID added successfully.';
                        $notification_type = 'success';
                    } else {
                        throw new Exception('Failed to add USDT ID.');
                    }
                    $stmt->close();
                    break;

                case 'set_active_usdt':
                    $a_id = $_POST['upiid'] ?? '';
                    if (empty($a_id)) {
                        throw new Exception('No USDT ID selected.');
                    }
                    $stmt = $conn->prepare("UPDATE deyyamrici SET sthiti = '0' WHERE sthiti = '1'");
                    $stmt->execute();
                    $stmt->close();
                    $stmt = $conn->prepare("UPDATE deyyamrici SET sthiti = '1' WHERE maulya = ?");
                    $stmt->bind_param('s', $a_id);
                    if ($stmt->execute()) {
                        $notification = 'Active USDT ID updated.';
                        $notification_type = 'success';
                    } else {
                        throw new Exception('Failed to set active USDT ID.');
                    }
                    $stmt->close();
                    break;

                case 'edit_usdt':
                    $id = $_POST['id'] ?? '';
                    $new_upi = trim($_POST['new_upi'] ?? '');
                    if (empty($id) || empty($new_upi)) {
                        throw new Exception('USDT ID cannot be empty.');
                    }
                    $stmt = $conn->prepare("UPDATE deyyamrici SET maulya = ? WHERE shonu = ?");
                    $stmt->bind_param('ss', $new_upi, $id);
                    if ($stmt->execute()) {
                        $notification = 'USDT ID updated successfully.';
                        $notification_type = 'success';
                    } else {
                        throw new Exception('Failed to update USDT ID.');
                    }
                    $stmt->close();
                    break;

                case 'delete_usdt':
                    $id = $_POST['id'] ?? '';
                    if (empty($id)) {
                        throw new Exception('No USDT ID selected for deletion.');
                    }
                    $stmt = $conn->prepare("SELECT sthiti FROM deyyamrici WHERE shonu = ?");
                    $stmt->bind_param('s', $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    if ($row['sthiti'] == 1) {
                        throw new Exception('Cannot delete active USDT ID.');
                    }
                    $stmt->close();
                    $stmt = $conn->prepare("DELETE FROM deyyamrici WHERE shonu = ?");
                    $stmt->bind_param('s', $id);
                    if ($stmt->execute()) {
                        $notification = 'USDT ID deleted successfully.';
                        $notification_type = 'success';
                    } else {
                        throw new Exception('Failed to delete USDT ID.');
                    }
                    $stmt->close();
                    break;

                case 'upload_image':
                    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
                        throw new Exception('No image selected.');
                    }
                    $target_dir = "../images_usdt/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    $filename = sanitizeFilename($_FILES['image']['name']);
                    $target_file = $target_dir . $filename;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $check = getimagesize($_FILES['image']['tmp_name']);
                    if ($check === false) {
                        throw new Exception('File is not an image.');
                    }
                    if (file_exists($target_file)) {
                        throw new Exception('File already exists.');
                    }
                    if ($_FILES['image']['size'] > 500000) {
                        throw new Exception('File is too large (max 500KB).');
                    }
                    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                        throw new Exception('Only JPG, JPEG, PNG files allowed.');
                    }
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $stmt = $conn->prepare("INSERT INTO images_usdt (filename, status) VALUES (?, '0')");
                        $stmt->bind_param('s', $filename);
                        if ($stmt->execute()) {
                            $notification = 'Image uploaded successfully.';
                            $notification_type = 'success';
                        } else {
                            unlink($target_file);
                            throw new Exception('Failed to add image to database.');
                        }
                        $stmt->close();
                    } else {
                        throw new Exception('Failed to upload image.');
                    }
                    break;

                case 'set_active_image':
                    $a_id = $_POST['imageid'] ?? '';
                    if (empty($a_id)) {
                        throw new Exception('No image selected.');
                    }
                    $stmt = $conn->prepare("UPDATE images_usdt SET status = '0' WHERE status = '1'");
                    $stmt->execute();
                    $stmt->close();
                    $stmt = $conn->prepare("UPDATE images_usdt SET status = '1' WHERE filename = ?");
                    $stmt->bind_param('s', $a_id);
                    if ($stmt->execute()) {
                        $notification = 'Active image updated.';
                        $notification_type = 'success';
                    } else {
                        throw new Exception('Failed to set active image.');
                    }
                    $stmt->close();
                    break;
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $notification = $e->getMessage();
            $notification_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>USDT Management Dashboard</title>
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="https://Sol-0203.com/favicon.ico" />
    <style>
        /* Enhanced Blue and White Theme */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1F2937;
        }
        .content-wrapper {
            background: linear-gradient(135deg, #F5F7FA 0%, #E0E7FF 100%);
            min-height: 100vh;
            padding: 1.5rem;
        }
        .page-title {
            color: #1E3A8A;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            margin-bottom: 1rem;
        }
        .form-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            border: 1px solid rgba(219, 234, 254, 0.5);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            padding: 1.25rem;
            margin: 0.5rem 0;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }
        .form-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
            border-color: #3B82F6;
        }
        .form-card h4 {
            color: #1E3A8A;
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .form-card label {
            font-weight: 500;
            color: #4B5563;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-card input[type="text"],
        .form-card input[type="file"] {
            background: #F9FAFB;
            border: 1px solid #D1D5DB;
            color: #1F2937;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            width: 100%;
            transition: all 0.3s ease;
        }
        .form-card input[type="text"]:focus,
        .form-card input[type="file"]:focus {
            background: #FFFFFF;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
        }
        .form-card input[type="radio"] {
            margin: 0.25rem 0.5rem 0 0;
            accent-color: #3B82F6;
            vertical-align: middle;
        }
        .form-card button {
            background: linear-gradient(90deg, #3B82F6 0%, #60A5FA 100%);
            border: none;
            color: #FFFFFF;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .form-card button:hover {
            background: linear-gradient(90deg, #1E3A8A 0%, #3B82F6 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }
        .form-card button:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            outline: none;
        }
        .form-card button.btn-danger {
            background: linear-gradient(90deg, #EF4444 0%, #F87171 100%);
        }
        .form-card button.btn-danger:hover {
            background: linear-gradient(90deg, #B91C1C 0%, #EF4444 100%);
        }
        .radio-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        .radio-list label {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: #1F2937;
            cursor: pointer;
        }
        .form-card img {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            margin-right: 0.75rem;
            vertical-align: middle;
            object-fit: cover;
        }
        .edit-modal, .delete-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .edit-modal-content, .delete-modal-content {
            background: #FFFFFF;
            margin: 15% auto;
            padding: 1.25rem;
            border-radius: 12px;
            border: 1px solid rgba(219, 234, 254, 0.5);
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        .close {
            color: #4B5563;
            float: right;
            font-size: 1.5rem;
            font-weight: 700;
            cursor: pointer;
        }
        .close:hover {
            color: #1E3A8A;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            min-width: 250px;
            z-index: 10000;
            background: rgba(31, 41, 55, 0.95);
            backdrop-filter: blur(8px);
            color: #FFFFFF;
            border-radius: 6px;
            padding: 0.75rem;
            font-size: 0.875rem;
        }
        @media (max-width: 992px) {
            .content-wrapper {
                padding: 1rem;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .form-card {
                padding: 1rem;
            }
            .form-card h4 {
                font-size: 1rem;
            }
            .form-card input[type="text"],
            .form-card input[type="file"],
            .form-card button {
                font-size: 0.85rem;
                padding: 0.5rem 0.75rem;
            }
        }
        @media (max-width: 576px) {
            .form-card {
                padding: 0.75rem;
                margin: 0.5rem 0;
            }
            .form-card button {
                width: 100%;
                text-align: center;
            }
            .form-card img {
                width: 32px;
                height: 32px;
            }
            .radio-list label {
                font-size: 0.75rem;
            }
            .radio-list {
                max-height: 150px;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
        <div class="main-panel">
            <div class="content-wrapper">
                <!-- Notification Toast -->
                <?php if ($notification): ?>
                    <div class="toast bg-<?php echo $notification_type; ?> text-white" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-body">
                            <?php echo htmlspecialchars($notification); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- USDT ID Section -->
                <div class="row">
                    <div class="col-12">
                        <h4 class="page-title">Manage USDT IDs</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="form-card">
                            <h4>Add USDT ID</h4>
                            <form action="" method="post" autocomplete="off">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="action" value="add_usdt">
                                <label for="newupi">USDT ID</label>
                                <input name="newupi" type="text" id="newupi" placeholder="Enter USDT ID" required>
                                <div class="d-flex align-items-center mt-3">
                                    <button type="submit">Add USDT ID</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="form-card">
                            <h4>Select Active USDT ID</h4>
                            <form action="" method="post" autocomplete="off">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="action" value="set_active_usdt">
                                <div class="radio-list">
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM deyyamrici WHERE sthiti IN ('0', '1') ORDER BY sthiti DESC");
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                    ?>
                                        <label for="<?php echo htmlspecialchars($row['maulya']); ?>">
                                            <input name="upiid" type="radio" id="<?php echo htmlspecialchars($row['maulya']); ?>" value="<?php echo htmlspecialchars($row['maulya']); ?>" <?php if ($row['sthiti'] == 1) echo 'checked'; ?>>
                                            <?php echo htmlspecialchars($row['maulya']); ?>
                                            <span style="margin-left: auto;">
                                                <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="<?php echo htmlspecialchars($row['shonu']); ?>" data-upi="<?php echo htmlspecialchars($row['maulya']); ?>">Edit</button>
                                                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="<?php echo htmlspecialchars($row['shonu']); ?>" data-upi="<?php echo htmlspecialchars($row['maulya']); ?>">Delete</button>
                                            </span>
                                        </label>
                                    <?php }
                                    $stmt->close();
                                    ?>
                                </div>
                                <button type="submit">Save Selection</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- USDT Image Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h4 class="page-title">Manage USDT Images</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="form-card">
                            <h4>Upload Image</h4>
                            <form action="" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="action" value="upload_image">
                                <label for="image">Select Image (JPG, PNG)</label>
                                <input type="file" name="image" id="image" accept="image/jpeg,image/png" required>
                                <div class="d-flex align-items-center mt-3">
                                    <button type="submit">Upload Image</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="form-card">
                            <h4>Select Active Image</h4>
                            <form action="" method="post" autocomplete="off">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="action" value="set_active_image">
                                <div class="radio-list">
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM images_usdt WHERE status IN ('0', '1')");
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                    ?>
                                        <label for="<?php echo htmlspecialchars($row['filename']); ?>">
                                            <input name="imageid" type="radio" id="<?php echo htmlspecialchars($row['filename']); ?>" value="<?php echo htmlspecialchars($row['filename']); ?>" <?php if ($row['status'] == 1) echo 'checked'; ?>>
                                            <img src="../images_usdt/<?php echo htmlspecialchars($row['filename']); ?>" alt="<?php echo htmlspecialchars($row['filename']); ?>">
                                            <?php echo htmlspecialchars($row['filename']); ?>
                                        </label>
                                    <?php }
                                    $stmt->close();
                                    ?>
                                </div>
                                <button type="submit">Save Selection</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div id="editModal" class="edit-modal">
                    <div class="edit-modal-content">
                        <span class="close">&times;</span>
                        <h3>Edit USDT ID</h3>
                        <form id="editForm" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="edit_usdt">
                            <input type="hidden" name="id" id="editId">
                            <div class="form-group">
                                <label for="editUpi">USDT ID</label>
                                <input type="text" class="form-control" name="new_upi" id="editUpi" required>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" class="btn btn-secondary mr-2 close">Cancel</button>
                                <button type="submit">Update</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div id="deleteModal" class="delete-modal">
                    <div class="delete-modal-content">
                        <span class="close">&times;</span>
                        <h3>Delete USDT ID</h3>
                        <p>Are you sure you want to delete "<span id="deleteUpi"></span>"?</p>
                        <form id="deleteForm" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="delete_usdt">
                            <input type="hidden" name="id" id="deleteId">
                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" class="btn btn-secondary mr-2 close">Cancel</button>
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <div class="d-sm-flex justify-content-center justify-content-sm-between">
                    <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">© 2025 <a href="https://Sol-0203.com/">Sol-0203</a>. All Rights Reserved.</span>
                </div>
            </footer>
        </div>
    </div>
</div>
<script src="vendors/base/vendor.bundle.base.js"></script>
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Client-side validation for USDT ID
    document.querySelector('form[action=""][method="post"]:not([enctype])').addEventListener('submit', function(e) {
        const upi = document.querySelector('input[name="newupi"]');
        if (upi && !upi.value.trim()) {
            e.preventDefault();
            document.querySelector('.toast').innerHTML = '<div class="toast-body">USDT ID cannot be empty</div>';
            $('.toast').toast({ delay: 3000 }).toast('show');
        }
    });

    // Client-side validation for image upload
    document.querySelector('form[enctype="multipart/form-data"]').addEventListener('submit', function(e) {
        const fileInput = document.querySelector('input[name="image"]');
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const validTypes = ['image/jpeg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                e.preventDefault();
                document.querySelector('.toast').innerHTML = '<div class="toast-body">Please select a JPG or PNG file</div>';
                $('.toast').toast({ delay: 3000 }).toast('show');
            }
            if (file.size > 500000) {
                e.preventDefault();
                document.querySelector('.toast').innerHTML = '<div class="toast-body">File size must be less than 500KB</div>';
                $('.toast').toast({ delay: 3000 }).toast('show');
            }
        }
    });

    // Edit modal functionality
    const editModal = document.getElementById("editModal");
    const editBtns = document.querySelectorAll(".edit-btn");
    const editClose = editModal.getElementsByClassName("close");

    editBtns.forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.getAttribute("data-id");
            const upi = this.getAttribute("data-upi");
            document.getElementById("editId").value = id;
            document.getElementById("editUpi").value = upi;
            editModal.style.display = "block";
        });
    });

    Array.from(editClose).forEach(close => {
        close.onclick = function () {
            editModal.style.display = "none";
        };
    });

    // Delete modal functionality
    const deleteModal = document.getElementById("deleteModal");
    const deleteBtns = document.querySelectorAll(".delete-btn");
    const deleteClose = deleteModal.getElementsByClassName("close");

    deleteBtns.forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.getAttribute("data-id");
            const upi = this.getAttribute("data-upi");
            document.getElementById("deleteId").value = id;
            document.getElementById("deleteUpi").textContent = upi;
            deleteModal.style.display = "block";
        });
    });

    Array.from(deleteClose).forEach(close => {
        close.onclick = function () {
            deleteModal.style.display = "none";
        };
    });

    window.onclick = function (event) {
        if (event.target == editModal) {
            editModal.style.display = "none";
        }
        if (event.target == deleteModal) {
            deleteModal.style.display = "none";
        }
    };

    // Toast notification
    $('.toast').toast({ delay: 3000 });
    $('.toast').toast('show');
</script>
</body>
</html>
```