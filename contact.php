<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/pet_functions.php';

// Check if pet_id is provided
$petId = isset($_GET['pet_id']) ? (int)$_GET['pet_id'] : 0;
$pet = null;

// Get pet details if ID is provided
if ($petId > 0) {
    $pet = getPetById($conn, $petId);
}

// Handle form submission
$formSubmitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $petInterest = isset($_POST['pet_interest']) ? (int)$_POST['pet_interest'] : 0;
    
    // Validate form data
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    // If no errors, save contact submission
    if (empty($errors)) {
        $success = saveContactSubmission($conn, $name, $email, $phone, $subject, $message, $petInterest);
        
        if ($success) {
            $formSubmitted = true;
        } else {
            $errors[] = 'Failed to submit your message. Please try again later.';
        }
    }
}

// Set page title
$pageTitle = "Contact Us - PetPal Adoption Center";
?>

<?php include 'includes/header.php'; ?>

<main>
    <section class="contact-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you! Fill out the form below to get in touch with our team.</p>
        </div>
    </section>

    <section class="contact-content container">
        <div class="contact-info">
            <div class="contact-info-card">
                <h2>Adoption Center</h2>
                <div class="info-item">
                    <span class="info-label">Address:</span>
                    <span class="info-value">123 Pet Street, Anytown, AN 12345</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">(555) 123-4567</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">info@petpal.example</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Hours:</span>
                    <span class="info-value">
                        Monday - Friday: 9am - 6pm<br>
                        Saturday: 10am - 5pm<br>
                        Sunday: 12pm - 4pm
                    </span>
                </div>
            </div>

            <div class="location-map">
                <h3>Our Location</h3>
                <div class="map-placeholder">
                    <p>Map location would be displayed here</p>
                </div>
            </div>
        </div>

        <div class="contact-form-container">
            <?php if ($formSubmitted): ?>
                <div class="form-success">
                    <h2>Thank You!</h2>
                    <p>Your message has been sent successfully. We'll get back to you as soon as possible.</p>
                    <?php if ($petId > 0 && $pet): ?>
                        <p>Thank you for your interest in <?php echo htmlspecialchars($pet['name']); ?>. Our adoption coordinator will contact you shortly to discuss the next steps.</p>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-primary">Return to Home</a>
                </div>
            <?php else: ?>
                <div class="contact-form">
                    <h2><?php echo ($petId > 0 && $pet) ? 'Adoption Inquiry for ' . htmlspecialchars($pet['name']) : 'Send Us a Message'; ?></h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="form-errors">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="contact.php<?php echo ($petId > 0) ? '?pet_id=' . $petId : ''; ?>" method="POST">
                        <div class="form-group">
                            <label for="name">Your Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" required value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ($petId > 0 && $pet ? 'Inquiry about ' . htmlspecialchars($pet['name']) : ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <?php if ($petId > 0 && $pet): ?>
                            <input type="hidden" name="pet_interest" value="<?php echo $petId; ?>">
                        <?php endif; ?>
                        
                        <div class="form-submit">
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>