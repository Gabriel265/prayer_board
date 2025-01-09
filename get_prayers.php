<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$envelope_id = $_GET['envelope_id'] ?? null;

if (!$envelope_id) {
    echo json_encode(['error' => 'No envelope ID provided']);
    exit;
}

// Verify the envelope belongs to a board owned by the current user
$stmt = $pdo->prepare("
    SELECT p.* 
    FROM prayer_points p
    JOIN envelopes e ON p.envelope_id = e.id
    JOIN prayer_boards b ON e.board_id = b.id
    WHERE e.id = ? AND b.user_id = ?
    ORDER BY p.answered_at ASC, p.created_at DESC
");

$stmt->execute([$envelope_id, $_SESSION['user_id']]);
$prayers = $stmt->fetchAll();

echo json_encode($prayers);