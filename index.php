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

// Get dashboard data
$stats = getDashboardStats($conn);
$recentPets = getRecentPets($conn, 5);
$recentMessages = getRecentMessages($conn, 5);

// Set page title
$pageTitle = "Admin Dashboard - PetPal Adoption Center";
?>

<?php include 'includes/admin_header.php'; ?>

<div class="admin-container">
    <?php include 'includes/admin_sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon pets-icon"></div>
                <div class="stat-content">
                    <h3>Total Pets</h3>
                    <p class="stat-number"><?php echo $stats['total_pets']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon available-icon"></div>
                <div class="stat-content">
                    <h3>Available</h3>
                    <p class="stat-number"><?php echo $stats['available_pets']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon adopted-icon"></div>
                <div class="stat-content">
                    <h3>Adopted</h3>
                    <p class="stat-number"><?php echo $stats['adopted_pets']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon messages-icon"></div>
                <div class="stat-content">
                    <h3>Messages</h3>
                    <p class="stat-number"><?php echo $stats['total_messages']; ?></p>
                </div>
            </div>
        </div>

        <div class="admin-sections">
            <div class="admin-section">
                <div class="section-header">
                    <h2>Recently Added Pets</h2>
                    <a href="pets.php" class="btn btn-outline btn-sm">View All</a>
                </div>
                
                <?php if ($recentPets && count($recentPets) > 0): ?>
                    <div class="admin-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Species</th>
                                    <th>Status</th>
                                    <th>Date Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPets as $pet): ?>
                                    <tr>
                                        <td>
                                            <div class="pet-thumbnail">
                                                <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($pet['name']); ?></td>
                                        <td><?php echo htmlspecialchars($pet['species']); ?></td>
                                        <td>
                                            <span class="status-pill <?php echo $pet['status']; ?>">
                                                <?php echo ucfirst(htmlspecialchars($pet['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($pet['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_pet.php?id=<?php echo $pet['id']; ?>" class="btn-icon edit-icon" title="Edit Pet">Edit</a>
                                                <a href="../pet_details.php?id=<?php echo $pet['id']; ?>" class="btn-icon view-icon" title="View Pet">View</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-data">No pets added yet.</p>
                <?php endif; ?>
            </div>

            <div class="admin-section">
                <div class="section-header">
                    <h2>Recent Messages</h2>
                    <a href="messages.php" class="btn btn-outline btn-sm">View All</a>
                </div>
                
                <?php if ($recentMessages && count($recentMessages) > 0): ?>
                    <div class="admin-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Pet Interest</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMessages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                                        <td>
                                            <?php if ($message['pet_interest']): ?>
                                                <a href="../pet_details.php?id=<?php echo $message['pet_interest']; ?>">
                                                    <?php echo htmlspecialchars($message['pet_name']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span>-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-pill <?php echo $message['status']; ?>">
                                                <?php echo ucfirst(htmlspecialchars($message['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="view_message.php?id=<?php echo $message['id']; ?>" class="btn-icon view-icon" title="View Message">View</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-data">No messages received yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/admin_footer.php'; ?>