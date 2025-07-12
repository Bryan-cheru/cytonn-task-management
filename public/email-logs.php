<?php
require_once __DIR__ . '/../app/Auth.php';
require_once __DIR__ . '/../app/Services/EmailService.php';

Auth::requireAuth();

// Check if user is admin
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$emailService = new EmailService();
$emailLogs = $emailService->getEmailLogs(100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Logs - Cytonn Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .log-entry { font-family: 'Courier New', monospace; font-size: 12px; }
        .log-success { color: #198754; }
        .log-failed { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="bg-dark text-white min-vh-100 p-3">
                    <h5><i class="fas fa-tasks"></i> Cytonn TMS</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link text-white">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage-tasks.php" class="nav-link text-white">
                                <i class="fas fa-tasks"></i> Manage Tasks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage-users.php" class="nav-link text-white">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="email-logs.php" class="nav-link text-white active">
                                <i class="fas fa-envelope"></i> Email Logs
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a href="auth/logout.php" class="nav-link text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-envelope"></i> Email Logs</h2>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary" onclick="location.reload()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Email Activity Log</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($emailLogs)): ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Email Logs Found</h5>
                                    <p class="text-muted">No emails have been sent yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Development Mode:</strong> Emails are logged here instead of being sent. 
                                    In production, configure SMTP settings in EmailService.php to send real emails.
                                </div>
                                
                                <div style="max-height: 600px; overflow-y: auto;">
                                    <?php foreach ($emailLogs as $log): ?>
                                        <?php
                                        $isSuccess = strpos($log, 'SUCCESS') !== false;
                                        $logClass = $isSuccess ? 'log-success' : 'log-failed';
                                        ?>
                                        <div class="log-entry <?php echo $logClass; ?> mb-1 p-2 border-bottom">
                                            <?php echo htmlspecialchars($log); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Instructions Card -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-cog"></i> Configure Email Sending</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>To enable real email sending in production:</strong></p>
                            <ol>
                                <li>Edit <code>app/Services/EmailService.php</code></li>
                                <li>Set your SMTP credentials in the class properties:
                                    <ul>
                                        <li><code>$smtp_username</code> - Your email address</li>
                                        <li><code>$smtp_password</code> - Your app-specific password</li>
                                        <li><code>$smtp_host</code> - SMTP server (default: smtp.gmail.com)</li>
                                    </ul>
                                </li>
                                <li>For Gmail: Enable 2-factor authentication and create an App Password</li>
                                <li>The system will automatically switch to SMTP mode when credentials are configured</li>
                            </ol>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Security Note:</strong> Never commit SMTP credentials to version control. 
                                Use environment variables or a separate config file in production.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
