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

// Handle pet deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $petId = (int)$_GET['delete'];
    $deleted = deletePet($conn, $petId);
    
    if ($deleted) {
        $deleteMessage = "Pet has been deleted successfully.";
    } else {
        $deleteError = "Failed to delete pet. Please try again.";
    }
}

// Get filters from URL parameters
$species = isset($_GET['species']) ? $_GET['species'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$petsPerPage = 15;
$offset = ($page - 1) * $petsPerPage;

// Get filtered pets for admin
$pets = getAdminPets($conn, $species, $status, $search, $petsPerPage, $offset);

// Get total count for pagination
$totalPets = getAdminPetsCount($conn, $species, $status, $search);
$totalPages = ceil($totalPets / $petsPerPage);

// Get all species for filter dropdown
$allSpecies = getAllSpecies($conn);

// Set page title
$pageTitle = "Manage Pets - PetPal Adoption Center";
?>

<?php include 'includes/admin_header.php'; ?>

<div class="admin-container">
    <?php include 'includes/admin_sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-header">
            <h1>Manage Pets</h1>
            <a href="add_pet.php" class="btn btn-primary">Add New Pet</a>
        </div>

        <?php if (isset($deleteMessage)): ?>
            <div class="success-message">
                <p><?php echo $deleteMessage; ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($deleteError)): ?>
            <div class="error-message">
                <p><?php echo $deleteError; ?></p>
            </div>
        <?php endif; ?>

        <div class="admin-filter-bar">
            <form action="pets.php" method="GET" class="admin-filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="species">Species</label>
                        <select name="species" id="species">
                            <option value="">All Species</option>
                            <?php foreach ($allSpecies as $s): ?>
                                <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $species === $s ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="">All Statuses</option>
                            <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="adopted" <?php echo $status === 'adopted' ? 'selected' : ''; ?>>Adopted</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        </select>
                    </div>
                    
                    <div class="filter-group search-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" placeholder="Search pets..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="pets.php" class="btn btn-outline">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="admin-table-container">
            <div class="table-header">
                <p>Showing <?php echo count($pets); ?> of <?php echo $totalPets; ?> pets</p>
            </div>
            
            <?php if ($pets && count($pets) > 0): ?>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Species</th>
                                <th>Breed</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pets as $pet): ?>
                                <tr>
                                    <td><?php echo $pet['id']; ?></td>
                                    <td>
                                        <div class="pet-thumbnail">
                                            <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($pet['name']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['species']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['age']); ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?></td>
                                    <td><?php echo htmlspecialchars($pet['gender']); ?></td>
                                    <td>
                                        <span class="status-pill <?php echo $pet['status']; ?>">
                                            <?php echo ucfirst(htmlspecialchars($pet['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($pet['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_pet.php?id=<?php echo $pet['id']; ?>" class="btn-icon edit-icon" title="Edit Pet">Edit</a>
                                            <a href="../pet_details.php?id=<?php echo $pet['id']; ?>" class="btn-icon view-icon" title="View Pet" target="_blank">View</a>
                                            <a href="pets.php?delete=<?php echo $pet['id']; ?>" class="btn-icon delete-icon" title="Delete Pet" onclick="return confirm('Are you sure you want to delete this pet? This action cannot be undone.')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="admin-pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&species=<?php echo urlencode($species); ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>" class="pagination-prev">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&species=<?php echo urlencode($species); ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&species=<?php echo urlencode($species); ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>" class="pagination-next">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-data">
                    <p>No pets found matching your criteria.</p>
                    <?php if (!empty($species) || !empty($status) || !empty($search)): ?>
                        <a href="pets.php" class="btn btn-primary">Clear Filters</a>
                    <?php else: ?>
                        <a href="add_pet.php" class="btn btn-primary">Add New Pet</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'includes/admin_footer.php'; ?>