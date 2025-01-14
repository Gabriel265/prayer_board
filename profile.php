<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';



// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$message = '';
$error = '';
$password_message = '';
$password_error = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Get user's boards
$stmt = $pdo->prepare("SELECT * FROM prayer_boards WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$boards = $stmt->fetchAll();

// Handle profile picture update
if (isset($_POST['update_picture']) && isset($_FILES['profile_picture'])) {
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $error = 'Invalid file type. Only JPG, PNG and GIF are allowed.';
        } else {
            $upload_dir = 'uploads/profile_pictures/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $file_name = $userId . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
                // Delete old profile picture if exists
                if ($user['profile_picture'] && file_exists($user['profile_picture'])) {
                    unlink($user['profile_picture']);
                }
                
                // Update database with new profile picture path
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->execute([$file_path, $userId]);
                
                $message = 'Profile picture updated successfully';
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            } else {
                $error = 'Failed to upload profile picture';
            }
        }
    }
}

// Handle profile information update
if (isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    // Validate inputs
    if (empty($username) || empty($email)) {
        $error = 'Username and email are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if username or email already exists (excluding current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $userId]);
        if ($stmt->rowCount() > 0) {
            $error = 'Username or email already exists';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->execute([$username, $email, $userId]);
                
                $message = 'Profile updated successfully';
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            } catch (PDOException $e) {
                $error = 'An error occurred while updating profile';
            }
        }
    }
}

// Handle password update
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = 'All password fields are required';
    } elseif (!password_verify($current_password, $user['password_hash'])) {
        $password_error = 'Current password is incorrect';
    } elseif ($new_password !== $confirm_password) {
        $password_error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $password_error = 'New password must be at least 6 characters';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $userId]);
            
            $password_message = 'Password updated successfully';
        } catch (PDOException $e) {
            $password_error = 'An error occurred while updating password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Prayer Board</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Profile Details Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h1 class="text-2xl font-bold mb-6">Profile Settings</h1>
                
                <?php if ($message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Profile Picture Section -->
                    <div class="text-center">
                        <div class="mb-4">
                            <?php if ($user['profile_picture']): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                     alt="Profile Picture" 
                                     class="w-48 h-48 rounded-full mx-auto object-cover">
                            <?php else: ?>
                                <div class="w-48 h-48 rounded-full mx-auto bg-gray-200 flex items-center justify-center">
                                    <span class="text-4xl text-gray-500">
                                        <?php echo htmlspecialchars(strtoupper(substr($user['username'], 0, 1))); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <form action="" method="POST" enctype="multipart/form-data" class="mb-4">
                            <input type="file" name="profile_picture" id="profile_picture" class="hidden" 
                                   accept="image/jpeg,image/png,image/gif">
                            <input type="hidden" name="update_picture" value="1">
                            <label for="profile_picture" 
                                   class="bg-blue-500 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-600">
                                Select New Picture
                            </label>
                            <button type="submit" id="upload-btn" class="mt-2 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 hidden">
                                Upload Picture
                            </button>
                        </form>
                    </div>
                    
                    <!-- Profile Details Form -->
                    <div class="md:col-span-2">
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div>
                                <label class="block text-gray-700 mb-2">Username</label>
                                <input type="text" name="username" class="w-full p-2 border rounded" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" class="w-full p-2 border rounded"
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Save Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Password Change Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Change Password</h2>
                
                <?php if ($password_message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($password_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($password_error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($password_error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="update_password" value="1">
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Current Password</label>
                        <input type="password" name="current_password" class="w-full p-2 border rounded" required>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">New Password</label>
                        <input type="password" name="new_password" class="w-full p-2 border rounded" required>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="w-full p-2 border rounded" required>
                    </div>
                    
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Change Password
                    </button>
                </form>
            </div>
            
            <!-- Boards Summary Section -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Your Prayer Boards</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach ($boards as $board): ?>
                        <a href="board.php?id=<?php echo $board['id']; ?>" 
                           class="block p-4 border rounded hover:bg-gray-50 transition-colors">
                            <h3 class="font-semibold"><?php echo htmlspecialchars($board['name']); ?></h3>
                            <p class="text-sm text-gray-600">
                                Created: <?php echo date('M j, Y', strtotime($board['created_at'])); ?>
                            </p>
                            <?php if ($board['description']): ?>
                                <p class="text-sm text-gray-500 mt-2">
                                    <?php echo htmlspecialchars(substr($board['description'], 0, 100)) . '...'; ?>
                                </p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show upload button when file is selected
        document.getElementById('profile_picture').addEventListener('change', function() {
            document.getElementById('upload-btn').classList.remove('hidden');
        });
    </script>
</body>
</html>
<?php require_once 'includes/footer.php'; ?>