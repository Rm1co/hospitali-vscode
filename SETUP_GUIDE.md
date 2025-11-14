# Hospital Management System - Setup Guide

## Email Verification Setup (PHPMailer)

### Prerequisites
- PHP with Composer installed
- Gmail account credentials

### Installation Steps

1. **Navigate to project root:**
   ```bash
   cd c:\Apache24\htdocs\hospitali-vscode-1
   ```

2. **Install PHPMailer via Composer:**
   ```bash
   composer install
   ```

   Or if you need to initialize Composer first:
   ```bash
   composer require phpmailer/phpmailer
   ```

3. **Configuration Details:**
   - Email: `brian.ikubu@strathmore.edu`
   - App Password: `vahi auht awkv kyri` (Gmail App-specific password)
   - SMTP Server: `smtp.gmail.com`
   - Port: `587`
   - Encryption: `STARTTLS`

### How It Works

1. User signs up and enters email
2. Clicks "Send Verification Code"
3. System generates random 6-digit code
4. Email sent via PHPMailer to provided email
5. User enters code to verify email
6. Code expires after 5 minutes

### Security Notes

⚠️ **Important**: 
- In production, the verification code should NOT be returned to the client
- Currently returns code for development/testing purposes
- Update `send-verification-code.php` to remove code return before production
- Use environment variables for sensitive credentials

### Features

✅ **Password Strength Meter**
- Real-time feedback as user types
- Requirements checklist:
  - Minimum 8 characters
  - At least one uppercase letter
  - At least one lowercase letter
  - At least one number
  - At least one special character (!@#$%)

✅ **Motivational Medical Quotes**
- 10 rotating quotes displayed on login page
- Click refresh button (↻) to change quote
- Encourages user engagement

✅ **Tab-based Login/Signup**
- Seamless switching between login and signup
- Dark/Light theme support
- Responsive design

✅ **Automatic Redirect**
- Visiting `index.html` automatically redirects to login
- Ensures all users start from login page

### File Structure

```
backend/php/
├── send-verification-code.php (Email verification)
├── login.php (Patient login)
├── register.php (Patient registration)
└── ... (other endpoints)

patients-login.html (Enhanced login page)
patients-login.js (Login functionality)
index.html (Redirects to login)
```

### Troubleshooting

**Error: "PHPMailer not installed"**
- Run: `composer install`
- Ensure vendor folder is created

**Error: "SMTP authentication failed"**
- Verify app-specific password is correct
- Ensure Less Secure Apps is enabled (if not using app password)
- Check Gmail account isn't blocked

**Verification code not received**
- Check spam/junk folder
- Verify email address is correct
- Check server logs for errors

### Email Template

The verification email includes:
- Hospital branding
- Clear instructions
- 6-digit verification code in large format
- Expiration time (5 minutes)
- Alternative text version

### Next Steps for Production

1. Remove code return from API response
2. Store verification codes in database with expiry
3. Add rate limiting to prevent brute force
4. Use environment variables for credentials
5. Implement email templates
6. Add spam prevention measures
