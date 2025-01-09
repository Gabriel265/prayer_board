<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$board_id = $_GET['id'] ?? null;
if (!$board_id) {
    header('Location: dashboard.php');
    exit();
}

// Fetch board details and verify ownership
$stmt = $pdo->prepare("SELECT * FROM prayer_boards WHERE id = ? AND user_id = ?");
$stmt->execute([$board_id, $_SESSION['user_id']]);
$board = $stmt->fetch();

if (!$board) {
    header('Location: dashboard.php');
    exit();
}

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

// Fetch prayers for an envelope
function getEnvelopePrayers($envelopeId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM prayer_points 
        WHERE envelope_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$envelopeId]);
    return $stmt->fetchAll();
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {

        case 'create_envelope':
            $board_id = $_POST['board_id'];
            $name = trim($_POST['name']);
            $color = $_POST['color'];
            
            // Verify board ownership
            $check_stmt = $pdo->prepare("SELECT id FROM prayer_boards WHERE id = ? AND user_id = ?");
            $check_stmt->execute([$board_id, $_SESSION['user_id']]);
            if (!$check_stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            // Get the next order index
            $order_stmt = $pdo->prepare("SELECT COALESCE(MAX(order_index), -1) + 1 FROM envelopes WHERE board_id = ?");
            $order_stmt->execute([$board_id]);
            $order_index = $order_stmt->fetchColumn();
            
            // Insert the new envelope
            $stmt = $pdo->prepare("
                INSERT INTO envelopes (board_id, name, color, order_index) 
                VALUES (?, ?, ?, ?)
            ");
            $success = $stmt->execute([$board_id, $name, $color, $order_index]);
            
            if ($success) {
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            exit;

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

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div id="envelopes" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($envelopes as $envelope): ?>
                <div class="envelope bg-white rounded-lg shadow-md p-6" 
                     data-id="<?php echo $envelope['id']; ?>"
                     style="background-color: <?php echo htmlspecialchars($envelope['color']); ?>">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($envelope['name']); ?></h3>
                        <div class="flex items-center space-x-2">
                            <button onclick="editEnvelope(<?php echo $envelope['id']; ?>)"
                                    class="text-gray-500 hover:text-gray-700">
                                Edit
                            </button>
                            <button onclick="deleteEnvelope(<?php echo $envelope['id']; ?>)"
                                    class="text-red-500 hover:text-red-700">
                                Delete
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        <p class="text-gray-600">
                            Total Prayers: <?php echo $envelope['prayer_count']; ?>
                        </p>
                        <p class="text-green-600">
                            Answered: <?php echo $envelope['answered_count']; ?>
                        </p>
                    </div>

                    <button onclick="viewPrayers(<?php echo $envelope['id']; ?>)"
                            class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        View Prayers
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Prayer List Modal -->
    <div id="prayerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold" id="modalEnvelopeName"></h2>
                <button onclick="hidePrayerModal()" class="text-gray-500 hover:text-gray-700">×</button>
            </div>

            <form onsubmit="addPrayer(event)" class="mb-6">
                <input type="hidden" id="currentEnvelopeId">
                <div class="flex space-x-2">
                    <input type="text" id="newPrayer" 
                           class="flex-1 p-2 border rounded" 
                           placeholder="Enter your prayer request...">
                    <button type="submit" 
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add
                    </button>
                </div>
            </form>

            <div id="prayerList" class="space-y-4"></div>
        </div>
    </div>

    <!-- Create/Edit Envelope Modal -->
    <div id="envelopeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 w-96">
            <h2 class="text-xl font-bold mb-4" id="envelopeModalTitle">Create New Envelope</h2>
            <form onsubmit="saveEnvelope(event)" class="space-y-4">
                <input type="hidden" id="editEnvelopeId">
                <div>
                    <label class="block text-gray-700 mb-2">Envelope Name</label>
                    <input type="text" id="envelopeName" class="w-full p-2 border rounded" required>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Color</label>
                    <input type="color" id="envelopeColor" class="w-full p-2 border rounded">
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="hideEnvelopeModal()" 
                            class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Initialize Sortable for envelope drag-and-drop
    new Sortable(document.getElementById('envelopes'), {
        animation: 150,
        onEnd: function(evt) {
            const envelopeId = evt.item.getAttribute('data-id');
            const newIndex = evt.newIndex;
            
            fetch('board.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_order&envelope_id=${envelopeId}&new_index=${newIndex}`
            });
        }
    });

    // Envelope management functions
    function showEnvelopeModal() {
        // Reset form
        document.getElementById('editEnvelopeId').value = '';
        document.getElementById('envelopeName').value = '';
        document.getElementById('envelopeColor').value = '#ffffff';
        document.getElementById('envelopeModalTitle').textContent = 'Create New Envelope';
        modal.show('envelopeModal');
    }
    
    function hideEnvelopeModal() {
        modal.hide('envelopeModal');
    }

    async function saveEnvelope(event) {
        event.preventDefault();
        
        // Validate form
        if (!validateForm(event.target)) {
            return;
        }
        
        const name = document.getElementById('envelopeName').value;
        const color = document.getElementById('envelopeColor').value;
        const editId = document.getElementById('editEnvelopeId').value;
        
        try {
            // Get board ID from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const boardId = urlParams.get('id');
            
            if (editId) {
                // Handle edit case if needed
                // await prayerBoard.updateEnvelope(editId, { name, color });
            } else {
                await prayerBoard.createEnvelope(boardId, {
                    name,
                    color
                });
            }
            
            hideEnvelopeModal();
            // Reload the page to show the new envelope
            window.location.reload();
        } catch (error) {
            // Error handling is managed by the API function
            console.error('Failed to save envelope:', error);
        }
    }

    // Prayer management functions
    async function viewPrayers(envelopeId) {
        const response = await fetch(`get_prayers.php?envelope_id=${envelopeId}`);
        const prayers = await response.json();
        
        document.getElementById('currentEnvelopeId').value = envelopeId;
        const prayerList = document.getElementById('prayerList');
        prayerList.innerHTML = '';
        
        prayers.forEach(prayer => {
            const div = document.createElement('div');
            div.className = 'flex justify-between items-center p-4 bg-gray-50 rounded';
            div.innerHTML = `
                <span class="${prayer.answered_at ? 'line-through text-gray-500' : ''}">${prayer.content}</span>
                <button onclick="markAnswered(${prayer.id})" 
                        class="${prayer.answered_at ? 'text-green-500' : 'text-gray-500'} hover:text-green-700">
                    ${prayer.answered_at ? 'Answered' : 'Mark as Answered'}
                </button>
            `;
            prayerList.appendChild(div);
        });
        
        modal.show('prayerModal');
    }

    async function addPrayer(event) {
        event.preventDefault();
        const envelopeId = document.getElementById('currentEnvelopeId').value;
        const content = document.getElementById('newPrayer').value;
        
        try {
            await prayerBoard.addPrayer(envelopeId, content);
            document.getElementById('newPrayer').value = '';
            await viewPrayers(envelopeId);
        } catch (error) {
            console.error('Failed to add prayer:', error);
        }
    }

    async function markAnswered(prayerId) {
        try {
            await prayerBoard.markAnswered(prayerId);
            const envelopeId = document.getElementById('currentEnvelopeId').value;
            await viewPrayers(envelopeId);
        } catch (error) {
            console.error('Failed to mark prayer as answered:', error);
        }
    }
    
    function hidePrayerModal() {
        modal.hide('prayerModal');
    }
</script>

    <!-- <script>
        // Initialize Sortable for envelope drag-and-drop
        new Sortable(document.getElementById('envelopes'), {
            animation: 150,
            onEnd: function(evt) {
                const envelopeId = evt.item.getAttribute('data-id');
                const newIndex = evt.newIndex;
                
                fetch('board.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_order&envelope_id=${envelopeId}&new_index=${newIndex}`
                });
            }
        });

        // Prayer management functions
        async function viewPrayers(envelopeId) {
            const response = await fetch(`get_prayers.php?envelope_id=${envelopeId}`);
            const prayers = await response.json();
            
            document.getElementById('currentEnvelopeId').value = envelopeId;
            const prayerList = document.getElementById('prayerList');
            prayerList.innerHTML = '';
            
            prayers.forEach(prayer => {
                const div = document.createElement('div');
                div.className = 'flex justify-between items-center p-4 bg-gray-50 rounded';
                div.innerHTML = `
                    <span class="${prayer.answered_at ? 'line-through text-gray-500' : ''}">${prayer.content}</span>
                    <button onclick="markAnswered(${prayer.id})" 
                            class="${prayer.answered_at ? 'text-green-500' : 'text-gray-500'} hover:text-green-700">
                        ${prayer.answered_at ? 'Answered' : 'Mark as Answered'}
                    </button>
                `;
                prayerList.appendChild(div);
            });
            
            document.getElementById('prayerModal').classList.remove('hidden');
        }

        async function addPrayer(event) {
            event.preventDefault();
            const envelopeId = document.getElementById('currentEnvelopeId').value;
            const content = document.getElementById('newPrayer').value;
            
            const response = await fetch('board.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add_prayer&envelope_id=${envelopeId}&content=${encodeURIComponent(content)}`
            });
            
            if ((await response.json()).success) {
                document.getElementById('newPrayer').value = '';
                viewPrayers(envelopeId);
            }
        }

        async function markAnswered(prayerId) {
            const response = await fetch('board.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_answered&prayer_id=${prayerId}`
            });
            
            if ((await response.json()).success) {
                const envelopeId = document.getElementById('currentEnvelopeId').value;
                viewPrayers(envelopeId);
            }
        }

        // Modal management functions
        function showEnvelopeModal() {
            document.getElementById('envelopeModal').classList.remove('hidden');
        }
        
        function hideEnvelopeModal() {
            document.getElementById('envelopeModal').classList.add('hidden');
        }
        
        function hidePrayerModal() {
            document.getElementById('prayerModal').classList.add('hidden');
        }
    </script> -->
</body>
</html>