<?php
$pageTitle = 'About Us';
$relativePath = getRelativePath();
require_once 'header.php';



// First, get the depth of the current page relative to the root
function getRelativePath() {
    $currentPath = $_SERVER['PHP_SELF'];
    $depth = substr_count(dirname($currentPath), '/') - 1;
    return str_repeat('../', $depth);
}
?>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">About Prayer Board</h1>
    
    <div class="prose lg:prose-lg text-gray-600">
        <p class="mb-6">
            Prayer Board is a community-driven platform dedicated to connecting people through the power of prayer. 
            We believe in the strength of collective faith and the comfort that comes from knowing others are 
            praying with and for you.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-8 mb-4">Our Mission</h2>
        <p class="mb-6">
            Our mission is to create a safe, supportive space where people from all walks of life can share their 
            prayer requests, offer spiritual support, and connect with others in their faith journey. We strive to 
            foster a community of compassion, understanding, and mutual support.
        </p>

        <h2 class="text-2xl font-semibold text-gray-800 mt-8 mb-4">Our Values</h2>
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="p-4 border rounded-lg">
                <h3 class="font-semibold text-gray-800 mb-2">Community</h3>
                <p>Building meaningful connections through shared faith and support.</p>
            </div>
            <div class="p-4 border rounded-lg">
                <h3 class="font-semibold text-gray-800 mb-2">Respect</h3>
                <p>Embracing diversity and treating all members with dignity and kindness.</p>
            </div>
            <div class="p-4 border rounded-lg">
                <h3 class="font-semibold text-gray-800 mb-2">Privacy</h3>
                <p>Ensuring a secure and confidential space for sharing personal prayers.</p>
            </div>
            <div class="p-4 border rounded-lg">
                <h3 class="font-semibold text-gray-800 mb-2">Support</h3>
                <p>Providing spiritual encouragement and practical assistance when needed.</p>
            </div>
        </div>

        <h2 class="text-2xl font-semibold text-gray-800 mt-8 mb-4">Join Our Community</h2>
        <p class="mb-6">
            Whether you're seeking prayer support or wanting to pray for others, Prayer Board welcomes you. 
            Join our growing community and experience the power of collective prayer.
        </p>

        <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="mt-8 text-center">
            <a href="/register.php" 
                class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-200">
                Create an Account
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>