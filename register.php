<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        if (registerUser($username, $email, $password)) {
            header('Location: login.php?registered=1');
            exit();
        } else {
            $error = 'Username or email already exists';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Prayer Board</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6 text-center">Create Account</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" class="w-full p-2 border rounded" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" class="w-full p-2 border rounded"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" class="w-full p-2 border rounded">
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" class="w-full p-2 border rounded">
                </div>
                
                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                    Register
                </button>
            </form>
            
            <p class="mt-4 text-center">
                Already have an account? <a href="login.php" class="text-blue-500">Login</a>
            </p>
        </div>
    </div>
</body>
</html>