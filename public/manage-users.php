<?php
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Models/User.php';

Auth::requireAdmin();

$userModel = new User();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $result = $userModel->create([
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'role' => $_POST['role']
                ]);
                
                if ($result) {
                    $message = "User created successfully!";
                } else {
                    $error = "Failed to create user. Email might already exist.";
                }
                break;
                
            case 'update':
                $result = $userModel->update($_POST['user_id'], [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'role' => $_POST['role']
                ]);
                
                if ($result) {
                    $message = "User updated successfully!";
                } else {
                    $error = "Failed to update user.";
                }
                break;
                
            case 'delete':
                $result = $userModel->delete($_POST['user_id']);
                if ($result) {
                    $message = "User deleted successfully!";
                } else {
                    $error = "Failed to delete user.";
                }
                break;
                
            case 'reset_password':
                $result = $userModel->updatePassword($_POST['user_id'], $_POST['new_password']);
                if ($result) {
                    $message = "Password reset successfully!";
                } else {
                    $error = "Failed to reset password.";
                }
                break;
        }
    }
}

// Get all users
$users = $userModel->getAll();
$currentUser = Auth::user();

// Get user statistics
$totalUsers = count($users);
$adminUsers = count(array_filter($users, fn($user) => $user['role'] === 'admin'));
$regularUsers = count(array_filter($users, fn($user) => $user['role'] === 'user'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Cytonn Task Management System</title>
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

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .role-admin {
            background-color: rgba(220, 38, 38, 0.1);
            color: var(--danger-color);
        }

        .role-user {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
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
                    <a href="manage-tasks.php" class="nav-link">
                        <i class="fas fa-tasks"></i>
                        Manage Tasks
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="manage-users.php" class="nav-link active">
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
                    <i class="fas fa-users me-2"></i>
                    Manage Users
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

                <!-- User Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-primary"><?= $totalUsers ?></h3>
                                <small class="text-muted">Total Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-danger"><?= $adminUsers ?></h3>
                                <small class="text-muted">Administrators</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-info"><?= $regularUsers ?></h3>
                                <small class="text-muted">Regular Users</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Management -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            All Users
                        </h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openUserModal()">
                            <i class="fas fa-plus me-1"></i> Add New User
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No users found. Create your first user!</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3" style="width: 32px; height: 32px; font-size: 12px;">
                                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                                </div>
                                                <strong><?= htmlspecialchars($user['name']) ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-envelope me-1"></i>
                                            <?= htmlspecialchars($user['email']) ?>
                                        </td>
                                        <td>
                                            <span class="role-badge role-<?= $user['role'] ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= (new DateTime($user['created_at']))->format('M d, Y') ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-warning" onclick="resetPassword(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" title="Reset Password">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                                <?php if ($user['id'] !== $currentUser['id']): ?>
                                                <button class="btn btn-outline-danger" onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
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

        <!-- User Modal -->
        <div class="modal fade" id="userModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalTitle">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" id="userForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" id="userAction" value="create">
                            <input type="hidden" name="user_id" id="userId">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3" id="passwordField">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">Role *</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="user">User</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="userSubmitBtn">
                                <i class="fas fa-save me-1"></i> Save User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Reset Modal -->
        <div class="modal fade" id="passwordModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" id="passwordForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="reset_password">
                            <input type="hidden" name="user_id" id="resetUserId">
                            
                            <p>Reset password for user: <strong id="resetUserName"></strong></p>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password *</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key me-1"></i> Reset Password
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
                        <p>Are you sure you want to delete the user "<span id="deleteUserName"></span>"?</p>
                        <p class="text-danger"><small>This action cannot be undone and will also delete all tasks assigned to this user.</small></p>
                    </div>
                    <div class="modal-footer">
                        <form method="POST" id="deleteForm">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" id="deleteUserId">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i> Delete User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openUserModal() {
            document.getElementById('userModalTitle').textContent = 'Add New User';
            document.getElementById('userAction').value = 'create';
            document.getElementById('userSubmitBtn').innerHTML = '<i class="fas fa-save me-1"></i> Save User';
            document.getElementById('userForm').reset();
            document.getElementById('passwordField').style.display = 'block';
            document.getElementById('password').required = true;
        }

        function editUser(user) {
            document.getElementById('userModalTitle').textContent = 'Edit User';
            document.getElementById('userAction').value = 'update';
            document.getElementById('userId').value = user.id;
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('role').value = user.role;
            document.getElementById('userSubmitBtn').innerHTML = '<i class="fas fa-save me-1"></i> Update User';
            document.getElementById('passwordField').style.display = 'none';
            document.getElementById('password').required = false;
            
            const modal = new bootstrap.Modal(document.getElementById('userModal'));
            modal.show();
        }

        function resetPassword(userId, userName) {
            document.getElementById('resetUserId').value = userId;
            document.getElementById('resetUserName').textContent = userName;
            
            const modal = new bootstrap.Modal(document.getElementById('passwordModal'));
            modal.show();
        }

        function deleteUser(userId, userName) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = userName;
            
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
