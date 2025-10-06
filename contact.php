<?php
/**
 * Contact Form API Endpoint
 * Luis Marte Portfolio Backend
 */

// Set headers for CORS and JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once '../config/database.php';
require_once '../classes/EmailHelper.php';

class ContactAPI {
    private $db;
    private $emailHelper;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->emailHelper = new EmailHelper();
    }
    
    public function handleContactForm() {
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $validation = $this->validateInput($input);
            if (!$validation['valid']) {
                return $this->sendResponse(false, $validation['message'], 400);
            }
            
            // Rate limiting check
            if (!$this->checkRateLimit()) {
                return $this->sendResponse(false, 'Too many requests. Please try again later.', 429);
            }
            
            // Sanitize input
            $contactData = $this->sanitizeInput($input);
            
            // Add metadata
            $contactData['ip_address'] = $this->getClientIP();
            $contactData['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $contactData['created_at'] = date('Y-m-d H:i:s');
            
            // Save to database
            $messageId = $this->saveContactMessage($contactData);
            
            if (!$messageId) {
                throw new Exception('Failed to save message to database');
            }
            
            // Send emails
            $emailResults = $this->sendEmails($contactData);
            
            // Prepare response
            $response = [
                'success' => true,
                'message' => 'Thank you for your message! I will get back to you soon.',
                'message_id' => $messageId,
                'email_sent' => $emailResults['admin_email'],
                'confirmation_sent' => $emailResults['confirmation_email']
            ];
            
            return $this->sendResponse(true, $response['message'], 200, $response);
            
        } catch (Exception $e) {
            $this->logError('Contact form error: ' . $e->getMessage());
            return $this->sendResponse(false, 'An error occurred while processing your message. Please try again.', 500);
        }
    }
    
    private function validateInput($input) {
        $required = ['name', 'email', 'subject', 'message'];
        
        // Check if input is valid JSON
        if (!$input) {
            return ['valid' => false, 'message' => 'Invalid JSON data'];
        }
        
        // Check required fields
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                return ['valid' => false, 'message' => ucfirst($field) . ' is required'];
            }
        }
        
        // Validate email
        if (!$this->emailHelper->validateEmail($input['email'])) {
            return ['valid' => false, 'message' => 'Please enter a valid email address'];
        }
        
        // Validate lengths
        if (strlen($input['name']) > 100) {
            return ['valid' => false, 'message' => 'Name must be less than 100 characters'];
        }
        
        if (strlen($input['email']) > 150) {
            return ['valid' => false, 'message' => 'Email must be less than 150 characters'];
        }
        
        if (strlen($input['subject']) > 200) {
            return ['valid' => false, 'message' => 'Subject must be less than 200 characters'];
        }
        
        if (strlen($input['message']) > 5000) {
            return ['valid' => false, 'message' => 'Message must be less than 5000 characters'];
        }
        
        if (strlen($input['message']) < 10) {
            return ['valid' => false, 'message' => 'Message must be at least 10 characters long'];
        }
        
        // Basic spam detection
        if ($this->detectSpam($input)) {
            return ['valid' => false, 'message' => 'Message appears to be spam'];
        }
        
        return ['valid' => true, 'message' => 'Valid'];
    }
    
    private function sanitizeInput($input) {
        return [
            'name' => trim(strip_tags($input['name'])),
            'email' => trim(strtolower($input['email'])),
            'subject' => trim(strip_tags($input['subject'])),
            'message' => trim($input['message']),
            'phone' => isset($input['phone']) ? trim(strip_tags($input['phone'])) : null,
            'company' => isset($input['company']) ? trim(strip_tags($input['company'])) : null
        ];
    }
    
    private function detectSpam($input) {
        $spamWords = ['viagra', 'casino', 'lottery', 'winner', 'congratulations', 'click here', 'free money', 'urgent', 'limited time'];
        $message = strtolower($input['message'] . ' ' . $input['subject']);
        
        foreach ($spamWords as $word) {
            if (strpos($message, $word) !== false) {
                return true;
            }
        }
        
        // Check for excessive links
        if (substr_count($message, 'http') > 2) {
            return true;
        }
        
        return false;
    }
    
    private function checkRateLimit() {
        $ip = $this->getClientIP();
        $hourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $count = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM contact_messages WHERE ip_address = ? AND created_at > ?",
            [$ip, $hourAgo]
        );
        
        return $count['count'] < API_RATE_LIMIT;
    }
    
    private function saveContactMessage($data) {
        $sql = "INSERT INTO contact_messages 
                (name, email, subject, message, phone, company, ip_address, user_agent, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new', ?)";
        
        $params = [
            $data['name'],
            $data['email'],
            $data['subject'],
            $data['message'],
            $data['phone'],
            $data['company'],
            $data['ip_address'],
            $data['user_agent'],
            $data['created_at']
        ];
        
        return $this->db->insert($sql, $params);
    }
    
    private function sendEmails($contactData) {
        $results = [
            'admin_email' => false,
            'confirmation_email' => false
        ];
        
        // Send notification to admin
        try {
            $results['admin_email'] = $this->emailHelper->sendContactNotification($contactData);
        } catch (Exception $e) {
            $this->logError('Failed to send admin notification: ' . $e->getMessage());
        }
        
        // Send confirmation to user
        try {
            $results['confirmation_email'] = $this->emailHelper->sendContactConfirmation($contactData);
        } catch (Exception $e) {
            $this->logError('Failed to send confirmation email: ' . $e->getMessage());
        }
        
        return $results;
    }
    
    private function getClientIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    private function sendResponse($success, $message, $httpCode = 200, $data = []) {
        http_response_code($httpCode);
        
        $response = [
            'success' => $success,
            'message' => $message,
            'timestamp' => date('c')
        ];
        
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }
        
        echo json_encode($response);
        exit();
    }
    
    private function logError($message) {
        if (LOG_ERRORS) {
            $log = date('Y-m-d H:i:s') . " - ContactAPI: " . $message . PHP_EOL;
            @file_put_contents(ERROR_LOG_FILE, $log, FILE_APPEND | LOCK_EX);
        }
    }
}

// Initialize and handle the request
$contactAPI = new ContactAPI();
$contactAPI->handleContactForm();