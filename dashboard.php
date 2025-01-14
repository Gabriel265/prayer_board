<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
    

        case 'edit_board':
            $board_id = $_POST['board_id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $background_color = $_POST['background_color'] ?? '#ffffff';
        
            if (empty($name) || !$board_id) {
                $_SESSION['error'] = 'Board name is required';
                header("Location: dashboard.php");
                exit;
            }
        
            try {
                $stmt = $pdo->prepare("
                    UPDATE prayer_boards 
                    SET name = ?, description = ?, background_color = ? 
                    WHERE id = ? AND user_id = ?
                ");
                
                if ($stmt->execute([$name, $description, $background_color, $board_id, $_SESSION['user_id']])) {
                    $_SESSION['success'] = 'Board updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update board';
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Database error occurred';
                error_log('Database error: ' . $e->getMessage());
            }
            
            header("Location: dashboard.php");
            exit;

            case 'delete_board':
                $board_id = $_POST['board_id'] ?? null;
                
                if (!$board_id) {
                    $_SESSION['error'] = 'Invalid board ID';
                    header("Location: dashboard.php");
                    exit;
                }
                
                try {
                    $pdo->beginTransaction();
                    
                    // First delete related prayer_points
                    $stmt = $pdo->prepare("
                        DELETE prayer_points 
                        FROM prayer_points 
                        INNER JOIN envelopes ON prayer_points.envelope_id = envelopes.id 
                        WHERE envelopes.board_id = ?
                    ");
                    $stmt->execute([$board_id]);
                    
                    // Then delete envelopes
                    $stmt = $pdo->prepare("
                        DELETE FROM envelopes 
                        WHERE board_id = ?
                    ");
                    $stmt->execute([$board_id]);
                    
                    // Finally delete the board itself
                    $stmt = $pdo->prepare("
                        DELETE FROM prayer_boards 
                        WHERE id = ? AND user_id = ?
                    ");
                    $stmt->execute([$board_id, $_SESSION['user_id']]);
                    
                    $pdo->commit();
                    $_SESSION['success'] = 'Board and all related items deleted successfully';
                    
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
                    error_log('Delete Board error: ' . $e->getMessage());
                }
                
                header("Location: dashboard.php");
                exit;
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
    <!-- <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <h1 class="text-xl font-bold"></h1>
                <div class="flex items-center space-x-4">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="" class="text-red-500">Forums</a>
                    <a href="" class="text-red-500">Private Chats</a>
                </div>
            </div>
        </div>
    </nav> -->

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
        <div class="bg-white rounded-lg shadow-md p-6 relative" style="background-color: <?php echo htmlspecialchars($board['background_color']); ?>">
            <!-- Edit/Delete buttons moved to top-right -->
            <div class="absolute top-4 right-4 flex items-center space-x-2">
            <button onclick="showEditBoardModal(
                    <?php echo $board['id']; ?>, 
                    '<?php echo htmlspecialchars(addslashes($board['name'])); ?>', 
                    '<?php echo htmlspecialchars(addslashes($board['description'])); ?>',
                    '<?php echo htmlspecialchars($board['background_color']); ?>')"
                    class="text-gray-500 hover:text-gray-700">
                    Edit
                </button>
                <form action="dashboard.php" method="POST" class="inline" 
                            onsubmit="return confirm('Are you sure you want to delete this Prayer Board?');">
                            <input type="hidden" name="action" value="delete_board">
                            <input type="hidden" name="board_id" value="<?php echo $board['id']; ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                Delete
                            </button>
                        </form>
            </div>

            <!-- Board content -->
            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($board['name']); ?></h3>
            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($board['description']); ?></p>
            
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    <p><?php echo $board['envelope_count']; ?> category(s)</p>
                    <p><?php echo $board['prayer_count']; ?> prayer(s)</p>
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

    <!-- Edit Board Modal -->
    <div id="editBoardModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 w-96">
        <h2 class="text-xl font-bold mb-4">Edit Prayer Board</h2>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="edit_board">
            <input type="hidden" name="board_id" id="editBoardId">
            
            <div>
                <label class="block text-gray-700 mb-2">Board Name</label>
                <input type="text" name="name" id="editBoardName" 
                       class="w-full p-2 border rounded" required>
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">Description</label>
                <textarea name="description" id="editBoardDescription" 
                         class="w-full p-2 border rounded" rows="3"></textarea>
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">Background Color</label>
                <input type="color" name="background_color" id="editBoardColor" 
                       class="w-full h-10 p-1 border rounded">
            </div>
            
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="hideEditBoardModal()" 
                        class="px-4 py-2 border rounded">Cancel</button>
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Save Changes
                </button>
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

        //edit board modal functions
        function showEditBoardModal(boardId, boardName, boardDescription, boardColor) {
    document.getElementById('editBoardId').value = boardId;
    document.getElementById('editBoardName').value = boardName;
    document.getElementById('editBoardDescription').value = boardDescription;
    document.getElementById('editBoardColor').value = boardColor;
    document.getElementById('editBoardModal').classList.remove('hidden');
}

function hideEditBoardModal() {
    document.getElementById('editBoardModal').classList.add('hidden');
}
    </script>
</body>
</html>
<?php require_once 'includes/footer.php'; ?>