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
    $species = isset($_POST['species']) ? trim($_POST['species']) : '';
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
    
    if (empty($species)) {
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
    
    // If no errors, add pet
    if (empty($errors)) {
        $petId = addPet($conn, $name, $species, $breed, $age, $gender, $size, $description, $image_url, $status);
        
        if ($petId) {
            $success = true;
        } else {
            $errors[] = 'Failed to add pet. Please try again.';
        }
    }
}

// Set page title
$pageTitle = "Add New Pet - PetPal Adoption Center";
?>

<?php include 'includes/admin_header.php'; ?>

<div class="admin-container">
    <?php include 'includes/admin_sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-header">
            <h1>Add New Pet</h1>
            <a href="pets.php" class="btn btn-outline">Back to Pets</a>
        </div>

        <?php if ($success): ?>
            <div class="success-message">
                <p>Pet has been added successfully!</p>
                <div class="success-actions">
                    <a href="add_pet.php" class="btn btn-outline">Add Another Pet</a>
                    <a href="pets.php" class="btn btn-primary">View All Pets</a>
                </div>
            </div>
        <?php else: ?>
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
                <form action="add_pet.php" method="POST" class="admin-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Pet Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="species">Species <span class="required">*</span></label>
                            <input type="text" id="species" name="species" value="<?php echo isset($_POST['species']) ? htmlspecialchars($_POST['species']) : ''; ?>" list="species-list" required>
                            <datalist id="species-list">
                                <?php foreach ($species as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div class="form-group">
                            <label for="breed">Breed <span class="required">*</span></label>
                            <input type="text" id="breed" name="breed" value="<?php echo isset($_POST['breed']) ? htmlspecialchars($_POST['breed']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="age">Age (Years) <span class="required">*</span></label>
                            <input type="number" id="age" name="age" min="0" value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Gender <span class="required">*</span></label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <?php foreach ($genders as $g): ?>
                                    <option value="<?php echo htmlspecialchars($g); ?>" <?php echo isset($_POST['gender']) && $_POST['gender'] === $g ? 'selected' : ''; ?>>
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
                                    <option value="<?php echo htmlspecialchars($s); ?>" <?php echo isset($_POST['size']) && $_POST['size'] === $s ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status <span class="required">*</span></label>
                            <select id="status" name="status" required>
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s); ?>" <?php echo isset($_POST['status']) && $_POST['status'] === $s ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(htmlspecialchars($s)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="image_url">Image URL <span class="required">*</span></label>
                            <input type="url" id="image_url" name="image_url" value="<?php echo isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : ''; ?>" required>
                            <small>Enter a URL to an image (e.g., https://example.com/image.jpg)</small>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="description">Description <span class="required">*</span></label>
                        <textarea id="description" name="description" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-submit">
                        <button type="submit" class="btn btn-primary">Add Pet</button>
                        <a href="pets.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include 'includes/admin_footer.php'; ?>