<?php
/**
 * Functions for admin functionality
 */

/**
 * Get user by username
 * 
 * @param PDO $conn The database connection
 * @param string $username The username to look up
 * @return array|bool The user data or false if not found
 */
function getUserByUsername($conn, $username) {
    try {
        $sql = "SELECT * FROM users WHERE username = :username";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        return $user;
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get dashboard statistics
 * 
 * @param PDO $conn The database connection
 * @return array Statistics for the dashboard
 */
function getDashboardStats($conn) {
    try {
        $stats = [];
        
        // Total pets
        $stmt = $conn->query("SELECT COUNT(*) FROM pets");
        $stats['total_pets'] = $stmt->fetchColumn();
        
        // Available pets
        $stmt = $conn->query("SELECT COUNT(*) FROM pets WHERE status = 'available'");
        $stats['available_pets'] = $stmt->fetchColumn();
        
        // Adopted pets
        $stmt = $conn->query("SELECT COUNT(*) FROM pets WHERE status = 'adopted'");
        $stats['adopted_pets'] = $stmt->fetchColumn();
        
        // Total messages
        $stmt = $conn->query("SELECT COUNT(*) FROM contact_messages");
        $stats['total_messages'] = $stmt->fetchColumn();
        
        return $stats;
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return [
            'total_pets' => 0,
            'available_pets' => 0,
            'adopted_pets' => 0,
            'total_messages' => 0
        ];
    }
}

/**
 * Add a new pet to the database
 * 
 * @param PDO $conn The database connection
 * @param string $name The pet's name
 * @param string $species The pet's species
 * @param string $breed The pet's breed
 * @param int $age The pet's age
 * @param string $gender The pet's gender
 * @param string $size The pet's size
 * @param string $description The pet's description
 * @param string $image_url The URL to the pet's image
 * @param string $status The pet's status
 * @return int|bool The new pet's ID or false on error
 */
function addPet($conn, $name, $species, $breed, $age, $gender, $size, $description, $image_url, $status = 'available') {
    try {
        $sql = "INSERT INTO pets (name, species, breed, age, gender, size, description, image_url, status, created_at) 
                VALUES (:name, :species, :breed, :age, :gender, :size, :description, :image_url, :status, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':species', $species);
        $stmt->bindParam(':breed', $breed);
        $stmt->bindParam(':age', $age, PDO::PARAM_INT);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':status', $status);
        
        $stmt->execute();
        
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update an existing pet in the database
 * 
 * @param PDO $conn The database connection
 * @param int $id The pet's ID
 * @param string $name The pet's name
 * @param string $species The pet's species
 * @param string $breed The pet's breed
 * @param int $age The pet's age
 * @param string $gender The pet's gender
 * @param string $size The pet's size
 * @param string $description The pet's description
 * @param string $image_url The URL to the pet's image
 * @param string $status The pet's status
 * @return bool True if the update was successful, false otherwise
 */
function updatePet($conn, $id, $name, $species, $breed, $age, $gender, $size, $description, $image_url, $status) {
    try {
        $sql = "UPDATE pets 
                SET name = :name, 
                    species = :species, 
                    breed = :breed, 
                    age = :age, 
                    gender = :gender, 
                    size = :size, 
                    description = :description, 
                    image_url = :image_url, 
                    status = :status 
                WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':species', $species);
        $stmt->bindParam(':breed', $breed);
        $stmt->bindParam(':age', $age, PDO::PARAM_INT);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a pet from the database
 * 
 * @param PDO $conn The database connection
 * @param int $id The pet's ID
 * @return bool True if the deletion was successful, false otherwise
 */
function deletePet($conn, $id) {
    try {
        // First update any messages that reference this pet
        $sql = "UPDATE contact_messages SET pet_interest = NULL WHERE pet_interest = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Now delete the pet
        $sql = "DELETE FROM pets WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get admin pets with filtering and pagination
 * 
 * @param PDO $conn The database connection
 * @param string $species The species filter
 * @param string $status The status filter
 * @param string $search The search query
 * @param int $limit The maximum number of pets to return
 * @param int $offset The offset for pagination
 * @return array|bool An array of pets or false on error
 */
function getAdminPets($conn, $species, $status, $search, $limit = 15, $offset = 0) {
    try {
        $params = [];
        $conditions = [];
        
        // Build query conditions based on filters
        if (!empty($species)) {
            $conditions[] = "species = :species";
            $params[':species'] = $species;
        }
        
        if (!empty($status)) {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
        }
        
        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $conditions[] = "(name LIKE :search OR breed LIKE :search OR description LIKE :search)";
            $params[':search'] = $searchParam;
        }
        
        // Build the final query
        $sql = "SELECT * FROM pets";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get the total count of pets for admin with filters
 * 
 * @param PDO $conn The database connection
 * @param string $species The species filter
 * @param string $status The status filter
 * @param string $search The search query
 * @return int|bool The count of matching pets or false on error
 */
function getAdminPetsCount($conn, $species, $status, $search) {
    try {
        $params = [];
        $conditions = [];
        
        // Build query conditions based on filters
        if (!empty($species)) {
            $conditions[] = "species = :species";
            $params[':species'] = $species;
        }
        
        if (!empty($status)) {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
        }
        
        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $conditions[] = "(name LIKE :search OR breed LIKE :search OR description LIKE :search)";
            $params[':search'] = $searchParam;
        }
        
        // Build the final query
        $sql = "SELECT COUNT(*) FROM pets";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get recent pets from the database
 * 
 * @param PDO $conn The database connection
 * @param int $limit The maximum number of pets to return
 * @return array|bool An array of pets or false on error
 */
function getRecentPets($conn, $limit = 5) {
    try {
        $sql = "SELECT * FROM pets ORDER BY created_at DESC LIMIT :limit";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all distinct species from the pets table
 * 
 * @param PDO $conn The database connection
 * @return array|bool An array of species or false on error
 */
function getAllSpecies($conn) {
    try {
        $sql = "SELECT DISTINCT species FROM pets ORDER BY species ASC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}