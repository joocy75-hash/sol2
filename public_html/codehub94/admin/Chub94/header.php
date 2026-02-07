<!-- Header with Blur Background -->
<header class="bg-white/80 backdrop-blur-lg sticky top-0 z-40 border-b border-gray-200/30">
    <div class="max-w-7xl mx-auto px-4 py-3">
        <!-- Compact Header Row -->
        <div class="flex items-center justify-between">
            <!-- Back Button -->
            <a href="https://Sol-0203.com/codehub94/admin/dashboard.php" class="flex items-center justify-center w-8 h-8 text-gray-600 hover:text-blue-600 transition-all duration-300 rounded-lg hover:bg-gray-100/50">
                <i class="fas fa-arrow-left"></i>
            </a>
            
            <!-- Logo - Centered -->
            <div class="flex items-center justify-center flex-1">
                <img src="https://gamblly.com/logo.png" alt="Gamblly Logo" class="h-6">
            </div>
            
            <!-- Empty div for spacing -->
            <div class="w-8"></div>
        </div>
    </div>
</header>

<!-- Compact Category Slider Below Header -->
<div class="bg-gray-50/30 border-b border-gray-200/30 py-2">
    <div class="max-w-7xl mx-auto px-4">
        <div class="compact-slider">
            <a href="https://Sol-0203.com/codehub94/admin/Chub94/home-games.php" class="compact-category-item <?= basename($_SERVER['PHP_SELF']) === 'home-games.php' ? 'active' : '' ?>">
                <i class="fas fa-home compact-category-icon"></i>
                <span class="compact-category-name">Home</span>
            </a>
            
            <a href="https://Sol-0203.com/codehub94/admin/Chub94/slot-games.php" class="compact-category-item <?= basename($_SERVER['PHP_SELF']) === 'slot-games.php' ? 'active' : '' ?>">
                <i class="fas fa-dice compact-category-icon"></i>
                <span class="compact-category-name">Slot</span>
            </a>
            
            <a href="https://Sol-0203.com/codehub94/admin/Chub94/live-games.php" class="compact-category-item <?= basename($_SERVER['PHP_SELF']) === 'live-games.php' ? 'active' : '' ?>">
                <i class="fas fa-video compact-category-icon"></i>
                <span class="compact-category-name">Live</span>
            </a>
            
            <a href="https://Sol-0203.com/codehub94/admin/manage_all.php" class="compact-category-item <?= basename($_SERVER['PHP_SELF']) === 'manage_all.php' ? 'active' : '' ?>">
                <i class="fas fa-gamepad compact-category-icon"></i>
                <span class="compact-category-name">All</span>
            </a>
        </div>
    </div>
</div>

<style>
/* Header Blur Effect */
.bg-white\/80 {
    background-color: rgba(255, 255, 255, 0.8);
}

.backdrop-blur-lg {
    backdrop-filter: blur(16px);
}

/* Compact Slider Styles */
.compact-slider {
    display: flex;
    background: white;
    border-radius: 10px;
    padding: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    gap: 4px;
    max-width: 400px;
    margin: 0 auto;
}

.compact-category-item {
    flex: 1;
    padding: 10px 6px;
    text-align: center;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    color: #64748b;
    text-decoration: none;
}

.compact-category-item:hover {
    background: #f8fafc;
}

.compact-category-item.active {
    background: #f1f1f1;
    color: #2563eb;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.15);
}

.compact-category-icon {
    font-size: 14px;
}

.compact-category-name {
    font-size: 11px;
    font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
    .compact-slider {
        max-width: 100%;
        padding: 4px;
        gap: 2px;
    }
    
    .compact-category-item {
        padding: 8px 4px;
        font-size: 11px;
    }
    
    .compact-category-icon {
        font-size: 12px;
    }
    
    .compact-category-name {
        font-size: 10px;
    }
}
</style>