<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/pet_functions.php';

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

// Get similar pets
$similarPets = getSimilarPets($conn, $pet['species'], $pet['id'], 3);

// Set page title
$pageTitle = htmlspecialchars($pet['name']) . " - PetPal Adoption Center";
?>

<?php include 'includes/header.php'; ?>

<main>
    <section class="pet-details">
        <div class="container">
            <div class="pet-details-header">
                <div class="breadcrumbs">
                    <a href="index.php">Home</a> &gt; 
                    <a href="pets.php">Pets</a> &gt; 
                    <a href="pets.php?category=<?php echo urlencode($pet['species']); ?>"><?php echo htmlspecialchars($pet['species']); ?></a> &gt; 
                    <span><?php echo htmlspecialchars($pet['name']); ?></span>
                </div>
                
                <div class="pet-header-content">
                    <h1><?php echo htmlspecialchars($pet['name']); ?></h1>
                    <div class="pet-badge-container">
                        <?php if ($pet['status'] === 'adopted'): ?>
                            <span class="status-badge large-badge adopted">Adopted</span>
                        <?php else: ?>
                            <span class="status-badge large-badge available">Available for Adoption</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="pet-details-content">
                <div class="pet-primary-image">
                    <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                </div>
                
                <div class="pet-info-container">
                    <div class="pet-info-section">
                        <h2>About <?php echo htmlspecialchars($pet['name']); ?></h2>
                        <div class="pet-summary">
                            <div class="pet-attribute">
                                <span class="attribute-label">Breed</span>
                                <span class="attribute-value"><?php echo htmlspecialchars($pet['breed']); ?></span>
                            </div>
                            <div class="pet-attribute">
                                <span class="attribute-label">Age</span>
                                <span class="attribute-value"><?php echo htmlspecialchars($pet['age']); ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?></span>
                            </div>
                            <div class="pet-attribute">
                                <span class="attribute-label">Gender</span>
                                <span class="attribute-value"><?php echo htmlspecialchars($pet['gender']); ?></span>
                            </div>
                            <div class="pet-attribute">
                                <span class="attribute-label">Size</span>
                                <span class="attribute-value"><?php echo htmlspecialchars($pet['size']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="pet-info-section">
                        <h3>Description</h3>
                        <div class="pet-description">
                            <p><?php echo nl2br(htmlspecialchars($pet['description'])); ?></p>
                        </div>
                    </div>

                    <?php if ($pet['status'] === 'available'): ?>
                        <div class="pet-adoption-container">
                            <h3>Interested in adopting <?php echo htmlspecialchars($pet['name']); ?>?</h3>
                            <a href="contact.php?pet_id=<?php echo $pet['id']; ?>" class="btn btn-primary">Inquire About This Pet</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($pet['status'] === 'available'): ?>
                <div class="adoption-info">
                    <h3>Adoption Information</h3>
                    <div class="adoption-steps">
                        <div class="adoption-step">
                            <span class="step-number">1</span>
                            <div class="step-content">
                                <h4>Submit an Inquiry</h4>
                                <p>Fill out our adoption inquiry form to express interest in <?php echo htmlspecialchars($pet['name']); ?>.</p>
                            </div>
                        </div>
                        <div class="adoption-step">
                            <span class="step-number">2</span>
                            <div class="step-content">
                                <h4>Meet and Greet</h4>
                                <p>Schedule a time to meet <?php echo htmlspecialchars($pet['name']); ?> in person at our facility.</p>
                            </div>
                        </div>
                        <div class="adoption-step">
                            <span class="step-number">3</span>
                            <div class="step-content">
                                <h4>Complete Application</h4>
                                <p>If it's a match, complete our adoption application and home check process.</p>
                            </div>
                        </div>
                        <div class="adoption-step">
                            <span class="step-number">4</span>
                            <div class="step-content">
                                <h4>Welcome Home!</h4>
                                <p>Pay the adoption fee and welcome your new family member home!</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (count($similarPets) > 0): ?>
    <section class="similar-pets container">
        <h2>Similar Pets You May Like</h2>
        <div class="pet-card-container">
            <?php foreach ($similarPets as $similarPet): ?>
                <div class="pet-card">
                    <div class="pet-card-image">
                        <img src="<?php echo htmlspecialchars($similarPet['image_url']); ?>" alt="<?php echo htmlspecialchars($similarPet['name']); ?>">
                        <?php if ($similarPet['status'] === 'adopted'): ?>
                            <span class="status-badge adopted">Adopted</span>
                        <?php else: ?>
                            <span class="status-badge available">Available</span>
                        <?php endif; ?>
                    </div>
                    <div class="pet-card-content">
                        <h3><?php echo htmlspecialchars($similarPet['name']); ?></h3>
                        <p class="pet-breed"><?php echo htmlspecialchars($similarPet['breed']); ?></p>
                        <p class="pet-details">
                            <span><?php echo htmlspecialchars($similarPet['age']); ?> <?php echo $similarPet['age'] == 1 ? 'year' : 'years'; ?></span> • 
                            <span><?php echo htmlspecialchars($similarPet['gender']); ?></span> • 
                            <span><?php echo htmlspecialchars($similarPet['size']); ?></span>
                        </p>
                        <a href="pet_details.php?id=<?php echo $similarPet['id']; ?>" class="btn btn-outline">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>