<?php
class EmailService {
    private $from_email = 'noreply@cytonn.com';
    private $from_name = 'Cytonn Task Management System';
    private $smtp_enabled = false; // Set to true to enable SMTP
    
    // SMTP Configuration (for Gmail/Outlook)
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username = ''; // Your email
    private $smtp_password = ''; // Your app password
    private $smtp_encryption = 'tls';

    public function __construct() {
        // Check if SMTP credentials are configured
        $this->smtp_enabled = !empty($this->smtp_username) && !empty($this->smtp_password);
    }

    public function sendTaskAssignmentNotification($userEmail, $userName, $taskTitle, $taskDescription, $deadline) {
        $subject = "New Task Assigned: " . $taskTitle;
        
        $message = $this->getTaskAssignmentTemplate($userName, $taskTitle, $taskDescription, $deadline);
        
        return $this->sendEmail($userEmail, $subject, $message);
    }

    public function sendTaskStatusUpdateNotification($userEmail, $userName, $taskTitle, $oldStatus, $newStatus) {
        $subject = "Task Status Updated: " . $taskTitle;
        
        $message = $this->getStatusUpdateTemplate($userName, $taskTitle, $oldStatus, $newStatus);
        
        return $this->sendEmail($userEmail, $subject, $message);
    }

    private function sendEmail($to, $subject, $message) {
        if ($this->smtp_enabled) {
            return $this->sendSMTPEmail($to, $subject, $message);
        } else {
            return $this->sendLocalEmail($to, $subject, $message);
        }
    }

    private function sendSMTPEmail($to, $subject, $message) {
        // Note: In production, use PHPMailer or similar library
        // This is a simplified implementation
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->from_name} <{$this->from_email}>\r\n";
        
        // For now, log to file instead of sending
        $this->logEmail($to, $subject, true, "SMTP not fully implemented - would send via {$this->smtp_host}");
        
        return true; // Return true for demo purposes
    }

    private function sendLocalEmail($to, $subject, $message) {
        // For local development, just log the email instead of sending
        $this->logEmail($to, $subject, true, "Local development mode - email logged instead of sent");
        
        return true; // Return true so the application doesn't break
    }

    private function getTaskAssignmentTemplate($userName, $taskTitle, $taskDescription, $deadline) {
        return "
        <html>
        <head>
            <title>New Task Assignment</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2563eb; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .task-details { background-color: white; padding: 15px; border-left: 4px solid #2563eb; margin: 15px 0; border-radius: 4px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .deadline { color: #dc3545; font-weight: bold; }
                .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 6px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📋 New Task Assignment</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$userName}</strong>,</p>
                    <p>You have been assigned a new task in the Cytonn Task Management System.</p>
                    
                    <div class='task-details'>
                        <h3>📌 Task Details:</h3>
                        <p><strong>Title:</strong> {$taskTitle}</p>
                        <p><strong>Description:</strong> {$taskDescription}</p>
                        <p><strong>Deadline:</strong> <span class='deadline'>📅 {$deadline}</span></p>
                        <p><strong>Status:</strong> <span style='color: #f59e0b;'>⏳ Pending</span></p>
                    </div>
                    
                    <p>Please log in to the system to view and manage your tasks.</p>
                    <p>If you have any questions, please contact your administrator.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from Cytonn Task Management System.</p>
                    <p>© 2025 Cytonn. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function getStatusUpdateTemplate($userName, $taskTitle, $oldStatus, $newStatus) {
        $statusColors = [
            'pending' => '#f59e0b',
            'in_progress' => '#3b82f6', 
            'completed' => '#10b981'
        ];
        
        $statusIcons = [
            'pending' => '⏳',
            'in_progress' => '🔄',
            'completed' => '✅'
        ];
        
        $oldColor = $statusColors[$oldStatus] ?? '#6b7280';
        $newColor = $statusColors[$newStatus] ?? '#6b7280';
        $newIcon = $statusIcons[$newStatus] ?? '📝';
        
        return "
        <html>
        <head>
            <title>Task Status Updated</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #10b981; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .status-update { background-color: white; padding: 15px; border-left: 4px solid #10b981; margin: 15px 0; border-radius: 4px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔄 Task Status Updated</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$userName}</strong>,</p>
                    <p>The status of your task has been updated.</p>
                    
                    <div class='status-update'>
                        <h3>📝 Task: {$taskTitle}</h3>
                        <p><strong>Status Change:</strong></p>
                        <p>
                            <span class='status-badge' style='background-color: {$oldColor}; color: white;'>{$oldStatus}</span>
                            ➡️
                            <span class='status-badge' style='background-color: {$newColor}; color: white;'>{$newIcon} {$newStatus}</span>
                        </p>
                    </div>
                    
                    <p>Please log in to the system to view the updated task details.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from Cytonn Task Management System.</p>
                    <p>© 2025 Cytonn. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function logEmail($to, $subject, $success, $note = '') {
        $logDir = __DIR__ . '/../../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/email.log';
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'SUCCESS' : 'FAILED';
        $logEntry = "[{$timestamp}] {$status} - To: {$to} | Subject: {$subject}";
        
        if ($note) {
            $logEntry .= " | Note: {$note}";
        }
        
        $logEntry .= "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    // Method to configure SMTP settings (for production use)
    public function configureSMTP($host, $port, $username, $password, $encryption = 'tls') {
        $this->smtp_host = $host;
        $this->smtp_port = $port;
        $this->smtp_username = $username;
        $this->smtp_password = $password;
        $this->smtp_encryption = $encryption;
        $this->smtp_enabled = !empty($username) && !empty($password);
    }

    // Method to get email logs for admin viewing
    public function getEmailLogs($limit = 50) {
        $logFile = __DIR__ . '/../../logs/email.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines); // Most recent first
        
        if ($limit > 0) {
            $lines = array_slice($lines, 0, $limit);
        }
        
        return $lines;
    }
}
?>
