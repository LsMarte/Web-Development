<?php
/**
 * Email Helper Class
 * Luis Marte Portfolio Backend
 */

require_once '../config/config.php';

class EmailHelper {
    private $headers;
    private $smtp_settings;
    
    public function __construct() {
        $this->smtp_settings = [
            'host' => SMTP_HOST,
            'port' => SMTP_PORT,
            'username' => SMTP_USERNAME,
            'password' => SMTP_PASSWORD,
            'encryption' => SMTP_ENCRYPTION
        ];
        
        $this->headers = [
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8',
            'From' => FROM_NAME . ' <' . FROM_EMAIL . '>',
            'Reply-To' => ADMIN_EMAIL,
            'X-Mailer' => 'PHP/' . phpversion()
        ];
    }
    
    /**
     * Send email using PHP mail() function
     * For production, consider using PHPMailer or similar library
     */
    public function sendEmail($to, $subject, $body, $additionalHeaders = []) {
        try {
            // Merge additional headers
            $headers = array_merge($this->headers, $additionalHeaders);
            
            // Convert headers array to string
            $headerString = '';
            foreach ($headers as $key => $value) {
                $headerString .= $key . ': ' . $value . "\r\n";
            }
            
            // Send email
            $result = mail($to, $subject, $body, $headerString);
            
            if (!$result) {
                throw new Exception('Failed to send email');
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->logError('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send contact form notification to admin
     */
    public function sendContactNotification($contactData) {
        $subject = 'New Contact Form Submission - ' . $contactData['subject'];
        
        $body = $this->getEmailTemplate('contact_notification', [
            'name' => htmlspecialchars($contactData['name']),
            'email' => htmlspecialchars($contactData['email']),
            'subject' => htmlspecialchars($contactData['subject']),
            'message' => nl2br(htmlspecialchars($contactData['message'])),
            'date' => date('Y-m-d H:i:s'),
            'ip' => $contactData['ip_address'] ?? 'Unknown'
        ]);
        
        return $this->sendEmail(ADMIN_EMAIL, $subject, $body);
    }
    
    /**
     * Send confirmation email to contact form submitter
     */
    public function sendContactConfirmation($contactData) {
        $subject = 'Thank you for contacting ' . FROM_NAME;
        
        $body = $this->getEmailTemplate('contact_confirmation', [
            'name' => htmlspecialchars($contactData['name']),
            'message' => nl2br(htmlspecialchars($contactData['message']))
        ]);
        
        return $this->sendEmail($contactData['email'], $subject, $body);
    }
    
    /**
     * Get email template with variable replacement
     */
    private function getEmailTemplate($templateType, $variables = []) {
        // Default templates (in production, load from database)
        $templates = [
            'contact_notification' => '
                <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
                        <h2 style="color: #667eea; text-align: center;">New Contact Form Submission</h2>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                            <p><strong>Name:</strong> {{name}}</p>
                            <p><strong>Email:</strong> {{email}}</p>
                            <p><strong>Subject:</strong> {{subject}}</p>
                            <p><strong>Date:</strong> {{date}}</p>
                            <p><strong>IP Address:</strong> {{ip}}</p>
                        </div>
                        <div style="background: white; padding: 15px; border: 1px solid #eee; border-radius: 5px;">
                            <h3 style="margin-top: 0;">Message:</h3>
                            <p>{{message}}</p>
                        </div>
                        <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px;">
                            <p>This message was sent from your portfolio contact form.</p>
                        </div>
                    </div>
                </body>
                </html>
            ',
            'contact_confirmation' => '
                <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
                        <h2 style="color: #667eea; text-align: center;">Thank You for Your Message!</h2>
                        <p>Dear {{name}},</p>
                        <p>Thank you for reaching out to me through my portfolio website. I have received your message and will get back to you within 24-48 hours.</p>
                        
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                            <h3 style="margin-top: 0;">Your Message:</h3>
                            <p>{{message}}</p>
                        </div>
                        
                        <p>In the meantime, feel free to check out my latest projects on my portfolio or connect with me on social media.</p>
                        
                        <div style="text-align: center; margin: 20px 0;">
                            <a href="https://github.com/LsMarte" style="display: inline-block; margin: 0 10px; color: #667eea; text-decoration: none;">GitHub</a>
                            <a href="https://www.linkedin.com/in/luis-marte-shim/" style="display: inline-block; margin: 0 10px; color: #667eea; text-decoration: none;">LinkedIn</a>
                        </div>
                        
                        <p>Best regards,<br>
                        <strong>Luis Marte</strong><br>
                        Frontend Developer<br>
                        <a href="mailto:' . ADMIN_EMAIL . '" style="color: #667eea;">' . ADMIN_EMAIL . '</a></p>
                        
                        <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px;">
                            <p>This is an automated response. Please do not reply to this email.</p>
                        </div>
                    </div>
                </body>
                </html>
            '
        ];
        
        if (!isset($templates[$templateType])) {
            return '<p>Template not found</p>';
        }
        
        $template = $templates[$templateType];
        
        // Replace variables
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        
        return $template;
    }
    
    /**
     * Validate email address
     */
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Log errors
     */
    private function logError($message) {
        if (LOG_ERRORS) {
            $log = date('Y-m-d H:i:s') . " - EmailHelper: " . $message . PHP_EOL;
            @file_put_contents(ERROR_LOG_FILE, $log, FILE_APPEND | LOCK_EX);
        }
    }
}