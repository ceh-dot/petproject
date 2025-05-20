<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin Dashboard - PetPal Adoption Center'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-page">
    <header class="admin-header">
        <div class="admin-header-content">
            <div class="admin-logo">
                <a href="index.php">
                    <img src="../assets/img/logo.png" alt="PetPal Logo">
                    <span>PetPal Admin</span>
                </a>
            </div>
            
            <div class="admin-user-menu">
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div class="user-dropdown">
                        <a href="../index.php" target="_blank">View Site</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>