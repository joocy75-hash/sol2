<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: teacher_login.php?msg=true');
    exit();
}

$teacherId = $_SESSION['teacher_id'];
$registerLink = '';
$errorMessage = '';

$query = "SELECT s.owncode 
          FROM teacher_profile t 
          JOIN shonu_subjects s ON t.user_id = s.id 
          WHERE t.user_id = '$teacherId' 
          LIMIT 1";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $owncode = $row['owncode'];
    $registerLink = "https://Sol-0203.com/#/register?invitationCode=$owncode";
} else {
    $errorMessage = "❌ Unable to fetch register link (user_id not matched in shonu_subjects)";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f5f6fa; padding-left: 180px; padding-top: 60px; }
        .main-content { padding: 20px; }
        .card { background: white; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; }
        footer { background: #001f3f; color: white; text-align: center; padding: 10px; position: fixed; left: 180px; bottom: 0; width: calc(100% - 180px); }
        a.button { display: inline-block; padding: 10px 20px; background: #007bff; color: white; border-radius: 5px; text-decoration: none; transition: background 0.3s; margin-top: 10px; }
        a.button:hover { background: #0056b3; }
        .copy-btn { padding: 6px 12px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px; }
        .copy-btn:hover { background: #218838; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<div class="main-content">
    <h1>Welcome to Teacher Dashboard</h1>
    <p>This is your main page content. Here you can manage agents, view summaries, and register new teachers.</p>

    <div class="card">
        <h2>Register New Teacher</h2>
        <?php if ($registerLink): ?>
            <p>
                <a href="<?= $registerLink ?>" target="_blank"><?= $registerLink ?></a>
                <button class="copy-btn" onclick="copyToClipboard('<?= $registerLink ?>')"><i class="fas fa-copy"></i> Copy</button>
            </p>
        <?php else: ?>
            <p class="error"><?= $errorMessage ?></p>
        <?php endif; ?>
    </div>
</div>

<footer>
    &copy; <?= date('Y') ?> Sol-0203. All rights reserved.
</footer>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Link copied to clipboard!');
    }, () => {
        alert('Failed to copy link.');
    });
}
</script>

</body>
</html>
