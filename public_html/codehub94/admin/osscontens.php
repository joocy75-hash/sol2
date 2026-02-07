<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");
include 'conn.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Base Site URL
$site_url = 'https://Sol-0203.com/';
// Handle Create Folder
if (isset($_POST['create_folder'])) {
    $folder_name = trim($_POST['folder_name']);
    if (!empty($folder_name)) {
        $folderPath = '../Uploads/' . $folder_name;
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        $conn->query("INSERT INTO ossimages (folder_name, image_url) VALUES ('$folder_name', '')");
        header("Location: osscontens.php?folder=" . urlencode($folder_name));
        exit;
    }
}
// Handle Rename Folder
if (isset($_POST['rename_folder'])) {
    $old_name = trim($_POST['old_name']);
    $new_name = trim($_POST['new_name']);
    if (!empty($old_name) && !empty($new_name)) {
        $old_path = '../Uploads/' . $old_name;
        $new_path = '../Uploads/' . $new_name;
        if (is_dir($old_path)) {
            rename($old_path, $new_path);
            $conn->query("UPDATE ossimages SET folder_name='$new_name', image_url = REPLACE(image_url, 'Uploads/$old_name/', 'Uploads/$new_name/') WHERE folder_name='$old_name'");
            header("Location: osscontens.php?folder=" . urlencode($new_name));
            exit;
        }
    }
}
// Handle Delete Folder
if (isset($_GET['delete_folder'])) {
    $folder = trim($_GET['delete_folder']);
    $folderPath = '../Uploads/' . $folder;
    if (is_dir($folderPath)) {
        array_map('unlink', glob("$folderPath/*.*"));
        rmdir($folderPath);
        $conn->query("DELETE FROM ossimages WHERE folder_name='$folder'");
    }
    header("Location: osscontens.php");
    exit;
}
// Handle Image Upload
if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $folder_name = $_POST['current_folder'];
    $targetDir = "../Uploads/" . $folder_name . "/";

    // Ensure target directory exists and is writable
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    if (!is_writable($targetDir)) {
        die("Error: Upload directory '$targetDir' is not writable.");
    }

    $fileName = time() . '_' . basename($_FILES["file"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $savePath = "Uploads/" . $folder_name . "/" . $fileName;

    // Validate file is an image
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $fileType = mime_content_type($_FILES["file"]["tmp_name"]);
    $fileExt = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));

    // Debug: Log file details
    error_log("File Upload: Name=" . $_FILES["file"]["name"] . ", Type=" . $fileType . ", Ext=" . $fileExt . ", Error=" . $_FILES["file"]["error"] . ", Size=" . $_FILES["file"]["size"]);

    if (in_array($fileType, $allowedTypes) && in_array($fileExt, $allowedExts)) {
        if ($_FILES["file"]["error"] === UPLOAD_ERR_OK) {
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                $fileSize = $_FILES['file']['size'];
                // Use prepared statement to avoid SQL injection
                $stmt = $conn->prepare("INSERT INTO ossimages (folder_name, image_url, file_size) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $folder_name, $savePath, $fileSize);
                $stmt->execute();
                header("Location: osscontens.php?folder=" . urlencode($folder_name));
                exit;
            } else {
                die("Error: Failed to move uploaded file to '$targetFilePath'.");
            }
        } else {
            die("Upload Error: Code " . $_FILES["file"]["error"]);
        }
    } else {
        die("Error: Only image files (jpg, jpeg, png, gif, webp) are allowed. Detected: Type=$fileType, Ext=$fileExt");
    }
}
// Handle Delete Image
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $folder_name = $_GET['folder'];
    $img = $conn->query("SELECT * FROM ossimages WHERE id=$delete_id")->fetch_assoc();
    if ($img) {
        $filePath = '../' . $img['image_url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $conn->query("DELETE FROM ossimages WHERE id=$delete_id");
    }
    header("Location: osscontens.php?folder=" . urlencode($folder_name));
    exit;
}
// Sidebar ke folder fetch query
$folders = $conn->query("SELECT folder_name, COUNT(*) as total_images FROM ossimages GROUP BY folder_name ORDER BY folder_name ASC");
// Current Folder
$current_folder = isset($_GET['folder']) ? trim($_GET['folder']) : '';
$images = [];
if ($current_folder) {
    $images = $conn->query("SELECT * FROM ossimages WHERE folder_name='$current_folder' AND image_url != '' ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>OSS Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: #f1f3f5; }
    .sidebar { width: 250px; height: 100vh; background: #007bff; color: #fff; position: fixed; overflow-y: auto; }
    .sidebar h5 { padding: 15px; }
    .sidebar input { margin: 0 10px 10px 10px; }
    .sidebar .folder { cursor: pointer; padding: 10px 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .sidebar .folder:hover, .sidebar .active-folder { background: #3399ff; }
    .main { margin-left: 250px; padding: 20px; }
    .empty-box { border: 2px dashed #ccc; padding: 80px; text-align: center; background: #fff; }
    .img-thumbnail { height: 150px; object-fit: cover; }
    .dropzone { border: 2px dashed #007bff; padding: 50px; text-align: center; background: #e9f3ff; cursor: pointer; }
    .dropzone.dragover { background: #d0e7ff; }
</style>
</head>
<body>
<!-- Sidebar Start -->
<div class="sidebar">
    <a href="dashboard.php" class="btn btn-warning rounded-pill px-4 py-2 shadow-sm mb-3 w-100">
        ⬅️ Back to Dashboard
    </a>
    <h5>📂 My Folders</h5>
    <input type="text" id="searchInput" class="form-control" placeholder="Search folders...">
    <div id="folderList">
        <?php if ($folders->num_rows > 0) {
            while($folder = $folders->fetch_assoc()) { ?>
            <div class="folder <?php if($current_folder == $folder['folder_name']) echo 'active-folder'; ?>" onclick="openFolder('<?php echo $folder['folder_name']; ?>')">
                📁 <?php echo htmlspecialchars($folder['folder_name']); ?> (<?php echo $folder['total_images']; ?>)
            </div>
        <?php }} else { ?>
            <p class="text-center">No folders</p>
        <?php } ?>
    </div>
    <div class="p-3">
        <button class="btn btn-light w-100 mb-2" data-bs-toggle="modal" data-bs-target="#createFolderModal">+ New Folder</button>
        <?php if ($current_folder) { ?>
        <button class="btn btn-light w-100 mb-2" data-bs-toggle="modal" data-bs-target="#renameFolderModal">✏️ Rename</button>
        <a href="osscontens.php?delete_folder=<?php echo urlencode($current_folder); ?>" class="btn btn-danger w-100" onclick="return confirm('Delete folder and all images?')">🗑️ Delete</a>
        <?php } ?>
    </div>
</div>
<!-- Sidebar End -->
<!-- Main Start -->
<div class="main">
    <?php if ($current_folder) { ?>
        <h4 class="mb-4">📁 <?php echo htmlspecialchars($current_folder); ?></h4>
        <!-- Dropzone Upload -->
        <form id="uploadForm" action="osscontens.php" method="post" enctype="multipart/form-data" class="dropzone mb-4">
            <input type="hidden" name="current_folder" value="<?php echo htmlspecialchars($current_folder); ?>">
            <input type="file" name="file" id="fileUpload" accept="image/jpeg,image/png,image/gif,image/webp" hidden onchange="validateAndSubmit()">
            <h5>📤 Drag & Drop images here or click to upload (jpg, jpeg, png, gif, webp only)</h5>
        </form>
        <?php if ($images && $images->num_rows > 0) { ?>
        <div class="row">
            <?php while($img = $images->fetch_assoc()) {
                $full_link = $site_url . $img['image_url']; ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <img src="../<?php echo $img['image_url']; ?>" class="card-img-top img-thumbnail">
                    <div class="card-body">
                        <input type="text" id="link<?php echo $img['id']; ?>" class="form-control mb-2" value="<?php echo $full_link; ?>" readonly>
                        <button class="btn btn-sm btn-success w-100" onclick="copyLink('link<?php echo $img['id']; ?>')">📋 Copy Link</button>
                        <a href="../<?php echo $img['image_url']; ?>" download class="btn btn-sm btn-primary w-100 mt-2">⬇️ Download</a>
                        <a href="osscontens.php?folder=<?php echo urlencode($current_folder); ?>&delete=<?php echo $img['id']; ?>" class="btn btn-sm btn-danger w-100 mt-2" onclick="return confirm('Delete this image?')">🗑️ Delete</a>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
            <div class="empty-box">
                <h5>No images uploaded yet.</h5>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="empty-box">
            <h5>Select a folder or create new.</h5>
        </div>
    <?php } ?>
</div>
<!-- Main End -->
<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create Folder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" name="folder_name" class="form-control" placeholder="Folder name" required>
      </div>
      <div class="modal-footer">
        <button type="submit" name="create_folder" class="btn btn-success">Create</button>
      </div>
    </form>
  </div>
</div>
<!-- Rename Folder Modal -->
<div class="modal fade" id="renameFolderModal" tabindex="-1" aria-labelledby="renameFolderModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Rename Folder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="old_name" value="<?php echo htmlspecialchars($current_folder); ?>">
        <input type="text" name="new_name" class="form-control" placeholder="New folder name" required>
      </div>
      <div class="modal-footer">
        <button type="submit" name="rename_folder" class="btn btn-success">Rename</button>
      </div>
    </form>
  </div>
</div>
<script>
// Folder open
function openFolder(folderName) {
    window.location.href = 'osscontens.php?folder=' + encodeURIComponent(folderName);
}
// Copy Link
function copyLink(id) {
    var copyText = document.getElementById(id);
    copyText.select();
    document.execCommand("copy");
    alert("Copied to clipboard!");
}
// Search folders
document.getElementById('searchInput').addEventListener('keyup', function() {
    var search = this.value.toLowerCase();
    var folders = document.querySelectorAll('#folderList .folder');
    folders.forEach(function(folder) {
        var text = folder.textContent.toLowerCase();
        folder.style.display = text.includes(search) ? '' : 'none';
    });
});
// Validate and submit image file
function validateAndSubmit() {
    var fileInput = document.getElementById('fileUpload');
    var file = fileInput.files[0];
    if (file) {
        var allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        var fileExt = file.name.split('.').pop().toLowerCase();
        console.log('Selected file:', file.name, 'Extension:', fileExt);
        if (allowedExts.includes(fileExt)) {
            document.getElementById('uploadForm').submit();
        } else {
            alert('Only image files (jpg, jpeg, png, gif, webp) are allowed.');
            fileInput.value = '';
        }
    } else {
        alert('No file selected.');
    }
}
// Dropzone Drag Effect
var dropzone = document.querySelector('.dropzone');
dropzone.addEventListener('click', function() {
    document.getElementById('fileUpload').click();
});
dropzone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('dragover');
});
dropzone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
});
dropzone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    var files = e.dataTransfer.files;
    if (files.length > 0) {
        var file = files[0];
        var allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        var fileExt = file.name.split('.').pop().toLowerCase();
        console.log('Dropped file:', file.name, 'Extension:', fileExt);
        if (allowedExts.includes(fileExt)) {
            document.getElementById('fileUpload').files = files;
            document.getElementById('uploadForm').submit();
        } else {
            alert('Only image files (jpg, jpeg, png, gif, webp) are allowed.');
        }
    } else {
        alert('No file dropped.');
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>