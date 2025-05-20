<?php
/**
 * General utility functions for the pet listing website
 */

/**
 * Checks if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Sanitizes output for display
 * 
 * @param string $data The data to sanitize
 * @return string The sanitized data
 */
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Creates a slug from a string
 * 
 * @param string $string The string to create a slug from
 * @return string The slug
 */
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', ' ', $string);
    $string = preg_replace('/\s/', '-', $string);
    return $string;
}

/**
 * Generate a random string
 * 
 * @param int $length The length of the string
 * @return string The random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Format a date
 * 
 * @param string $date The date to format
 * @param string $format The format to use
 * @return string The formatted date
 */
function formatDate($date, $format = 'F j, Y') {
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

/**
 * Truncate text to a specified length
 * 
 * @param string $text The text to truncate
 * @param int $length The length to truncate to
 * @param string $append The string to append to the truncated text
 * @return string The truncated text
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . $append;
}

/**
 * Saves a contact form submission to the database
 * 
 * @param PDO $conn The database connection
 * @param string $name The name of the person submitting the form
 * @param string $email The email of the person submitting the form
 * @param string $phone The phone number of the person submitting the form
 * @param string $subject The subject of the message
 * @param string $message The message
 * @param int $petInterest The ID of the pet the person is interested in, if any
 * @return bool True if the submission was saved, false otherwise
 */
function saveContactSubmission($conn, $name, $email, $phone, $subject, $message, $petInterest = 0) {
    try {
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message, pet_interest, status, created_at) 
                VALUES (:name, :email, :phone, :subject, :message, :pet_interest, :status, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':pet_interest', $petInterest);
        
        $status = 'new';
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get recent messages from the database
 * 
 * @param PDO $conn The database connection
 * @param int $limit The maximum number of messages to return
 * @return array|bool An array of messages or false on error
 */
function getRecentMessages($conn, $limit = 5) {
    try {
        $sql = "SELECT m.*, p.name as pet_name 
                FROM contact_messages m 
                LEFT JOIN pets p ON m.pet_interest = p.id 
                ORDER BY m.created_at DESC 
                LIMIT :limit";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if database tables exist, and if not, create them
 * This function is used for initial setup
 * 
 * @param PDO $conn The database connection
 * @return bool True if tables were created or already exist, false on error
 */
function createTablesIfNotExist($conn) {
    try {
        // Check if pets table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'pets'");
        $petsTableExists = $stmt->rowCount() > 0;
        
        // Check if users table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'users'");
        $usersTableExists = $stmt->rowCount() > 0;
        
        // Check if contact_messages table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'contact_messages'");
        $messagesTableExists = $stmt->rowCount() > 0;
        
        // Create tables if they don't exist
        if (!$petsTableExists) {
            $conn->exec("CREATE TABLE pets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                species VARCHAR(50) NOT NULL,
                breed VARCHAR(100),
                age INT,
                gender VARCHAR(20),
                size VARCHAR(20),
                description TEXT,
                image_url VARCHAR(255),
                status VARCHAR(20) DEFAULT 'available',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }
        
        if (!$usersTableExists) {
            $conn->exec("CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                role VARCHAR(20) DEFAULT 'admin',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Create default admin user
            $username = 'admin';
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $email = 'admin@example.com';
            $role = 'admin';
            
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
        }
        
        if (!$messagesTableExists) {
            $conn->exec("CREATE TABLE contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20),
                subject VARCHAR(200),
                message TEXT,
                pet_interest INT,
                status VARCHAR(20) DEFAULT 'new',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (pet_interest) REFERENCES pets(id) ON DELETE SET NULL
            )");
        }
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}