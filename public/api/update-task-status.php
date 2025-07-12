<?php
require_once __DIR__ . '/../../app/Auth.php';
require_once __DIR__ . '/../../app/Models/Task.php';
require_once __DIR__ . '/../../app/Models/User.php';
require_once __DIR__ . '/../../app/Services/EmailService.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Fallback to POST data if JSON input is empty
    if (empty($input)) {
        $input = $_POST;
    }

    if (!isset($input['task_id']) || !isset($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    $taskId = $input['task_id'];
    $newStatus = $input['status'];

    // Validate status
    $validStatuses = ['Pending', 'In Progress', 'Completed'];
    if (!in_array($newStatus, $validStatuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }

    $taskModel = new Task();
    $userModel = new User();

    // Get current task details
    $task = $taskModel->getById($taskId);
    if (!$task) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Task not found']);
        exit();
    }

    // Check permissions
    if (Auth::isUser() && $task['assigned_to'] != Auth::id()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You can only update your own tasks']);
        exit();
    }

    // Update task status
    $result = $taskModel->updateStatus($taskId, $newStatus);
    
    if ($result) {
        // Send email notification if task is completed
        if ($newStatus === 'Completed' && $task['status'] !== 'Completed') {
            $assignedUser = $userModel->getById($task['assigned_to']);
            $createdBy = $userModel->getById($task['created_by']);
            
            if ($assignedUser && $createdBy) {
                $emailService = new EmailService();
                // You can add a method to send completion notification
                // $emailService->sendTaskCompletionNotification(...);
            }
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Task status updated successfully',
            'task_id' => $taskId,
            'new_status' => $newStatus,
            'updated_by' => Auth::user()['name']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

$oldStatus = $task['status'];

// Update task status
$result = $taskModel->updateStatus($taskId, $newStatus);

if ($result) {
    // Send email notification if status changed
    if ($oldStatus !== $newStatus) {
        $emailService = new EmailService();
        $emailService->sendTaskStatusUpdateNotification(
            $task['assigned_user_email'],
            $task['assigned_user_name'],
            $task['title'],
            $oldStatus,
            $newStatus
        );
    }
    
    echo json_encode(['success' => true, 'message' => 'Task status updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
}
?>
