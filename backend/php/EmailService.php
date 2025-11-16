<?php
/**
 * Email Service for sending notifications
 * Uses PHP mail() function - configure SMTP in php.ini for production
 */

class EmailService {
    private static $config = null;
    
    private static function getConfig() {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/email-config.php';
        }
        return self::$config;
    }
    
    /**
     * Send admin account creation email
     */
    public static function sendAdminCreationEmail($email, $fullName, $username, $temporaryPassword, $role) {
        $config = self::getConfig();
        $subject = "Your StrathMedics Hospital Admin Account";
        
        $loginUrl = $config['hospital_url'] . '/public/admin/admin-login.html';
        
        $message = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #06b6d4, #0f766e); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .credentials { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #06b6d4; border-radius: 4px; }
        .credential-item { margin: 10px 0; padding: 10px; background: #f3f4f6; border-radius: 4px; }
        .credential-label { font-weight: bold; color: #374151; }
        .credential-value { color: #1f2937; font-family: monospace; font-size: 16px; }
        .button { display: inline-block; background: #06b6d4; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Welcome to StrathMedics Hospital</h1>
            <p>Admin Portal Access</p>
        </div>
        <div class='content'>
            <h2>Hello " . htmlspecialchars($fullName) . ",</h2>
            
            <p>Your administrator account has been created for <strong>" . htmlspecialchars($role) . "</strong> role at StrathMedics Hospital Management System.</p>
            
            <div class='credentials'>
                <h3>Your Login Credentials</h3>
                
                <div class='credential-item'>
                    <div class='credential-label'>Username:</div>
                    <div class='credential-value'>" . htmlspecialchars($username) . "</div>
                </div>
                
                <div class='credential-item'>
                    <div class='credential-label'>Temporary Password:</div>
                    <div class='credential-value'>" . htmlspecialchars($temporaryPassword) . "</div>
                </div>
                
                <div class='credential-item'>
                    <div class='credential-label'>Login URL:</div>
                    <div class='credential-value'><a href='" . $loginUrl . "'>" . $loginUrl . "</a></div>
                </div>
            </div>
            
            <div style='text-align: center;'>
                <a href='" . $loginUrl . "' class='button'>Login to Admin Portal</a>
            </div>
            
            <div class='warning'>
                <strong>⚠️ Security Notice:</strong>
                <ul style='margin: 10px 0;'>
                    <li>This is a temporary password - you will be required to change it on first login</li>
                    <li>Do not share your credentials with anyone</li>
                    <li>Keep this email secure or delete it after changing your password</li>
                </ul>
            </div>
            
            <h3>Your Role & Permissions</h3>
            <p>As a <strong>" . htmlspecialchars($role) . "</strong>, you have access to specific modules within the hospital management system. After logging in, you'll be directed to your designated dashboard.</p>
            
            <h3>Need Help?</h3>
            <p>If you have any questions or issues accessing your account, please contact the IT department or the Super Administrator.</p>
        </div>
        
        <div class='footer'>
            <p><strong>StrathMedics Hospital Management System</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; " . date('Y') . " StrathMedics Hospital. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
";
        
        return self::sendEmail($email, $subject, $message);
    }
    
    /**
     * Send staff account creation email
     */
    public static function sendStaffCreationEmail($email, $fullName, $temporaryPassword, $role, $department, $activationToken = null) {
        $config = self::getConfig();
        $subject = "Your StrathMedics Hospital Staff Account - Activate Now";
        
        // Use activation link if token is provided, otherwise use signup link
        $activationUrl = $activationToken 
            ? $config['hospital_url'] . '/public/staff/staff-activate.html?token=' . urlencode($activationToken)
            : $config['hospital_url'] . '/public/staff/staff-signup.html?email=' . urlencode($email);
        
        $signupUrl = $activationUrl; // For backward compatibility
        
        $message = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #06b6d4, #0f766e); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .credentials { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #06b6d4; border-radius: 4px; }
        .credential-item { margin: 10px 0; padding: 10px; background: #f3f4f6; border-radius: 4px; }
        .credential-label { font-weight: bold; color: #374151; }
        .credential-value { color: #1f2937; font-family: monospace; font-size: 16px; }
        .button { display: inline-block; background: #06b6d4; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .steps { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .step { margin: 15px 0; padding-left: 30px; position: relative; }
        .step:before { content: '→'; position: absolute; left: 0; color: #06b6d4; font-weight: bold; }
        .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Welcome to StrathMedics Hospital</h1>
            <p>Complete Your Account Setup</p>
        </div>
        <div class='content'>
            <h2>Hello " . htmlspecialchars($fullName) . ",</h2>
            
            <p>A staff account has been created for you at StrathMedics Hospital. You have been assigned as <strong>" . htmlspecialchars($role) . "</strong> in the <strong>" . htmlspecialchars($department) . "</strong> department.</p>
            
            <div class='credentials'>
                <h3>Your Account Information</h3>
                
                <div class='credential-item'>
                    <div class='credential-label'>Email:</div>
                    <div class='credential-value'>" . htmlspecialchars($email) . "</div>
                </div>
                
                <div class='credential-item'>
                    <div class='credential-label'>Temporary Password:</div>
                    <div class='credential-value'>" . htmlspecialchars($temporaryPassword) . "</div>
                </div>
                
                <div class='credential-item'>
                    <div class='credential-label'>Role:</div>
                    <div class='credential-value'>" . htmlspecialchars($role) . "</div>
                </div>
                
                <div class='credential-item'>
                    <div class='credential-label'>Department:</div>
                    <div class='credential-value'>" . htmlspecialchars($department) . "</div>
                </div>
            </div>
            
            <div class='steps'>
                <h3>Activate Your Account:</h3>
                <div class='step'>Click the activation button below</div>
                <div class='step'>You'll be directed to the activation page with your email pre-filled</div>
                <div class='step'>Enter your temporary password provided above</div>
                <div class='step'>Create your new secure password</div>
                <div class='step'>Start using your account!</div>
            </div>
            
            <div style='text-align: center;'>
                <a href='" . $signupUrl . "' class='button'>Activate Account Now</a>
            </div>
            
            <p style='margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 6px;'>
                <strong>⚠️ Important:</strong> This activation link is unique to your account. Please activate your account within 7 days. For security, do not share this email with anyone.
            </p>
        </div>
        
        <div class='footer'>
            <p><strong>StrathMedics Hospital Management System</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; " . date('Y') . " StrathMedics Hospital. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
";
        
        return self::sendEmail($email, $subject, $message);
    }
    
    /**
     * Core email sending function
     */
    private static function sendEmail($to, $subject, $htmlMessage) {
        require_once __DIR__ . '/GmailSMTP.php';
        
        try {
            $mailer = new GmailSMTP();
            return $mailer->send($to, $subject, $htmlMessage);
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
    

}
?>
