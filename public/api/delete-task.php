<?php
require_once __DIR__ . '/../../app/Auth.php';
require_once __DIR__ . '/../../app/Models/Task.php';

header('Content-Type: application/json');

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['task_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing task ID']);
    exit();
}

$taskId = $input['task_id'];
$taskModel = new Task();

// Check if task exists
$task = $taskModel->getById($taskId);
if (!$task) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Task not found']);
    exit();
}

// Delete task
$result = $taskModel->delete($taskId);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
}
?>
