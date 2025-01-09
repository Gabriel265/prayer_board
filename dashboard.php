<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch user's prayer boards
function getUserBoards($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            b.*, 
            COUNT(DISTINCT e.id) as envelope_count,
            COUNT(DISTINCT p.id) as prayer_count
        FROM prayer_boards b
        LEFT JOIN envelopes e ON b.id = e.board_id
        LEFT JOIN prayer_points p ON e.id = p.envelope_id
        WHERE b.user_id = ?
        GROUP BY b.id
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

$boards = getUserBoards($_SESSION['user_id']);

// Handle new board creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_board'])) {
    $name = trim($_POST['board_name']);
    $description = trim($_POST['description']);
    $background_color = $_POST['background_color'] ?? '#ffffff';
    
    if (!empty($name)) {
        $stmt = $pdo->prepare("
            INSERT INTO prayer_boards (user_id, name, description, background_color) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $name, $description, $background_color]);
        header('Location: dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Prayer Board</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <h1 class="text-xl font-bold">Prayer Board</h1>
                <div class="flex items-center space-x-4">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="text-red-500">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">My Prayer Boards</h2>
            <button onclick="showCreateBoardModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Create New Board
            </button>
        </div>

        <!-- Prayer Boards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($boards as $board): ?>
                <div class="bg-white rounded-lg shadow-md p-6" style="background-color: <?php echo htmlspecialchars($board['background_color']); ?>">
                    <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($board['name']); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($board['description']); ?></p>
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <p><?php echo $board['envelope_count']; ?> envelopes</p>
                            <p><?php echo $board['prayer_count']; ?> prayers</p>
                        </div>
                        <a href="board.php?id=<?php echo $board['id']; ?>" 
                           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Open Board
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Create Board Modal -->
    <div id="createBoardModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 w-96">
            <h2 class="text-xl font-bold mb-4">Create New Prayer Board</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Board Name</label>
                    <input type="text" name="board_name" class="w-full p-2 border rounded" required>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Description</label>
                    <textarea name="description" class="w-full p-2 border rounded" rows="3"></textarea>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Background Color</label>
                    <input type="color" name="background_color" value="#ffffff" class="w-full p-2 border rounded">
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="hideCreateBoardModal()" 
                            class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" name="create_board" 
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showCreateBoardModal() {
            document.getElementById('createBoardModal').classList.remove('hidden');
        }
        
        function hideCreateBoardModal() {
            document.getElementById('createBoardModal').classList.add('hidden');
        }
    </script>
</body>
</html>