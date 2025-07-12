<?php
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Models/Task.php';
require_once __DIR__ . '/../app/Models/User.php';

Auth::requireAuth();

$taskModel = new Task();
$userModel = new User();

// Get statistics
$stats = $taskModel->getStatistics();
$user = Auth::user();

// Get tasks based on user role
if (Auth::isAdmin()) {
    $tasks = $taskModel->getAll();
    $users = $userModel->getAll();
} else {
    $tasks = $taskModel->getTasksByUser(Auth::id());
}

// Calculate additional metrics
$totalUsers = Auth::isAdmin() ? count($users) : 1;
$myTasks = Auth::isAdmin() ? $taskModel->getTasksByUser(Auth::id()) : $tasks;
$myTasksCount = count($myTasks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cytonn Task Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
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

        .sidebar-brand:hover {
            color: white;
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 1rem;
        }

        .stat-icon.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary-color); }
        .stat-icon.warning { background: rgba(217, 119, 6, 0.1); color: var(--warning-color); }
        .stat-icon.info { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
        .stat-icon.success { background: rgba(5, 150, 105, 0.1); color: var(--success-color); }
        .stat-icon.danger { background: rgba(220, 38, 38, 0.1); color: var(--danger-color); }

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

        .task-item {
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .task-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .task-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .task-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: var(--secondary-color);
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

        .status-progress {
            background-color: rgba(14, 165, 233, 0.1);
            color: #0ea5e9;
        }

        .status-completed {
            background-color: rgba(5, 150, 105, 0.1);
            color: var(--success-color);
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

        .dropdown-toggle::after {
            display: none;
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
                grid-template-columns: 1fr;
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
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                
                <?php if (Auth::isAdmin()): ?>
                <div class="nav-item">
                    <a href="manage-tasks.php" class="nav-link">
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
                <div class="nav-item">
                    <a href="email-logs.php" class="nav-link">
                        <i class="fas fa-envelope"></i>
                        Email Logs
                    </a>
                </div>
                <?php else: ?>
                <div class="nav-item">
                    <a href="my-tasks.php" class="nav-link">
                        <i class="fas fa-clipboard-list"></i>
                        My Tasks
                    </a>
                </div>
                <?php endif; ?>
                
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
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </h1>
                
                <div class="user-info">
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars($user['name']) ?></div>
                        <small class="text-muted"><?= ucfirst($user['role']) ?></small>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-icon primary">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-number"><?= $stats['total_tasks'] ?></div>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?= $stats['pending_tasks'] ?></div>
                        <div class="stat-label">Pending Tasks</div>
                    </div>
                    
                    <div class="stat-card info">
                        <div class="stat-icon info">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <div class="stat-number"><?= $stats['in_progress_tasks'] ?></div>
                        <div class="stat-label">In Progress</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?= $stats['completed_tasks'] ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                    
                    <?php if ($stats['overdue_tasks'] > 0): ?>
                    <div class="stat-card danger">
                        <div class="stat-icon danger">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-number"><?= $stats['overdue_tasks'] ?></div>
                        <div class="stat-label">Overdue Tasks</div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (Auth::isAdmin()): ?>
                    <div class="stat-card primary">
                        <div class="stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number"><?= $totalUsers ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Tasks -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    <?= Auth::isAdmin() ? 'Recent Tasks' : 'My Tasks' ?>
                                </h5>
                                <?php if (Auth::isAdmin()): ?>
                                <a href="manage-tasks.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Add Task
                                </a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if (empty($tasks)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No tasks found</h5>
                                    <p class="text-muted">
                                        <?= Auth::isAdmin() ? 'Start by creating your first task.' : 'No tasks assigned to you yet.' ?>
                                    </p>
                                </div>
                                <?php else: ?>
                                <div class="row">
                                    <?php 
                                    $displayTasks = array_slice($tasks, 0, 6); // Show only first 6 tasks
                                    foreach ($displayTasks as $task): 
                                        $statusClass = strtolower(str_replace(' ', '-', $task['status']));
                                        $deadlineDate = new DateTime($task['deadline']);
                                        $today = new DateTime();
                                        $isOverdue = $deadlineDate < $today && $task['status'] !== 'Completed';
                                    ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="task-item <?= $isOverdue ? 'border-danger' : '' ?>">
                                            <div class="task-title"><?= htmlspecialchars($task['title']) ?></div>
                                            <div class="task-description text-muted mb-2">
                                                <?= htmlspecialchars(substr($task['description'], 0, 80)) ?>
                                                <?= strlen($task['description']) > 80 ? '...' : '' ?>
                                            </div>
                                            <div class="task-meta">
                                                <div>
                                                    <span class="status-badge status-<?= str_replace(' ', '-', strtolower($task['status'])) ?>">
                                                        <?= $task['status'] ?>
                                                    </span>
                                                    <?php if ($isOverdue): ?>
                                                    <span class="status-badge ms-1" style="background-color: rgba(220, 38, 38, 0.1); color: var(--danger-color);">
                                                        OVERDUE
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?= $deadlineDate->format('M d, Y') ?>
                                                </small>
                                            </div>
                                            <?php if (Auth::isAdmin()): ?>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    Assigned to: <?= htmlspecialchars($task['assigned_user_name']) ?>
                                                </small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (count($tasks) > 6): ?>
                                <div class="text-center mt-3">
                                    <a href="<?= Auth::isAdmin() ? 'manage-tasks.php' : 'my-tasks.php' ?>" class="btn btn-outline-primary">
                                        View All Tasks (<?= count($tasks) ?>)
                                    </a>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const { createApp } = Vue;
        
        createApp({
            data() {
                return {
                    tasks: <?= json_encode($tasks) ?>,
                    stats: <?= json_encode($stats) ?>,
                    user: <?= json_encode($user) ?>,
                    isAdmin: <?= Auth::isAdmin() ? 'true' : 'false' ?>
                }
            },
            methods: {
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                },
                getStatusClass(status) {
                    return 'status-' + status.toLowerCase().replace(' ', '-');
                },
                isOverdue(deadline, status) {
                    const today = new Date();
                    const taskDeadline = new Date(deadline);
                    return taskDeadline < today && status !== 'Completed';
                }
            },
            mounted() {
                console.log('Dashboard loaded successfully');
            }
        }).mount('#app');

        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }

        // Auto-refresh dashboard every 5 minutes
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 300000);
    </script>
</body>
</html>
