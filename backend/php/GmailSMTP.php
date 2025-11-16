<?php
/**
 * PHPMailer Integration using Gmail SMTP
 * This uses a simplified approach that works better with Gmail
 */

// Load PHPMailer if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class GmailSMTP {
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/email-config.php';
    }
    
    public function send($to, $subject, $htmlBody) {
        // If in development mode, just log
        if ($this->config['development_mode']) {
            return $this->logEmail($to, $subject, $htmlBody);
        }
        
        // Use PHPMailer library if available, otherwise use simple mail()
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->sendWithPHPMailer($to, $subject, $htmlBody);
        } else {
            return $this->sendWithMailFunction($to, $subject, $htmlBody);
        }
    }
    
    private function sendWithPHPMailer($to, $subject, $htmlBody) {
        try {
            $mail = new PHPMailer(true);
            
            // Enable verbose debug output (comment out in production)
            // $mail->SMTPDebug = 2;
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_secure'];
            $mail->Port = $this->config['smtp_port'];
            $mail->Timeout = 30; // 30 seconds timeout
            
            // Recipients
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);
            
            $result = $mail->send();
            if ($result) {
                error_log("✓ Email sent successfully to: $to via PHPMailer");
                return true;
            } else {
                $this->logError("PHPMailer send returned false");
                return false;
            }
            
        } catch (Exception $e) {
            $errorMsg = isset($mail) ? $mail->ErrorInfo : $e->getMessage();
            error_log("✗ PHPMailer Error: $errorMsg");
            $this->logError("PHPMailer: $errorMsg");
            return false;
        }
    }
    
    private function sendWithMailFunction($to, $subject, $htmlBody) {
        // Fallback to PHP mail() function
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $this->config['from_name'] . " <" . $this->config['from_email'] . ">" . "\r\n";
        
        try {
            if (mail($to, $subject, $htmlBody, $headers)) {
                error_log("Email sent successfully to: $to via mail()");
                return true;
            } else {
                error_log("Failed to send email via mail()");
                $this->logError("PHP mail() function failed");
                return false;
            }
        } catch (Exception $e) {
            $this->logError("mail() error: " . $e->getMessage());
            return false;
        }
    }
    
    private function logEmail($to, $subject, $htmlBody) {
        $logDir = __DIR__ . '/../../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/emails.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $logEntry = "\n" . str_repeat('=', 80) . "\n";
        $logEntry .= "[$timestamp] EMAIL TO: $to\n";
        $logEntry .= "SUBJECT: $subject\n";
        $logEntry .= str_repeat('-', 80) . "\n";
        $logEntry .= "MESSAGE:\n";
        $logEntry .= strip_tags($htmlBody) . "\n";
        $logEntry .= str_repeat('=', 80) . "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        error_log("Email logged to file for: $to");
        return true;
    }
    
    private function logError($message) {
        $logDir = __DIR__ . '/../../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/email_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] ERROR: $message\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
?>
