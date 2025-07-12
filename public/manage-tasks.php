<?php
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Models/Task.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Services/EmailService.php';

Auth::requireAdmin();

$taskModel = new Task();
$userModel = new User();
$emailService = new EmailService();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $result = $taskModel->create([
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'assigned_to' => $_POST['assigned_to'],
                    'created_by' => Auth::id(),
                    'deadline' => $_POST['deadline'],
                    'status' => $_POST['status'] ?? 'Pending'
                ]);
                
                if ($result) {
                    // Send email notification
                    $assignedUser = $userModel->getById($_POST['assigned_to']);
                    if ($assignedUser) {
                        $emailService->sendTaskAssignmentNotification(
                            $assignedUser['email'],
                            $assignedUser['name'],
                            $_POST['title'],
                            $_POST['description'],
                            $_POST['deadline']
                        );
                    }
                    $message = "Task created successfully!";
                } else {
                    $error = "Failed to create task.";
                }
                break;
                
            case 'update':
                $result = $taskModel->update($_POST['task_id'], [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'assigned_to' => $_POST['assigned_to'],
                    'deadline' => $_POST['deadline'],
                    'status' => $_POST['status']
                ]);
                
                if ($result) {
                    $message = "Task updated successfully!";
                } else {
                    $error = "Failed to update task.";
                }
                break;
                
            case 'delete':
                $result = $taskModel->delete($_POST['task_id']);
                if ($result) {
                    $message = "Task deleted successfully!";
                } else {
                    $error = "Failed to delete task.";
                }
                break;
        }
    }
}

// Get all tasks and users
$tasks = $taskModel->getAll();
$users = $userModel->getUsersForAssignment();
$currentUser = Auth::user();

// Get statistics
$stats = $taskModel->getStatistics();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks - Cytonn Task Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --light-bg: #f8fafc;
            --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--light-bg);
            font-size: 14px;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            width: 280px;
        }

        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin: 0.25rem 1rem;
        }

        .nav-link {
            color: #cbd5e1 !important;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white !important;
            transform: translateX(4px);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: white !important;
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }

        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            background-color: var(--light-bg);
        }

        .top-navbar {
            background: white;
            box-shadow: var(--card-shadow);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .content-area {
            padding: 2rem;
        }

        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            border: none;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .card-header {
            background: none;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .table {
            font-size: 14px;
        }

        .table th {
            background-color: #f8fafc;
            border: none;
            font-weight: 600;
            color: #374151;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e5e7eb;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .status-pending {
            background-color: rgba(217, 119, 6, 0.1);
            color: var(--warning-color);
        }

        .status-in-progress {
            background-color: rgba(14, 165, 233, 0.1);
            color: #0ea5e9;
        }

        .status-completed {
            background-color: rgba(5, 150, 105, 0.1);
            color: var(--success-color);
        }

        .btn-group .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .modal-content {
            border: none;
            border-radius: 1rem;
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 1rem 1.5rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: fixed;
                top: 0;
                left: -100%;
                z-index: 1000;
                transition: left 0.3s ease;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .table-responsive {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Sidebar -->
        <nav class="sidebar position-fixed">
            <div class="sidebar-brand">
                <h4 class="mb-0">
                    <i class="fas fa-tasks me-2"></i>
                    Cytonn Tasks
                </h4>
                <small class="text-light opacity-75">Task Management System</small>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="manage-tasks.php" class="nav-link active">
                        <i class="fas fa-tasks"></i>
                        Manage Tasks
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="manage-users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Manage Users
                    </a>
                </div>
                
                <div class="nav-item mt-4">
                    <a href="auth/logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <div class="top-navbar">
                <h1 class="page-title">
                    <i class="fas fa-tasks me-2"></i>
                    Manage Tasks
                </h1>
                
                <div class="user-info">
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars($currentUser['name']) ?></div>
                        <small class="text-muted">Administrator</small>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Success/Error Messages -->
                <?php if (isset($message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Task Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-primary"><?= $stats['total_tasks'] ?></h3>
                                <small class="text-muted">Total Tasks</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-warning"><?= $stats['pending_tasks'] ?></h3>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-info"><?= $stats['in_progress_tasks'] ?></h3>
                                <small class="text-muted">In Progress</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-success"><?= $stats['completed_tasks'] ?></h3>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tasks Management -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            All Tasks
                        </h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal" onclick="openTaskModal()">
                            <i class="fas fa-plus me-1"></i> Add New Task
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Assigned To</th>
                                        <th>Status</th>
                                        <th>Deadline</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tasks)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No tasks found. Create your first task!</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($tasks as $task): 
                                        $deadlineDate = new DateTime($task['deadline']);
                                        $today = new DateTime();
                                        $isOverdue = $deadlineDate < $today && $task['status'] !== 'Completed';
                                    ?>
                                    <tr <?= $isOverdue ? 'class="table-danger"' : '' ?>>
                                        <td>
                                            <strong><?= htmlspecialchars($task['title']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars(substr($task['description'], 0, 50)) ?>...</small>
                                        </td>
                                        <td>
                                            <i class="fas fa-user me-1"></i>
                                            <?= htmlspecialchars($task['assigned_user_name']) ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= str_replace(' ', '-', strtolower($task['status'])) ?>">
                                                <?= $task['status'] ?>
                                            </span>
                                            <?php if ($isOverdue): ?>
                                            <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Overdue</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= $deadlineDate->format('M d, Y') ?>
                                        </td>
                                        <td>
                                            <?= (new DateTime($task['created_at']))->format('M d, Y') ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" onclick="editTask(<?= htmlspecialchars(json_encode($task)) ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="deleteTask(<?= $task['id'] ?>, '<?= htmlspecialchars($task['title']) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Modal -->
        <div class="modal fade" id="taskModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="taskModalTitle">Add New Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" id="taskForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" id="taskAction" value="create">
                            <input type="hidden" name="task_id" id="taskId">
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="title" class="form-label">Task Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="assigned_to" class="form-label">Assign To *</label>
                                    <select class="form-select" id="assigned_to" name="assigned_to" required>
                                        <option value="">Select User</option>
                                        <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="deadline" class="form-label">Deadline *</label>
                                    <input type="date" class="form-control" id="deadline" name="deadline" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Pending">Pending</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="taskSubmitBtn">
                                <i class="fas fa-save me-1"></i> Save Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the task "<span id="deleteTaskTitle"></span>"?</p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <form method="POST" id="deleteForm">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="task_id" id="deleteTaskId">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i> Delete Task
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum date to today
        document.getElementById('deadline').min = new Date().toISOString().split('T')[0];

        function openTaskModal() {
            document.getElementById('taskModalTitle').textContent = 'Add New Task';
            document.getElementById('taskAction').value = 'create';
            document.getElementById('taskSubmitBtn').innerHTML = '<i class="fas fa-save me-1"></i> Save Task';
            document.getElementById('taskForm').reset();
            document.getElementById('deadline').min = new Date().toISOString().split('T')[0];
        }

        function editTask(task) {
            document.getElementById('taskModalTitle').textContent = 'Edit Task';
            document.getElementById('taskAction').value = 'update';
            document.getElementById('taskId').value = task.id;
            document.getElementById('title').value = task.title;
            document.getElementById('description').value = task.description;
            document.getElementById('assigned_to').value = task.assigned_to;
            document.getElementById('deadline').value = task.deadline;
            document.getElementById('status').value = task.status;
            document.getElementById('taskSubmitBtn').innerHTML = '<i class="fas fa-save me-1"></i> Update Task';
            
            const modal = new bootstrap.Modal(document.getElementById('taskModal'));
            modal.show();
        }

        function deleteTask(taskId, taskTitle) {
            document.getElementById('deleteTaskId').value = taskId;
            document.getElementById('deleteTaskTitle').textContent = taskTitle;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
