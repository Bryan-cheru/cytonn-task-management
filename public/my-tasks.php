<?php
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Models/Task.php';

Auth::requireAuth();

$taskModel = new Task();
$currentUser = Auth::user();

// Handle status updates
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $result = $taskModel->updateStatus($_POST['task_id'], $_POST['status']);
    if ($result) {
        $message = "Task status updated successfully!";
    } else {
        $error = "Failed to update task status.";
    }
}

// Get user's tasks
$myTasks = $taskModel->getTasksByUser(Auth::id());

// Calculate statistics
$totalTasks = count($myTasks);
$pendingTasks = count(array_filter($myTasks, fn($task) => $task['status'] === 'Pending'));
$inProgressTasks = count(array_filter($myTasks, fn($task) => $task['status'] === 'In Progress'));
$completedTasks = count(array_filter($myTasks, fn($task) => $task['status'] === 'Completed'));
$overdueTasks = count(array_filter($myTasks, function($task) {
    $deadline = new DateTime($task['deadline']);
    $today = new DateTime();
    return $deadline < $today && $task['status'] !== 'Completed';
}));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - Cytonn Task Management System</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .stat-card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-2px);
        }

        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.info { border-left-color: #0ea5e9; }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: var(--secondary-color);
            font-weight: 500;
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

        .task-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .task-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .task-card.overdue {
            border-color: var(--danger-color);
            background-color: rgba(220, 38, 38, 0.05);
        }

        .task-title {
            font-weight: 600;
            color: #1e293b;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .task-description {
            color: var(--secondary-color);
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .task-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
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

        .deadline-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--secondary-color);
            font-size: 0.875rem;
        }

        .deadline-info.overdue {
            color: var(--danger-color);
            font-weight: 600;
        }

        .status-update-form {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
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

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .task-meta {
                flex-direction: column;
                align-items: flex-start;
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
                    <a href="my-tasks.php" class="nav-link active">
                        <i class="fas fa-clipboard-list"></i>
                        My Tasks
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
                    <i class="fas fa-clipboard-list me-2"></i>
                    My Tasks
                </h1>
                
                <div class="user-info">
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars($currentUser['name']) ?></div>
                        <small class="text-muted">User</small>
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
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-number"><?= $totalTasks ?></div>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-number"><?= $pendingTasks ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    
                    <div class="stat-card info">
                        <div class="stat-number"><?= $inProgressTasks ?></div>
                        <div class="stat-label">In Progress</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-number"><?= $completedTasks ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                    
                    <?php if ($overdueTasks > 0): ?>
                    <div class="stat-card danger">
                        <div class="stat-number"><?= $overdueTasks ?></div>
                        <div class="stat-label">Overdue</div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tasks List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            My Assigned Tasks
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($myTasks)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No tasks assigned</h5>
                            <p class="text-muted">You don't have any tasks assigned yet. Check back later!</p>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <?php foreach ($myTasks as $task): 
                                $deadlineDate = new DateTime($task['deadline']);
                                $today = new DateTime();
                                $isOverdue = $deadlineDate < $today && $task['status'] !== 'Completed';
                                $daysUntilDeadline = $today->diff($deadlineDate)->days;
                                $isPastDeadline = $deadlineDate < $today;
                            ?>
                            <div class="col-lg-6 col-xl-4">
                                <div class="task-card <?= $isOverdue ? 'overdue' : '' ?>">
                                    <div class="task-title"><?= htmlspecialchars($task['title']) ?></div>
                                    <div class="task-description"><?= htmlspecialchars($task['description']) ?></div>
                                    
                                    <div class="task-meta">
                                        <div>
                                            <span class="status-badge status-<?= str_replace(' ', '-', strtolower($task['status'])) ?>">
                                                <?= $task['status'] ?>
                                            </span>
                                        </div>
                                        <div class="deadline-info <?= $isOverdue ? 'overdue' : '' ?>">
                                            <i class="fas fa-calendar"></i>
                                            <?php if ($isOverdue): ?>
                                                <span>Overdue by <?= $daysUntilDeadline ?> day<?= $daysUntilDeadline != 1 ? 's' : '' ?></span>
                                            <?php elseif ($isPastDeadline): ?>
                                                <span>Due <?= $deadlineDate->format('M d, Y') ?></span>
                                            <?php else: ?>
                                                <span><?= $daysUntilDeadline == 0 ? 'Due today' : 'Due in ' . $daysUntilDeadline . ' day' . ($daysUntilDeadline != 1 ? 's' : '') ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($task['status'] !== 'Completed'): ?>
                                    <div class="status-update-form">
                                        <form method="POST" class="d-flex gap-2 align-items-center">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" style="width: auto;">
                                                <option value="Pending" <?= $task['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="In Progress" <?= $task['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="Completed" <?= $task['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save me-1"></i> Update
                                            </button>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            Assigned by: <?= htmlspecialchars($task['created_by_name']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Auto-refresh every 5 minutes
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 300000);
    </script>
</body>
</html>
