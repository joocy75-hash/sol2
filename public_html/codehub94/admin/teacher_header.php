<?php
if (!isset($_SESSION)) { session_start(); }
if (!isset($_SESSION['teacher_id'])) {
    header('Location: teacher_login.php?msg=true');
    exit();
}
?>
<style>
.header {
    background-color: #001f3f;
    color: white;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    height: 60px;
    position: fixed;
    top: 0;
    left: 220px;  /* ⬅ sidebar width to push header */
    right: 0;
    z-index: 1000;
}

.header strong {
    font-size: 1.2rem;
    font-weight: 700;
}

.header .time,
.header #dateBox {
    margin-right: 15px;
    font-size: 0.9rem;
}

.header .avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: 2px solid #fff;
    transition: transform 0.3s;
}

.header .avatar:hover {
    transform: scale(1.1);
}

/* ensure content doesn’t go under header */
body {
    margin: 0;
    padding-top: 60px;
}
</style>

<div class="header">
    <div>
        <strong>TASHAN WIN</strong>
    </div>
    <div>
        <span class="time" id="timeBox"></span>
        <span id="dateBox"></span>
        <img src="https://scripthubdemo.online/admin_noob_panel_sanju/https://Sol-0203.io/logo.png" class="avatar" alt="Teacher Avatar">
    </div>
</div>

<script>
function updateTime() {
    const now = new Date();
    document.getElementById('timeBox').textContent = now.toLocaleTimeString();
    document.getElementById('dateBox').textContent = now.toLocaleDateString(undefined, {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });
}
setInterval(updateTime, 1000);
updateTime();
</script>
