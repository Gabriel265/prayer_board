<?php
$pageTitle = 'Privacy Policy';
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
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Privacy Policy</h1>
    
    <div class="prose lg:prose-lg text-gray-600">
        <p class="mb-6">Last updated: <?php echo date('F d, Y'); ?></p>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Introduction</h2>
            <p>
                Prayer Board ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy 
                explains how we collect, use, disclose, and safeguard your information when you use our website 
                and services.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Information We Collect</h2>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Personal Information</h3>
            <ul class="list-disc ml-6 mb-4">
                <li>Name and email address when you create an account</li>
                <li>Profile information you choose to share</li>
                <li>Prayer requests and responses you post</li>
                <li>Communications with other users</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mb-2">Usage Information</h3>
            <ul class="list-disc ml-6 mb-4">
                <li>Log data and device information</li>
                <li>Cookie data and similar technologies</li>
                <li>Usage patterns and preferences</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">How We Use Your Information</h2>
            <ul class="list-disc ml-6 mb-4">
                <li>To provide and maintain our services</li>
                <li>To notify you about changes to our services</li>
                <li>To allow you to participate in interactive features</li>
                <li>To provide customer support</li>
                <li>To gather analysis or valuable information to improve our services</li>
                <li>To monitor the usage of our services</li>
                <li>To detect, prevent and address technical issues</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Data Security</h2>
            <p>
                We implement appropriate technical and organizational security measures to protect your personal 
                information. However, no method of transmission over the Internet or electronic storage is 100% 
                secure, and we cannot guarantee absolute security.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Your Rights</h2>
            <p>You have the right to:</p>
            <ul class="list-disc ml-6 mb-4">
                <li>Access your personal information</li>
                <li>Correct inaccurate or incomplete information</li>
                <li>Request deletion of your information</li>
                <li>Object to our processing of your information</li>
                <li>Withdraw consent at any time</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Contact Us</h2>
            <p>
                If you have any questions about this Privacy Policy, please contact us at:
                <br>
                Email: gabrielkadiwa@gmail.com
                <br>
                Phone: (+265) 995 375 405
            </p>
        </section>
    </div>
</div>

<?php require_once 'footer.php'; ?>