<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Registration successful! Please login.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Both fields are required';
    } else {
        if (loginUser($email, $password)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Prayer Board</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" class="w-full p-2 border rounded">
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" class="w-full p-2 border rounded">
                </div>
                
                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                    Login
                </button>
            </form>
            
            <p class="mt-4 text-center">
                Don't have an account? <a href="register.php" class="text-blue-500">Register</a>
            </p>
        </div>
    </div>
</body>
</html>