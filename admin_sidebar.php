<aside class="admin-sidebar">
        <nav class="admin-nav">
            <ul>
                <li>
                    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <span class="nav-icon dashboard-icon"></span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="pets.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pets.php' || basename($_SERVER['PHP_SELF']) == 'add_pet.php' || basename($_SERVER['PHP_SELF']) == 'edit_pet.php' ? 'active' : ''; ?>">
                        <span class="nav-icon pets-icon"></span>
                        <span class="nav-text">Manage Pets</span>
                    </a>
                </li>
                <li>
                    <a href="messages.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' || basename($_SERVER['PHP_SELF']) == 'view_message.php' ? 'active' : ''; ?>">
                        <span class="nav-icon messages-icon"></span>
                        <span class="nav-text">Messages</span>
                    </a>
                </li>
                <li>
                    <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' || basename($_SERVER['PHP_SELF']) == 'add_user.php' || basename($_SERVER['PHP_SELF']) == 'edit_user.php' ? 'active' : ''; ?>">
                        <span class="nav-icon users-icon"></span>
                        <span class="nav-text">Users</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                        <span class="nav-icon settings-icon"></span>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="nav-icon logout-icon"></span>
                        <span class="nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>