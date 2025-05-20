<?php
/**
 * Functions for managing pets in the database
 */

/**
 * Get a list of featured pets
 * 
 * @param PDO $conn The database connection
 * @param int $limit The maximum number of pets to return
 * @return array|bool An array of pets or false on error
 */
function getFeaturedPets($conn, $limit = 3) {
    try {
        $sql = "SELECT * FROM pets WHERE status = 'available' ORDER BY RAND() LIMIT :limit";
        
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
 * Get a list of recently added pets
 * 
 * @param PDO $conn The database connection
 * @param int $limit The maximum number of pets to return
 * @return array|bool An array of pets or false on error
 */
function getRecentPets($conn, $limit = 6) {
    try {
        $sql = "SELECT * FROM pets ORDER BY created_at DESC LIMIT :limit";
        
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
 * Get pet categories with count
 * 
 * @param PDO $conn The database connection
 * @return array|bool An array of categories or false on error
 */
function getPetCategories($conn) {
    try {
        $sql = "SELECT species, COUNT(*) as count FROM pets WHERE status = 'available' GROUP BY species ORDER BY count DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all unique species
 * 
 * @param PDO $conn The database connection
 * @return array|bool An array of species or false on error
 */
function getAllSpecies($conn) {
    try {
        $sql = "SELECT DISTINCT species FROM pets ORDER BY species";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $species = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $species;
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get a specific pet by ID
 * 
 * @param PDO $conn The database connection
 * @param int $id The ID of the pet
 * @return array|bool The pet data or false if not found
 */
function getPetById($conn, $id) {
    try {
        $sql = "SELECT * FROM pets WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $pet = $stmt->fetch();
        
        if (!$pet) {
            return false;
        }
        
        return $pet;
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get similar pets based on species
 * 
 * @param PDO $conn The database connection
 * @param string $species The species to find similar pets for
 * @param int $excludeId The ID of the pet to exclude
 * @param int $limit The maximum number of pets to return
 * @return array|bool An array of similar pets or false on error
 */
function getSimilarPets($conn, $species, $excludeId, $limit = 3) {
    try {
        $sql = "SELECT * FROM pets WHERE species = :species AND id != :exclude_id AND status = 'available' ORDER BY RAND() LIMIT :limit";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':species', $species);
        $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
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
 * Get filtered pets
 * 
 * @param PDO $conn The database connection
 * @param string $category The category/species filter
 * @param string $age The age filter
 * @param string $size The size filter
 * @param string $gender The gender filter
 * @param string $status The status filter
 * @param int $limit The maximum number of pets to return
 * @param int $offset The offset for pagination
 * @return array|bool An array of filtered pets or false on error
 */
function getFilteredPets($conn, $category, $age, $size, $gender, $status, $limit = 12, $offset = 0) {
    try {
        $params = [];
        $conditions = [];
        
        // Build query conditions based on filters
        if (!empty($category)) {
            $conditions[] = "species = :category";
            $params[':category'] = $category;
        }
        
        if (!empty($age)) {
            // Age filter logic based on categories
            switch ($age) {
                case 'Puppy/Kitten':
                    $conditions[] = "age <= 1";
                    break;
                case 'Young':
                    $conditions[] = "age > 1 AND age <= 3";
                    break;
                case 'Adult':
                    $conditions[] = "age > 3 AND age <= 8";
                    break;
                case 'Senior':
                    $conditions[] = "age > 8";
                    break;
            }
        }
        
        if (!empty($size)) {
            $conditions[] = "size = :size";
            $params[':size'] = $size;
        }
        
        if (!empty($gender)) {
            $conditions[] = "gender = :gender";
            $params[':gender'] = $gender;
        }
        
        if ($status !== 'all') {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
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
 * Get the total count of pets matching the filters
 * 
 * @param PDO $conn The database connection
 * @param string $category The category/species filter
 * @param string $age The age filter
 * @param string $size The size filter
 * @param string $gender The gender filter
 * @param string $status The status filter
 * @return int|bool The count of matching pets or false on error
 */
function getTotalPetsCount($conn, $category, $age, $size, $gender, $status) {
    try {
        $params = [];
        $conditions = [];
        
        // Build query conditions based on filters
        if (!empty($category)) {
            $conditions[] = "species = :category";
            $params[':category'] = $category;
        }
        
        if (!empty($age)) {
            // Age filter logic based on categories
            switch ($age) {
                case 'Puppy/Kitten':
                    $conditions[] = "age <= 1";
                    break;
                case 'Young':
                    $conditions[] = "age > 1 AND age <= 3";
                    break;
                case 'Adult':
                    $conditions[] = "age > 3 AND age <= 8";
                    break;
                case 'Senior':
                    $conditions[] = "age > 8";
                    break;
            }
        }
        
        if (!empty($size)) {
            $conditions[] = "size = :size";
            $params[':size'] = $size;
        }
        
        if (!empty($gender)) {
            $conditions[] = "gender = :gender";
            $params[':gender'] = $gender;
        }
        
        if ($status !== 'all') {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
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
 * Search for pets by keyword
 * 
 * @param PDO $conn The database connection
 * @param string $query The search query
 * @param int $limit The maximum number of pets to return
 * @param int $offset The offset for pagination
 * @return array|bool An array of matching pets or false on error
 */
function searchPets($conn, $query, $limit = 12, $offset = 0) {
    try {
        $searchParam = '%' . $query . '%';
        
        $sql = "SELECT * FROM pets WHERE 
                name LIKE :query OR 
                species LIKE :query OR 
                breed LIKE :query OR 
                description LIKE :query 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':query', $searchParam);
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
 * Get the total count of pets matching a search query
 * 
 * @param PDO $conn The database connection
 * @param string $query The search query
 * @return int|bool The count of matching pets or false on error
 */
function getTotalSearchResults($conn, $query) {
    try {
        $searchParam = '%' . $query . '%';
        
        $sql = "SELECT COUNT(*) FROM pets WHERE 
                name LIKE :query OR 
                species LIKE :query OR 
                breed LIKE :query OR 
                description LIKE :query";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':query', $searchParam);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}