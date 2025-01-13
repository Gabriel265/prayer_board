<?php
$pageTitle = 'Welcome to Prayer Board';
require_once 'includes/header.php';
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
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-gray-600 mb-4">"This app has transformed my prayer life. Being able to see all my answered prayers is such an encouragement."</p>
                <p class="font-semibold">- Sarah M.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-gray-600 mb-4">"I love how I can organize my prayers into different categories. It helps me stay focused and consistent."</p>
                <p class="font-semibold">- John D.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-gray-600 mb-4">"The simple and clean interface makes it easy to use. I appreciate being able to track my prayer journey."</p>
                <p class="font-semibold">- Rachel K.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>