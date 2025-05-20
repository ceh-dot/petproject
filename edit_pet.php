<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/admin_functions.php';

// Check if user is logged in
session_start();
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get pet ID from URL parameter
$petId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect if no pet ID provided
if ($petId === 0) {
    header('Location: pets.php');
    exit;
}

// Get pet details
$pet = getPetById($conn, $petId);

// Redirect if pet not found
if (!$pet) {
    header('Location: pets.php');
    exit;
}

// Get species and sizes for dropdown
$species = getAllSpecies($conn);
$sizes = ['Small', 'Medium', 'Large', 'Extra Large'];
$genders = ['Male', 'Female'];
$statuses = ['available', 'adopted', 'pending'];

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $petSpecies = isset($_POST['species']) ? trim($_POST['species']) : '';
    $breed = isset($_POST['breed']) ? trim($_POST['breed']) : '';
    $age = isset($_POST['age']) ? (int)$_POST['age'] : 0;
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $size = isset($_POST['size']) ? trim($_POST['size']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $image_url = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'available';
    
    // Validate form data
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($petSpecies)) {
        $errors[] = 'Species is required';
    }
    
    if (empty($breed)) {
        $errors[] = 'Breed is required';
    }
    
    if ($age <= 0) {
        $errors[] = 'Age must be greater than 0';
    }
    
    if (empty($gender)) {
        $errors[] = 'Gender is required';
    }
    
    if (empty($size)) {
        $errors[] = 'Size is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Description is required';
    }
    
    if (empty($image_url)) {
        $errors[] = 'Image URL is required';
    }
    
    // If no errors, update pet
    if (empty($errors)) {
        $updated = updatePet($conn, $petId, $name, $petSpecies, $breed, $age, $gender, $size, $description, $image_url, $status);
        
        if ($updated) {
            $success = true;
            // Refresh pet data
            $pet = getPetById($conn, $petId);
        } else {
            $errors[] = 'Failed to update pet. Please try again.';
        }
    }
}

// Set page title
$pageTitle = "Edit Pet: " . htmlspecialchars($pet['name']) . " - PetPal Adoption Center";
?>

<?php include 'includes/admin_header.php'; ?>

<div class="admin-container">
    <?php include 'includes/admin_sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-header">
            <h1>Edit Pet: <?php echo htmlspecialchars($pet['name']); ?></h1>
            <div class="header-actions">
                <a href="../pet_details.php?id=<?php echo $pet['id']; ?>" class="btn btn-outline" target="_blank">View Pet</a>
                <a href="pets.php" class="btn btn-outline">Back to Pets</a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="success-message">
                <p>Pet has been updated successfully!</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="admin-form-container">
            <form action="edit_pet.php?id=<?php echo $petId; ?>" method="POST" class="admin-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Pet Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="species">Species <span class="required">*</span></label>
                        <input type="text" id="species" name="species" value="<?php echo htmlspecialchars($pet['species']); ?>" list="species-list" required>
                        <datalist id="species-list">
                            <?php foreach ($species as $s): ?>
                                <option value="<?php echo htmlspecialchars($s); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="form-group">
                        <label for="breed">Breed <span class="required">*</span></label>
                        <input type="text" id="breed" name="breed" value="<?php echo htmlspecialchars($pet['breed']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="age">Age (Years) <span class="required">*</span></label>
                        <input type="number" id="age" name="age" min="0" value="<?php echo htmlspecialchars($pet['age']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <?php foreach ($genders as $g): ?>
                                <option value="<?php echo htmlspecialchars($g); ?>" <?php echo $pet['gender'] === $g ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="size">Size <span class="required">*</span></label>
                        <select id="size" name="size" required>
                            <option value="">Select Size</option>
                            <?php foreach ($sizes as $s): ?>
                                <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $pet['size'] === $s ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select id="status" name="status" required>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $pet['status'] === $s ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(htmlspecialchars($s)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url">Image URL <span class="required">*</span></label>
                        <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($pet['image_url']); ?>" required>
                        <small>Enter a URL to an image (e.g., https://example.com/image.jpg)</small>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($pet['description']); ?></textarea>
                </div>
                
                <div class="form-submit">
                    <button type="submit" class="btn btn-primary">Update Pet</button>
                    <a href="pets.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
        
        <div class="admin-preview">
            <h2>Current Pet Image</h2>
            <div class="preview-image">
                <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
            </div>
        </div>
    </main>
</div>

<?php include 'includes/admin_footer.php'; ?>