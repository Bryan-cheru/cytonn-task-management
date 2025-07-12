<?php
class EmailService {
    private $from_email = 'noreply@cytonn.com';
    private $from_name = 'Cytonn Task Management System';
    private $smtp_enabled = false; // Set to true to enable SMTP
    
    // SMTP Configuration (for Gmail/Outlook)
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username = 'briancheruiyot501@gmail.com'; // Your Gmail address
    private $smtp_password = 'adru bsik pkph ryqp'; // Your app password (with spaces)
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
        // Gmail requires proper SMTP authentication with STARTTLS
        // We'll use a simple socket-based SMTP implementation
        
        $smtp_server = $this->smtp_host;
        $smtp_port = $this->smtp_port;
        $smtp_username = $this->smtp_username;
        $smtp_password = $this->smtp_password;
        
        try {
            // Create socket connection
            $socket = fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
            
            if (!$socket) {
                $this->logEmail($to, $subject, false, "Could not connect to SMTP server: $errstr ($errno)");
                return false;
            }
            
            // Read initial response
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '220') {
                $this->logEmail($to, $subject, false, "SMTP connection failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send EHLO
            fputs($socket, "EHLO localhost\r\n");
            $response = $this->getMultiLineResponse($socket);
            
            // Start TLS
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '220') {
                $this->logEmail($to, $subject, false, "STARTTLS failed: $response");
                fclose($socket);
                return false;
            }
            
            // Enable crypto
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->logEmail($to, $subject, false, "Failed to enable TLS encryption");
                fclose($socket);
                return false;
            }
            
            // Send EHLO again after TLS
            fputs($socket, "EHLO localhost\r\n");
            $response = $this->getMultiLineResponse($socket);
            
            // Authenticate
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '334') {
                $this->logEmail($to, $subject, false, "AUTH LOGIN failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send username
            fputs($socket, base64_encode($smtp_username) . "\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '334') {
                $this->logEmail($to, $subject, false, "Username authentication failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send password
            fputs($socket, base64_encode($smtp_password) . "\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '235') {
                $this->logEmail($to, $subject, false, "Password authentication failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send mail from
            fputs($socket, "MAIL FROM:<$smtp_username>\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '250') {
                $this->logEmail($to, $subject, false, "MAIL FROM failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send rcpt to
            fputs($socket, "RCPT TO:<$to>\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '250') {
                $this->logEmail($to, $subject, false, "RCPT TO failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send data command
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '354') {
                $this->logEmail($to, $subject, false, "DATA command failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send email headers and body
            $email_data = "From: {$this->from_name} <{$smtp_username}>\r\n";
            $email_data .= "To: $to\r\n";
            $email_data .= "Subject: $subject\r\n";
            $email_data .= "MIME-Version: 1.0\r\n";
            $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
            $email_data .= "\r\n";
            $email_data .= $message;
            $email_data .= "\r\n.\r\n";
            
            fputs($socket, $email_data);
            $response = fgets($socket, 512);
            if (substr($response, 0, 3) != '250') {
                $this->logEmail($to, $subject, false, "Email sending failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send quit
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            $this->logEmail($to, $subject, true, "Successfully sent via Gmail SMTP");
            return true;
            
        } catch (Exception $e) {
            $this->logEmail($to, $subject, false, "SMTP Exception: " . $e->getMessage());
            return false;
        }
    }
    
    private function getMultiLineResponse($socket) {
        $response = '';
        while (true) {
            $line = fgets($socket, 512);
            $response .= $line;
            if (substr($line, 3, 1) != '-') {
                break;
            }
        }
        return $response;
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
