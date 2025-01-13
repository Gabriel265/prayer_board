<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();



// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_envelope':
            $board_id = $_POST['board_id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $color = $_POST['color'] ?? '#ffffff';
            
            if (empty($name)) {
                $_SESSION['error'] = 'Envelope name is required';
                header("Location: board.php?id=" . urlencode($board_id));
                exit;
            }
            
            // Verify board ownership
            $check_stmt = $pdo->prepare("SELECT id FROM prayer_boards WHERE id = ? AND user_id = ?");
            $check_stmt->execute([$board_id, $_SESSION['user_id']]);
            if (!$check_stmt->fetch()) {
                $_SESSION['error'] = 'Unauthorized access';
                header("Location: dashboard.php");
                exit;
            }
            
            try {
                // Get the next order index
                $order_stmt = $pdo->prepare("SELECT COALESCE(MAX(order_index), -1) + 1 FROM envelopes WHERE board_id = ?");
                $order_stmt->execute([$board_id]);
                $order_index = $order_stmt->fetchColumn();
                
                // Insert the new envelope
                $stmt = $pdo->prepare("
                    INSERT INTO envelopes (board_id, name, color, order_index) 
                    VALUES (?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$board_id, $name, $color, $order_index])) {
                    $_SESSION['success'] = 'Envelope created successfully';
                } else {
                    $_SESSION['error'] = 'Failed to create envelope';
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Database error occurred';
                error_log('Database error: ' . $e->getMessage());
            }
            
            header("Location: board.php?id=" . urlencode($board_id));
            exit;

        case 'edit_envelope':
            $envelope_id = $_POST['envelope_id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $color = $_POST['color'] ?? '#ffffff';
            $board_id = $_POST['board_id'] ?? null;

            if (empty($name) || !$envelope_id) {
                $_SESSION['error'] = 'Envelope name is required';
                header("Location: board.php?id=" . urlencode($board_id));
                exit;
            }

            try {
                $stmt = $pdo->prepare("
                    UPDATE envelopes 
                    SET name = ?, color = ? 
                    WHERE id = ? AND board_id IN (
                        SELECT id FROM prayer_boards WHERE user_id = ?
                    )
                ");
                
                if ($stmt->execute([$name, $color, $envelope_id, $_SESSION['user_id']])) {
                    $_SESSION['success'] = 'Envelope updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update envelope';
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Database error occurred';
                error_log('Database error: ' . $e->getMessage());
            }
            
            header("Location: board.php?id=" . urlencode($board_id));
            exit;

            case 'delete_envelope':
                $envelope_id = $_POST['envelope_id'] ?? null;
                $board_id = $_POST['board_id'] ?? null;
                
                if (!$envelope_id || !$board_id) {
                    $_SESSION['error'] = 'Invalid envelope or board ID';
                    header("Location: board.php?id=" . urlencode($board_id));
                    exit;
                }
                
                try {
                    // Verify ownership and delete envelope
                    $stmt = $pdo->prepare("
                        DELETE e FROM envelopes e
                        INNER JOIN prayer_boards b ON e.board_id = b.id
                        WHERE e.id = :envelope_id 
                        AND e.board_id = :board_id 
                        AND b.user_id = :user_id
                    ");
                    
                    $params = [
                        ':envelope_id' => $envelope_id,
                        ':board_id' => $board_id,
                        ':user_id' => $_SESSION['user_id']
                    ];
                    
                    if ($stmt->execute($params)) {
                        if ($stmt->rowCount() > 0) {
                            $_SESSION['success'] = 'Envelope deleted successfully';
                        } else {
                            $_SESSION['error'] = 'Envelope not found or you don\'t have permission to delete it';
                        }
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        $_SESSION['error'] = 'Failed to delete envelope: ' . $errorInfo[2];
                        error_log('SQL Error: ' . print_r($errorInfo, true));
                    }
                } catch (PDOException $e) {
                    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
                    error_log('Delete envelope error: ' . $e->getMessage());
                }
                
                header("Location: board.php?id=" . urlencode($board_id));
                exit;

        case 'add_prayer':
            $envelope_id = $_POST['envelope_id'] ?? null;
            $content = trim($_POST['content'] ?? '');
            $board_id = $_POST['board_id'] ?? null;
            
            if (empty($content) || !$envelope_id) {
                $_SESSION['error'] = 'Prayer content is required';
                header("Location: board.php?id=" . urlencode($board_id));
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO prayer_points (envelope_id, content) 
                    SELECT ?, ? 
                    FROM envelopes e 
                    JOIN prayer_boards pb ON e.board_id = pb.id 
                    WHERE e.id = ? AND pb.user_id = ?
                ");
                
                if ($stmt->execute([$envelope_id, $content, $envelope_id, $_SESSION['user_id']])) {
                    $_SESSION['success'] = 'Prayer added successfully';
                } else {
                    $_SESSION['error'] = 'Failed to add prayer';
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Database error occurred';
                error_log('Database error: ' . $e->getMessage());
            }
            
            header("Location: board.php?id=" . urlencode($board_id));
            exit;

        case 'edit_prayer':
            $prayer_id = $_POST['prayer_id'] ?? null;
            $content = trim($_POST['content'] ?? '');
            $board_id = $_POST['board_id'] ?? null;
            
            if (empty($content) || !$prayer_id) {
                $_SESSION['error'] = 'Prayer content is required';
                header("Location: board.php?id=" . urlencode($board_id));
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE prayer_points pp
                    SET content = ?
                    WHERE pp.id = ? AND EXISTS (
                        SELECT 1 FROM envelopes e 
                        JOIN prayer_boards pb ON e.board_id = pb.id 
                        WHERE e.id = pp.envelope_id AND pb.user_id = ?
                    )
                ");
                
                if ($stmt->execute([$content, $prayer_id, $_SESSION['user_id']])) {
                    $_SESSION['success'] = 'Prayer updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update prayer';
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Database error occurred';
                error_log('Database error: ' . $e->getMessage());
            }
            
            header("Location: board.php?id=" . urlencode($board_id));
            exit;

        case 'toggle_prayer_status':
            $prayer_id = $_POST['prayer_id'] ?? null;
            $board_id = $_POST['board_id'] ?? null;
            $current_status = $_POST['current_status'] ?? null;
            
            if (!$prayer_id) {
                $_SESSION['error'] = 'Invalid prayer';
                header("Location: board.php?id=" . urlencode($board_id));
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE prayer_points pp
                    SET answered_at = CASE 
                        WHEN answered_at IS NULL THEN CURRENT_TIMESTAMP 
                        ELSE NULL 
                    END
                    WHERE pp.id = ? AND EXISTS (
                        SELECT 1 FROM envelopes e 
                        JOIN prayer_boards pb ON e.board_id = pb.id 
                        WHERE e.id = pp.envelope_id AND pb.user_id = ?
                    )
                ");
                
                if ($stmt->execute([$prayer_id, $_SESSION['user_id']])) {
                    $_SESSION['success'] = 'Prayer status updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update prayer status';
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Database error occurred';
                error_log('Database error: ' . $e->getMessage());
            }
            
            header("Location: board.php?id=" . urlencode($board_id));
            exit;

        case 'delete_prayer':
            $prayer_id = $_POST['prayer_id'] ?? null;
            $board_id = $_POST['board_id'] ?? null;
            
            if (!$prayer_id) {
                $_SESSION['error'] = 'Invalid prayer';
                header("Location: board.php?id=" . urlencode($board_id));
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("
                    DELETE pp FROM prayer_points pp
                    WHERE pp.id = ? AND EXISTS (
                        SELECT 1 FROM envelopes e 
                        JOIN prayer_boards pb ON e.board_id = pb.id 
                        WHERE e.id = pp.envelope_id AND pb.user_id = ?
                    )
                ");
                
                if ($stmt->execute([$prayer_id, $_SESSION['user_id']])) {
                    $_SESSION['success'] = 'Prayer deleted successfully';
                } else {
                    $_SESSION['error'] = 'Failed to delete prayer';
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Database error occurred';
                error_log('Database error: ' . $e->getMessage());
            }
            
            header("Location: board.php?id=" . urlencode($board_id));
            exit;
    }
}



$board_id = $_GET['id'] ?? null;
if (!$board_id) {
    header('Location: dashboard.php');
    exit();
}

// Fetch board details and verify ownership
$stmt = $pdo->prepare("SELECT * FROM prayer_boards WHERE id = ? AND user_id = ?");
$stmt->execute([$board_id, $_SESSION['user_id']]);
$board = $stmt->fetch();

// Fetch envelopes for this board
function getBoardEnvelopes($boardId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            e.*,
            COUNT(p.id) as prayer_count,
            COUNT(CASE WHEN p.answered_at IS NOT NULL THEN 1 END) as answered_count
        FROM envelopes e
        LEFT JOIN prayer_points p ON e.id = p.envelope_id
        WHERE e.board_id = ?
        GROUP BY e.id
        ORDER BY e.order_index
    ");
    $stmt->execute([$boardId]);
    return $stmt->fetchAll();
}

$envelope_id = $_GET['id'] ?? null;

// Fetch prayers for an envelope
function getEnvelopePrayers($envelope_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM prayer_points 
        WHERE envelope_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$envelope_id]);
    return $stmt->fetchAll();
}

$prayers = getEnvelopePrayers(envelope_id: $envelope_id);

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');

    // Check session for AJAX requests
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Session expired. Please refresh the page.'
        ]);
        exit;
    }
    
    switch ($_POST['action']) {


        case 'add_prayer':
            $envelope_id = $_POST['envelope_id'];
            $content = trim($_POST['content']);
            
            $stmt = $pdo->prepare("INSERT INTO prayer_points (envelope_id, content) VALUES (?, ?)");
            $success = $stmt->execute([$envelope_id, $content]);
            
            echo json_encode(['success' => $success]);
            exit;
            
        case 'mark_answered':
            $prayer_id = $_POST['prayer_id'];
            
            $stmt = $pdo->prepare("UPDATE prayer_points SET answered_at = CURRENT_TIMESTAMP WHERE id = ?");
            $success = $stmt->execute([$prayer_id]);
            
            echo json_encode(['success' => $success]);
            exit;
            
        case 'update_order':
            $envelope_id = $_POST['envelope_id'];
            $new_index = $_POST['new_index'];
            
            $stmt = $pdo->prepare("UPDATE envelopes SET order_index = ? WHERE id = ?");
            $success = $stmt->execute([$new_index, $envelope_id]);
            
            echo json_encode(['success' => $success]);
            exit;
    }
}

$envelopes = getBoardEnvelopes($board_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($board['name']); ?> - Prayer Board</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <script src="./assets/js/main.js"></script> 
</head>
<body class="bg-gray-100" style="background-color: <?php echo htmlspecialchars($board['background_color']); ?>">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-gray-500">← Back to Dashboard</a>
                    <h1 class="text-xl font-bold"><?php echo htmlspecialchars($board['name']); ?></h1>
                </div>
                <div class="flex space-x-4">
                    <button onclick="showBoardSettings()" class="text-gray-500 hover:text-gray-700">
                        Settings
                    </button>
                    <button onclick="showEnvelopeModal()" 
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Envelope
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4">
            <?php 
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4">
            <?php 
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>
</div>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div id="envelopes" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($envelopes as $envelope): ?>
                <div class="envelope bg-white rounded-lg shadow-md p-6" 
                     data-id="<?php echo $envelope['id']; ?>"
                     style="background-color: <?php echo htmlspecialchars($envelope['color']); ?>">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($envelope['name']); ?></h3>
                        <div class="flex items-center space-x-2">
                            <button onclick="showEditEnvelopeModal(
                                <?php echo $envelope['id']; ?>, 
                                '<?php echo htmlspecialchars(addslashes($envelope['name'])); ?>', 
                                '<?php echo htmlspecialchars($envelope['color']); ?>')"
                                class="text-gray-500 hover:text-gray-700">
                                Edit
                            </button>
                            <form action="board.php" method="POST" class="inline" 
                                onsubmit="return confirm('Are you sure you want to delete this Category?');">
                                <input type="hidden" name="action" value="delete_envelope">
                                <input type="hidden" name="envelope_id" value="<?php echo $envelope['id']; ?>">
                                <input type="hidden" name="board_id" value="<?php echo $envelope['board_id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        <p class="text-gray-600">
                            Total Prayer(s): <?php echo $envelope['prayer_count']; ?>
                        </p>
                        <p class="text-green-600">
                            Answered: <?php echo $envelope['answered_count']; ?>
                        </p>
                    </div>

                    <button onclick="viewPrayers(<?php echo $envelope['id']; ?>, '<?php echo htmlspecialchars($envelope['name'], ENT_QUOTES); ?>')"
                            class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        View Prayers
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Prayer Modal -->
    <div id="prayerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold" id="modalEnvelopeName"></h2>
            <button onclick="hidePrayerModal()" class="text-gray-500 hover:text-gray-700">×</button>
        </div>

        <form action="board.php" method="POST" class="mb-6">
            <input type="hidden" name="action" value="add_prayer">
            <input type="hidden" name="envelope_id" id="currentEnvelopeId">
            <input type="hidden" name="board_id" value="<?php echo htmlspecialchars($board_id); ?>">
            <div class="flex space-x-2">
                <input type="text" name="content" class="flex-1 p-2 border rounded" 
                       placeholder="Enter your prayer request..." required>
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Add
                </button>
            </div>
        </form>

        <div id="prayerList" class="space-y-4">
            <!-- Prayers will be loaded here dynamically -->
        </div>
    </div>
</div>

<!-- Edit Prayer Modal -->
<div id="editPrayerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 w-96">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Edit Prayer</h2>
            <button onclick="hideEditPrayerModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form action="board.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="edit_prayer">
            <input type="hidden" name="prayer_id" id="editPrayerId">
            <input type="hidden" name="board_id" value="<?php echo htmlspecialchars($board_id); ?>">
            
            <div>
                <label class="block text-gray-700 mb-2">Prayer Content</label>
                <textarea name="content" id="editPrayerContent" 
                          class="w-full p-2 border rounded" required></textarea>
            </div>
            
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="hideEditPrayerModal()" 
                        class="px-4 py-2 border rounded hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>


<!-- Edit Envelope Modal -->
<div id="editEnvelopeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 w-96">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Edit Envelope</h2>
            <button onclick="hideEditEnvelopeModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form action="board.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="edit_envelope">
            <input type="hidden" name="envelope_id" id="editEnvelopeId">
            <input type="hidden" name="board_id" value="<?php echo htmlspecialchars($board_id); ?>">
            
            <div>
                <label class="block text-gray-700 mb-2">Envelope Name</label>
                <input type="text" name="name" id="editEnvelopeName" 
                       class="w-full p-2 border rounded" required>
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">Color</label>
                <input type="color" name="color" id="editEnvelopeColor" 
                       class="w-full h-10 p-1 border rounded">
            </div>
            
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="hideEditEnvelopeModal()" 
                        class="px-4 py-2 border rounded hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- Create Envelope Modal -->
<div id="envelopeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 w-96">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold" id="envelopeModalTitle">Create New Envelope</h2>
            <button onclick="hideEnvelopeModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form action="board.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create_envelope">
            <input type="hidden" name="board_id" value="<?php echo htmlspecialchars($board_id); ?>">
            
            <div>
                <label class="block text-gray-700 mb-2">Envelope Name</label>
                <input type="text" name="name" class="w-full p-2 border rounded" required 
                       placeholder="Enter envelope name">
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">Color</label>
                <input type="color" name="color" class="w-full h-10 p-1 border rounded" 
                       value="#ffffff">
            </div>
            
            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" onclick="hideEnvelopeModal()" 
                        class="px-4 py-2 border rounded hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Create Envelope
                </button>
            </div>
        </form>
    </div>
</div>

<script>

    

    //view prayers function

    function viewPrayers(envelopeId, envelopeName) {
    // Set envelope ID and name in the modal
    document.getElementById('currentEnvelopeId').value = envelopeId;
    document.getElementById('modalEnvelopeName').textContent = envelopeName;
    
    // Get board ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const boardId = urlParams.get('id');
    
    // Load prayers for this envelope
    const prayerList = document.getElementById('prayerList');
    prayerList.innerHTML = 'Loading prayers...';
    
    // Fetch prayers
    fetch(`get_prayers.php?envelope_id=${envelopeId}&board_id=${boardId}`)
        .then(response => response.text())
        .then(html => {
            prayerList.innerHTML = html;
        })
        .catch(error => {
            prayerList.innerHTML = 'Error loading prayers. Please try again.';
            console.error('Error:', error);
        });
    
    // Show the modal
    document.getElementById('prayerModal').classList.remove('hidden');
}
// Modal functions

// Envelope management functions
function showEnvelopeModal() {
    document.getElementById('envelopeModal').classList.remove('hidden');
    // Focus on the name input when modal opens
    setTimeout(() => {
        document.querySelector('#envelopeModal input[name="name"]').focus();
    }, 100);
}

function hideEnvelopeModal() {
    document.getElementById('envelopeModal').classList.add('hidden');
    // Reset form when modal closes
    document.querySelector('#envelopeModal form').reset();
}

//edit envelope modal functions
function showEditEnvelopeModal(envelopeId, envelopeName, envelopeColor) {
    // Set the values in the modal
    document.getElementById('editEnvelopeId').value = envelopeId;
    document.getElementById('editEnvelopeName').value = envelopeName;
    document.getElementById('editEnvelopeColor').value = envelopeColor;

    // Show the modal
    document.getElementById('editEnvelopeModal').classList.remove('hidden');
}

function hideEditEnvelopeModal() {
    document.getElementById('editEnvelopeModal').classList.add('hidden');
}

function showPrayerModal(envelopeId, envelopeName) {
    document.getElementById('currentEnvelopeId').value = envelopeId;
    document.getElementById('modalEnvelopeName').textContent = envelopeName;
    document.getElementById('prayerModal').classList.remove('hidden');
}

function hidePrayerModal() {
    document.getElementById('prayerModal').classList.add('hidden');
}

function showEditPrayerModal(prayerId, content) {
    document.getElementById('editPrayerId').value = prayerId;
    document.getElementById('editPrayerContent').value = content;
    document.getElementById('editPrayerModal').classList.remove('hidden');
}

function hideEditPrayerModal() {
    document.getElementById('editPrayerModal').classList.add('hidden');
}

function showEditEnvelopeModal(envelopeId, name, color) {
    document.getElementById('editEnvelopeId').value = envelopeId;
    document.getElementById('editEnvelopeName').value = name;
    document.getElementById('editEnvelopeColor').value = color;
    document.getElementById('editEnvelopeModal').classList.remove('hidden');
}

function hideEditEnvelopeModal() {
    document.getElementById('editEnvelopeModal').classList.add('hidden');
}

// Update the envelope display HTML to include edit button
function updateEnvelopeDisplay(envelope) {
    return `
        <div class="envelope bg-white rounded-lg shadow-md p-6" 
             data-id="${envelope.id}"
             style="background-color: ${envelope.color}">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">${envelope.name}</h3>
                <div class="flex items-center space-x-2">
                    <button onclick="showEditEnvelopeModal(${envelope.id}, '${envelope.name}', '${envelope.color}')"
                            class="text-gray-500 hover:text-gray-700">
                        Edit
                    </button>
                </div>
            </div>
            
            <div class="space-y-2 mb-4">
                <p class="text-gray-600">
                    Total Prayers: ${envelope.prayer_count}
                </p>
                <p class="text-green-600">
                    Answered: ${envelope.answered_count}
                </p>
            </div>

            <button onclick="showPrayerModal(${envelope.id}, '${envelope.name}')"
                    class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                View Prayers
            </button>
        </div>
    `;
}


// Initialize sortable functionality for envelopes
document.addEventListener('DOMContentLoaded', function() {
    const envelopes = document.getElementById('envelopes');
    if (envelopes) {
        new Sortable(envelopes, {
            animation: 150,
            onEnd: function(evt) {
                const envelopeId = evt.item.getAttribute('data-id');
                const newIndex = evt.newIndex;
                
                // Update order via form submission
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'board.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'update_order';
                
                const envelopeInput = document.createElement('input');
                envelopeInput.type = 'hidden';
                envelopeInput.name = 'envelope_id';
                envelopeInput.value = envelopeId;
                
                const orderInput = document.createElement('input');
                orderInput.type = 'hidden';
                orderInput.name = 'new_index';
                orderInput.value = newIndex;
                
                form.appendChild(actionInput);
                form.appendChild(envelopeInput);
                form.appendChild(orderInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});
</script>
    
</body>
</html>