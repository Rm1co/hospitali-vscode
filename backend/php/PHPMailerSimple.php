<?php
/**
 * PHPMailer Wrapper for Hospital Email System
 * This is a simple implementation using PHP's built-in mail with SMTP config
 */

class PHPMailerSimple {
    private $config;
    private $to;
    private $subject;
    private $body;
    private $from;
    private $fromName;
    private $isHTML = true;
    
    public function __construct() {
        $this->config = require __DIR__ . '/email-config.php';
    }
    
    public function setFrom($email, $name = '') {
        $this->from = $email;
        $this->fromName = $name ?: $this->config['from_name'];
    }
    
    public function addAddress($email) {
        $this->to = $email;
    }
    
    public function isHTML($bool) {
        $this->isHTML = $bool;
    }
    
    public function Subject($subject) {
        $this->subject = $subject;
    }
    
    public function Body($body) {
        $this->body = $body;
    }
    
    public function send() {
        // If in development mode, just log the email
        if ($this->config['development_mode']) {
            return $this->logEmail();
        }
        
        // Use PHP's socket connection to send via SMTP
        try {
            return $this->sendViaSMTP();
        } catch (Exception $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }
    
    private function sendViaSMTP() {
        $smtp = @fsockopen(
            $this->config['smtp_host'],
            $this->config['smtp_port'],
            $errno,
            $errstr,
            30
        );
        
        if (!$smtp) {
            throw new Exception("Could not connect to SMTP server: $errstr ($errno)");
        }
        
        $response = fgets($smtp);
        
        // Send EHLO
        fputs($smtp, "EHLO " . $this->config['smtp_host'] . "\r\n");
        $response = fgets($smtp);
        
        // Start TLS
        if ($this->config['smtp_secure'] === 'tls') {
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp);
            stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fputs($smtp, "EHLO " . $this->config['smtp_host'] . "\r\n");
            $response = fgets($smtp);
        }
        
        // Authenticate
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp);
        
        fputs($smtp, base64_encode($this->config['smtp_username']) . "\r\n");
        $response = fgets($smtp);
        
        fputs($smtp, base64_encode($this->config['smtp_password']) . "\r\n");
        $response = fgets($smtp);
        
        if (strpos($response, '235') === false) {
            fclose($smtp);
            throw new Exception("SMTP Authentication failed: $response");
        }
        
        // Send email
        fputs($smtp, "MAIL FROM: <" . $this->from . ">\r\n");
        $response = fgets($smtp);
        
        fputs($smtp, "RCPT TO: <" . $this->to . ">\r\n");
        $response = fgets($smtp);
        
        fputs($smtp, "DATA\r\n");
        $response = fgets($smtp);
        
        // Headers
        $headers = "From: " . $this->fromName . " <" . $this->from . ">\r\n";
        $headers .= "To: <" . $this->to . ">\r\n";
        $headers .= "Subject: " . $this->subject . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        if ($this->isHTML) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        fputs($smtp, $headers . "\r\n");
        fputs($smtp, $this->body . "\r\n");
        fputs($smtp, ".\r\n");
        
        $response = fgets($smtp);
        
        // Quit
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        if (strpos($response, '250') !== false) {
            error_log("Email sent successfully to: " . $this->to);
            return true;
        } else {
            throw new Exception("Failed to send email: $response");
        }
    }
    
    private function logEmail() {
        $logDir = __DIR__ . '/../../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/emails.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $logEntry = "\n" . str_repeat('=', 80) . "\n";
        $logEntry .= "[$timestamp] EMAIL TO: {$this->to}\n";
        $logEntry .= "SUBJECT: {$this->subject}\n";
        $logEntry .= str_repeat('-', 80) . "\n";
        $logEntry .= "MESSAGE:\n";
        $logEntry .= strip_tags($this->body) . "\n";
        $logEntry .= str_repeat('=', 80) . "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        error_log("Email logged to file for: {$this->to}");
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
        error_log("Email error: $message");
    }
}
?>
