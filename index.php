<?php
$pageTitle = 'Welcome to Prayer Board';
require_once 'config/database.php';
require_once 'includes/header.php';






// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $testimonial_id = $_POST['testimonial_id'] ?? '';

    if ($action === 'add' && !empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO testimonials (user_id, comment) VALUES (?, ?)");
        $stmt->execute([$user_id, $comment]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    if ($action === 'edit' && !empty($comment) && !empty($testimonial_id)) {
        $stmt = $pdo->prepare("UPDATE testimonials SET comment = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$comment, $testimonial_id, $user_id]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    if ($action === 'delete' && !empty($testimonial_id)) {
        $stmt = $pdo->prepare("UPDATE testimonials SET status = 'archived' WHERE id = ? AND user_id = ?");
        $stmt->execute([$testimonial_id, $user_id]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}


?>

<div class="max-w-6xl mx-auto">
    <!-- Hero Section -->
    <div class="text-center py-20">
        <h1 class="text-5xl font-bold mb-6">Organize Your Prayer Life</h1>
        <p class="text-xl text-gray-600 mb-8">Keep track of your prayers, celebrate answered prayers, and grow in your faith journey.</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="space-x-4">
                <a href="register.php" class="bg-blue-500 text-white px-8 py-3 rounded-lg text-lg hover:bg-blue-600">Get Started</a>
                <a href="login.php" class="text-blue-500 hover:text-blue-600">Already have an account?</a>
            </div>
        <?php else: ?>
            <a href="./dashboard.php" class="bg-blue-500 text-white px-8 py-3 rounded-lg text-lg hover:bg-blue-600">Go to Dashboard</a>
        <?php endif; ?>
    </div>

    <!-- Features Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 py-16">
    <div class="text-center p-6">
            <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">Disclaimer</h2>
            <p class="text-gray-600">Don't use this for real life applications as I just created it during my free time and it is not optimized 
                for live commecial deployments and will keep adding more features to it in the future. For now I will need yourfeeback on changes and aditions you feel
                are good or this website.</p>
        </div>
        <div class="text-center p-6">
            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">Create Prayer Boards</h2>
            <p class="text-gray-600">Organize your prayers into custom categories and keep track of your prayer requests.</p>
        </div>
        
        <div class="text-center p-6">
            <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">Track Answered Prayers</h2>
            <p class="text-gray-600">Celebrate God's faithfulness by recording and reflecting on answered prayers.</p>
        </div>
        
        <div class="text-center p-6">
            <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">Customize Your Boards</h2>
            <p class="text-gray-600">Create personalized categories and organize your prayer life your way.</p>
        </div>
    </div>

    
<!-- Testimonials Section -->
<div class="bg-gray-50 py-16 px-4 rounded-lg mb-16">
    <h2 class="text-3xl font-bold text-center mb-12">What Our Users Say</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        // Fetch active testimonials
        $stmt = $pdo->prepare("SELECT t.*, u.username FROM testimonials t 
                              JOIN users u ON t.user_id = u.id 
                              WHERE t.status = 'active' 
                              ORDER BY t.created_at DESC LIMIT 3");
        $stmt->execute();
        $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($testimonials as $row) {
            echo '<div class="bg-white p-6 rounded-lg shadow">';
            echo '<p class="text-gray-600 mb-4">"' . htmlspecialchars($row['comment']) . '"</p>';
            echo '<p class="font-semibold">- ' . htmlspecialchars($row['username']) . '</p>';
            
            // Show edit/delete buttons if user owns this testimonial
            if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']) {
                echo '<div class="mt-4 space-x-2">';
                echo '<button onclick="document.getElementById(\'editModal' . $row['id'] . '\').style.display=\'flex\'" 
                      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Edit</button>';
                echo '<form method="POST" class="inline">';
                echo '<input type="hidden" name="action" value="delete">';
                echo '<input type="hidden" name="testimonial_id" value="' . $row['id'] . '">';
                echo '<button type="submit" onclick="return confirm(\'Are you sure you want to delete this testimonial?\')" 
                      class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Delete</button>';
                echo '</form>';
                echo '</div>';

                // Edit Modal for this testimonial
                echo '<div id="editModal' . $row['id'] . '" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center">';
                echo '<div class="bg-white p-8 rounded-lg max-w-lg w-full">';
                echo '<h3 class="text-2xl font-bold mb-4">Edit Your Testimonial</h3>';
                echo '<form method="POST">';
                echo '<input type="hidden" name="action" value="edit">';
                echo '<input type="hidden" name="testimonial_id" value="' . $row['id'] . '">';
                echo '<textarea name="comment" class="w-full p-4 border rounded-lg mb-4 h-32" required>' . 
                     htmlspecialchars($row['comment']) . '</textarea>';
                echo '<div class="flex justify-end space-x-2">';
                echo '<button type="button" onclick="this.closest(\'#editModal' . $row['id'] . '\').style.display=\'none\'" 
                      class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>';
                echo '<button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Update</button>';
                echo '</div></form></div></div>';
            }
            echo '</div>';
        }

        // Add "Add Testimonial" box if user is logged in
        if(isLoggedIn()) {
            echo '<div onclick="document.getElementById(\'addModal\').style.display=\'flex\'" 
                  class="bg-white p-6 rounded-lg shadow cursor-pointer hover:shadow-lg 
                  flex items-center justify-center min-h-[200px]">';
            echo '<div class="text-center">';
            echo '<svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>';
            echo '<p class="text-gray-600">Add Your Testimonial</p>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<!-- Add Testimonial Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center">
    <div class="bg-white p-8 rounded-lg max-w-lg w-full">
        <h3 class="text-2xl font-bold mb-4">Add Your Testimonial</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <textarea name="comment" 
                      class="w-full p-4 border rounded-lg mb-4 h-32"
                      placeholder="Share your experience..."
                      required></textarea>
            <div class="flex justify-end space-x-2">
                <button type="button" 
                        onclick="document.getElementById('addModal').style.display='none'"
                        class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Submit</button>
            </div>
        </form>
    </div>
</div>
<script>

// Add these JavaScript functions to handle the modal and AJAX requests
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Your Testimonial';
    document.getElementById('testimonialId').value = '';
    document.getElementById('testimonialComment').value = '';
    document.getElementById('testimonialModal').classList.remove('hidden');
}

function openEditModal(id, comment) {
    document.getElementById('modalTitle').textContent = 'Edit Your Testimonial';
    document.getElementById('testimonialId').value = id;
    document.getElementById('testimonialComment').value = comment;
    document.getElementById('testimonialModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('testimonialModal').classList.add('hidden');
}


// Add this function to auto-refresh testimonials periodically
function refreshTestimonials() {
    location.reload();
}

// Refresh testimonials every 5 minutes (300000 milliseconds)
setInterval(refreshTestimonials, 300000);

</script>

<?php require_once 'includes/footer.php'; ?>