<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$envelope_id = $_GET['envelope_id'] ?? null;

if (!$envelope_id) {
    header('HTTP/1.1 400 Bad Request');
    exit('Envelope ID is required');
}

// Verify user has access to this envelope
$check_stmt = $pdo->prepare("
    SELECT 1 FROM envelopes e 
    JOIN prayer_boards pb ON e.board_id = pb.id 
    WHERE e.id = ? AND pb.user_id = ?
");
$check_stmt->execute([$envelope_id, $_SESSION['user_id']]);
if (!$check_stmt->fetch()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

// Fetch prayers for the envelope
$stmt = $pdo->prepare("
    SELECT * FROM prayer_points 
    WHERE envelope_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$envelope_id]);
$prayers = $stmt->fetchAll();

// Return the prayers as HTML
foreach ($prayers as $prayer): ?>
    <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
        <span class="<?php echo $prayer['answered_at'] ? 'line-through text-gray-500' : ''; ?>">
            <?php echo htmlspecialchars($prayer['content']); ?>
        </span>
        <div class="flex space-x-2">
            <form action="board.php" method="POST" class="inline">
                <input type="hidden" name="action" value="toggle_prayer_status">
                <input type="hidden" name="prayer_id" value="<?php echo $prayer['id']; ?>">
                <input type="hidden" name="board_id" value="<?php echo htmlspecialchars($_GET['board_id']); ?>">
                <button type="submit" 
                        class="<?php echo $prayer['answered_at'] ? 'text-green-500' : 'text-gray-500'; ?> hover:text-green-700">
                    <?php echo $prayer['answered_at'] ? 'Unanswer' : 'Mark as Answered'; ?>
                </button>
            </form>
            <button onclick="showEditPrayerModal(<?php echo $prayer['id']; ?>, '<?php echo htmlspecialchars($prayer['content'], ENT_QUOTES); ?>')" 
                    class="text-blue-500 hover:text-blue-700">
                Edit
            </button>
            <form action="board.php" method="POST" class="inline" 
                  onsubmit="return confirm('Are you sure you want to delete this prayer?');">
                <input type="hidden" name="action" value="delete_prayer">
                <input type="hidden" name="prayer_id" value="<?php echo $prayer['id']; ?>">
                <input type="hidden" name="board_id" value="<?php echo htmlspecialchars($_GET['board_id']); ?>">
                <button type="submit" class="text-red-500 hover:text-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>
<?php endforeach; ?>