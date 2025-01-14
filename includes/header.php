<?php
require_once 'auth.php';
$relativePath = getRelativePath();



// First, get the depth of the current page relative to the root
function getRelativePath() {
    $currentPath = $_SERVER['PHP_SELF'];
    $depth = substr_count(dirname($currentPath), '/') - 1;
    return str_repeat('../', $depth);
}

// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Prayer Board'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo $relativePath; ?>assets/css/styles.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="<?php echo $relativePath; ?>index.php" class="text-xl font-bold text-blue-600">Prayer Board</a>
                <div class="flex items-center space-x-4">
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?php echo $relativePath; ?>dashboard.php" class="text-gray-600 hover:text-gray-800">Dashboard</a>
        <div class="relative group">
            <button class="text-gray-600 hover:text-gray-800">
                <?php echo htmlspecialchars($_SESSION['username']); ?>
            </button>
            <!-- Added padding to create a hoverable area between button and menu -->
            <div class="absolute right-0 w-48 pt-2 invisible group-hover:visible">
                <!-- Added overflow-hidden to contain the shadow within rounded corners -->
                <div class="bg-white rounded-md shadow-lg overflow-hidden">
                    <a href="<?php echo $relativePath; ?>profile.php" 
                       class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Profile</a>
                    <a href="/settings.php" 
                       class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Settings</a>
                    <a href="<?php echo $relativePath; ?>logout.php" 
                       class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <a href="<?php echo $relativePath; ?>login.php" class="text-gray-600 hover:text-gray-800">Login</a>
        <a href="<?php echo $relativePath; ?>register.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Register</a>
    <?php endif; ?>
</div>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-8">