<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin_functions.php';

// Check if already logged in
session_start();
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate form data
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // If no errors, try to login
if (empty($errors)) {
    $user = getUserByUsername($conn, $username);

    // Debug logs
    error_log("Login attempt for username: $username");
    if ($user) {
        error_log("User found: " . print_r($user, true));
        if (password_verify($password, $user['password'])) {
            error_log("Password verification successful");
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect to admin dashboard
            header('Location: index.php');
            exit;
        } else {
            error_log("Password verification failed");
        }
    } else {
        error_log("User not found");
    }
    
    $errors[] = 'Invalid username or password';
}
}

// Set page title
$pageTitle = "Admin Login - PetPal Adoption Center";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-login-page">
    <div class="admin-login-container">
        <div class="login-logo">
            <a href="../index.php">
                <img src="../assets/img/logo.png" alt="PetPal Logo">
            </a>
        </div>
        
        <div class="login-box">
            <h1>Admin Login</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-submit">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
            
            <div class="login-footer">
                <a href="../index.php">Return to Website</a>
            </div>
        </div>
    </div>
</body>
</html>