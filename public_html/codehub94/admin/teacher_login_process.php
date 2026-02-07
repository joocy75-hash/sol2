<?php
session_start();
include 'conn.php'; // Make sure this connects to your DB

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $password = md5($_POST['password']); // Encrypt password (same as stored in DB)

    // Query to check teacher credentials
    $sql = "SELECT s.id, s.mobile, s.email 
            FROM teacher_profile t
            JOIN shonu_subjects s ON t.user_id = s.id
            WHERE s.mobile = '$mobile' AND s.password = '$password'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $data = mysqli_fetch_assoc($result);
        $_SESSION['teacher_id'] = $data['id'];
        $_SESSION['teacher_mobile'] = $data['mobile'];
        $_SESSION['teacher_email'] = $data['email'];

        // Redirect to dashboard
        header('Location: teacher_dashboard.php');
        exit();
    } else {
        // Redirect back to login with error message
        header('Location: teacher_login.php?err=true');
        exit();
    }
} else {
    // If accessed directly, show unauthorized message
    header('Location: teacher_login.php?msg=true');
    exit();
}
?>
