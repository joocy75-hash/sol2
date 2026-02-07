<?php
if (!isset($_SESSION)) { session_start(); }
if (!isset($_SESSION['teacher_id'])) {
    header('Location: teacher_login.php?msg=true');
    exit();
}
?>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<div class="sidebar">
    <a href="teacher_dashboard.php"><span class="material-icons">dashboard</span> <span class="link-text">Dashboard</span></a>
    
    <a href="teacher_promotion.php"><span class="material-icons">campaign</span> <span class="link-text">My Promotion</span></a>

    
    <div class="dropdown">
        <div class="dropdown-btn">
            <span><span class="material-icons">group</span> <span class="link-text">Manage Agent</span></span>
            <span class="material-icons arrow">expand_more</span>
        </div>
        <div class="dropdown-container">
            <a href="teacher_addagents.php"><span class="material-icons">person_add</span> <span class="link-text">Add Agent</span></a>
            <a href="agent_list.php"><span class="material-icons">list</span> <span class="link-text">Agent List</span></a>
            <a href="teacher_agentssummary.php"><span class="material-icons">summarize</span> <span class="link-text">Agent Summary</span></a>
        </div>
    </div>
    
    <div class="dropdown">
        <div class="dropdown-btn">
          <span><span class="material-icons">sports_esports</span> <span class="link-text">Game</span></span>
            <span class="material-icons arrow">expand_more</span>
        </div>
     <div class="dropdown-container">
    <a href="teacher_wingo.php">
        <span class="material-icons">casino</span>
        <span class="link-text">Wingo</span>
    </a>
    <a href="teacher_5d.php">
        <span class="material-icons">confirmation_number</span>
        <span class="link-text">5D</span>
    </a>
    <a href="teacher_k3.php">
        <span class="material-icons">sports_esports</span>
        <span class="link-text">K3</span>
    </a>
    <a href="teacher_trx.php">
        <span class="material-icons">currency_bitcoin</span>
        <span class="link-text">TRX</span>
    </a>
    <a href="teacher_userprofit.php">
        <span class="material-icons">trending_up</span>
        <span class="link-text">User Profit</span>
    </a>
    <a href="teacher_userloss.php">
        <span class="material-icons">trending_down</span>
        <span class="link-text">User Loss</span>
    </a>
    <a href="teacher_userranking.php">
        <span class="material-icons">leaderboard</span>
        <span class="link-text">User Ranking</span>
    </a>
</div>

    </div>
    
    
    
    <div class="dropdown">
    <div class="dropdown-btn" style="display: flex; align-items: center; justify-content: space-between; white-space: nowrap; overflow: hidden;">
        <span style="display: flex; align-items: center;">
            <span class="material-icons">event</span>
            <span class="link-text" style="margin-left: 8px; white-space: nowrap;">Event Management</span>
        </span>
        <span class="material-icons arrow">expand_more</span>
    </div>
    <div class="dropdown-container">
        <a href="teacher_redenvlope.php" style="white-space: nowrap;">
            <span class="material-icons">local_shipping</span>
            <span class="link-text" style="margin-left: 8px;">Red Envelope </span>
        </a>
    </div>
</div>

    
    
    

    <a href="gift_code.php"><span class="material-icons">redeem</span> <span class="link-text">Gift Code</span></a>
    
    
    <div class="dropdown">
    <div class="dropdown-btn" style="display: flex; align-items: center; justify-content: space-between; white-space: nowrap; overflow: hidden;">
        <span style="display: flex; align-items: center;">
            <span class="material-icons">settings</span>
            <span class="link-text" style="margin-left: 8px; white-space: nowrap;">Setting </span>
        </span>
        <span class="material-icons arrow">expand_more</span>
    </div>
    <div class="dropdown-container">
        <!-- Red Envelope link removed -->
        <a href="teacher_agentsturnover.php">
    <span class="material-icons">settings</span>
    <span class="link-text">Setting</span>
</a>

    </div>
</div>

    
    
    
    
</div>

<div class="header">
    <strong>Sol-0203 </strong>
    <div class="header-right">
        <span class="time" id="timeBox"></span>
        <span id="dateBox"></span>
        <div class="avatar-wrapper">
            <img src="https://Sol-0203.com/Chub94/images/Sol-0203.png" class="avatar" alt="Avatar">
            <div class="dropdown-menu">
                <a href="teacher_profile.php">Profile</a>
                <a href="teacher_logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
function updateTime() {
    const now = new Date();
    document.getElementById('timeBox').textContent = now.toLocaleTimeString();
    document.getElementById('dateBox').textContent = now.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}
setInterval(updateTime, 1000);
updateTime();

document.querySelectorAll('.dropdown-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const dropdown = btn.parentElement;
        dropdown.classList.toggle('active');
        dropdown.querySelector('.dropdown-container').classList.toggle('show');
        dropdown.querySelector('.arrow').textContent = dropdown.classList.contains('active') ? 'expand_less' : 'expand_more';
    });
});

document.querySelector('.avatar-wrapper').addEventListener('click', (e) => {
    e.stopPropagation();
    document.querySelector('.dropdown-menu').classList.toggle('show');
});
document.addEventListener('click', () => {
    document.querySelector('.dropdown-menu').classList.remove('show');
});
</script>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding-left: 200px;
    padding-top: 50px;
    background-color: #f5f7fa;
}

.header {
    background-color: #001f3f;
    color: white;
    padding: 8px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 50px;
    position: fixed;
    top: 0;
    left: 200px;
    right: 0;
    z-index: 1000;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.header-right {
    display: flex;
    align-items: center;
}
.header .time, .header #dateBox {
    margin-right: 15px;
    font-size: 0.85rem;
}
.avatar-wrapper {
    position: relative;
    cursor: pointer;
}
.avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 2px solid #fff;
    transition: transform 0.3s;
}
.avatar:hover {
    transform: scale(1.1);
}
.dropdown-menu {
    display: none;
    position: absolute;
    top: 40px;
    right: 0;
    background: white;
    color: #333;
    border-radius: 5px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    min-width: 120px;
    z-index: 1001;
}
.dropdown-menu a {
    display: block;
    padding: 10px;
    text-decoration: none;
    color: #333;
    font-size: 0.9rem;
}
.dropdown-menu a:hover {
    background: #f0f0f0;
}
.dropdown-menu.show {
    display: block;
}

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 200px;
    background-color: #001933;
    padding-top: 50px;
    box-shadow: 2px 0 10px rgba(0,0,0,0.2);
}
.sidebar a, .dropdown-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
    padding: 10px 18px;
    text-decoration: none;
    font-size: 0.9rem;
    cursor: pointer;
}
.sidebar a:hover, .dropdown-btn:hover {
    background-color: #003366;
}
.sidebar .material-icons {
    font-size: 20px;
    margin-right: 10px;
}
.sidebar .link-text {
    flex-grow: 1;
    text-align: left;
    margin-left: 5px;
}
.dropdown-container {
    display: none;
    flex-direction: column;
    background: #002244;
}
.dropdown-container a {
    padding-left: 45px;
    font-size: 0.85rem;
}
.dropdown-container.show {
    display: flex;
}
.dropdown-btn .arrow {
    transition: transform 0.3s;
}
.dropdown.active .arrow {
    transform: rotate(180deg);
}
</style>
