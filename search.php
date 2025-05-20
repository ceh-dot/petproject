<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/pet_functions.php';

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Redirect if no query provided
if (empty($query)) {
    header('Location: pets.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$petsPerPage = 12;
$offset = ($page - 1) * $petsPerPage;

// Get search results
$searchResults = searchPets($conn, $query, $petsPerPage, $offset);

// Get total count for pagination
$totalPets = getTotalSearchResults($conn, $query);
$totalPages = ceil($totalPets / $petsPerPage);

// Set page title
$pageTitle = "Search Results for \"" . htmlspecialchars($query) . "\" - PetPal Adoption Center";
?>

<?php include 'includes/header.php'; ?>

<main>
    <section class="search-header">
        <div class="container">
            <h1>Search Results</h1>
            <p>Showing results for: <strong><?php echo htmlspecialchars($query); ?></strong></p>
            
            <div class="search-container">
                <form action="search.php" method="GET">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search for pets..." required>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
    </section>

    <section class="search-results container">
        <div class="results-header">
            <h2><?php echo $totalPets; ?> <?php echo $totalPets === 1 ? 'Pet' : 'Pets'; ?> Found</h2>
        </div>

        <?php if ($searchResults && count($searchResults) > 0): ?>
            <div class="pet-card-container">
                <?php foreach ($searchResults as $pet): ?>
                    <div class="pet-card">
                        <div class="pet-card-image">
                            <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                            <?php if ($pet['status'] === 'adopted'): ?>
                                <span class="status-badge adopted">Adopted</span>
                            <?php else: ?>
                                <span class="status-badge available">Available</span>
                            <?php endif; ?>
                        </div>
                        <div class="pet-card-content">
                            <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                            <p class="pet-breed"><?php echo htmlspecialchars($pet['breed']); ?></p>
                            <p class="pet-details">
                                <span><?php echo htmlspecialchars($pet['age']); ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?></span> • 
                                <span><?php echo htmlspecialchars($pet['gender']); ?></span> • 
                                <span><?php echo htmlspecialchars($pet['size']); ?></span>
                            </p>
                            <a href="pet_details.php?id=<?php echo $pet['id']; ?>" class="btn btn-outline">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>" class="pagination-prev">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>" class="pagination-next">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <p>No pets found matching "<?php echo htmlspecialchars($query); ?>".</p>
                <div class="no-results-suggestions">
                    <h3>Suggestions:</h3>
                    <ul>
                        <li>Check your spelling</li>
                        <li>Try more general keywords</li>
                        <li>Try different keywords</li>
                    </ul>
                </div>
                <a href="pets.php" class="btn btn-primary">Browse All Pets</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'includes/footer.php'; ?>