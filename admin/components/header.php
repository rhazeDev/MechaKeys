<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <h1>Admin Dashboard</h1>
    </div>
    <div class="top-bar-right">
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($admin['FullName'], 0, 1)); ?></div>
            <div class="user-details">
                <span class="user-name"><?php echo htmlspecialchars($admin['FullName']); ?></span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>
</div>
