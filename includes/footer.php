
</main>
    <footer class="bg-white shadow-lg mt-auto">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Prayer Board</h3>
                    <p class="text-gray-600">A place to share and connect through prayer.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo $relativePath; ?>index.php" class="text-gray-600 hover:text-blue-600">Home</a></li>
                        <li><a href="<?php echo $relativePath; ?>includes/about.php"  class="text-gray-600 hover:text-blue-600">About</a></li>
                    <li><a href="<?php echo $relativePath; ?>includes/contact.php" class="text-gray-600 hover:text-blue-600">Contact</a></li> <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="<?php echo $relativePath; ?>dashboard.php" class="text-gray-600 hover:text-blue-600">Dashboard</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Connect</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo $relativePath; ?>includes/privacy.php" class="text-gray-600 hover:text-blue-600">Privacy Policy</a></li>
                        <li><a href="<?php echo $relativePath; ?>includes/terms.php" class="text-gray-600 hover:text-blue-600">Terms of Service</a></li>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <li><a href="/register.php" class="text-gray-600 hover:text-blue-600">Join Our Community</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-200 mt-8 pt-8 text-center">
                <p class="text-gray-600">&copy; <?php echo date('Y'); ?> Prayer Board by Gabriel Kadiwa. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>