<?php
/**
 * Portfolio Content API
 * Luis Marte Portfolio Backend
 */

// Set headers for CORS and JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once '../config/database.php';

class PortfolioAPI {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function handleRequest() {
        try {
            $endpoint = $_GET['endpoint'] ?? '';
            
            switch ($endpoint) {
                case 'skills':
                    return $this->getSkills();
                case 'projects':
                    return $this->getProjects();
                case 'services':
                    return $this->getServices();
                case 'stats':
                    return $this->getStats();
                case 'settings':
                    return $this->getPublicSettings();
                default:
                    return $this->getAllContent();
            }
            
        } catch (Exception $e) {
            $this->logError('Portfolio API error: ' . $e->getMessage());
            return $this->sendResponse(false, 'An error occurred while fetching data', 500);
        }
    }
    
    private function getSkills() {
        $skills = $this->db->fetchAll("
            SELECT name, category, proficiency_level, icon_class, description, is_featured
            FROM skills 
            ORDER BY category, sort_order ASC, proficiency_level DESC
        ");
        
        // Group by category
        $groupedSkills = [];
        foreach ($skills as $skill) {
            $category = $skill['category'];
            if (!isset($groupedSkills[$category])) {
                $groupedSkills[$category] = [];
            }
            $groupedSkills[$category][] = $skill;
        }
        
        return $this->sendResponse(true, 'Skills retrieved successfully', 200, [
            'skills' => $groupedSkills,
            'featured_skills' => array_filter($skills, function($skill) {
                return $skill['is_featured'] == 1;
            })
        ]);
    }
    
    private function getProjects() {
        $category = $_GET['category'] ?? null;
        $featured_only = $_GET['featured'] ?? false;
        
        $whereClause = "WHERE is_published = 1";
        $params = [];
        
        if ($category) {
            $whereClause .= " AND category = ?";
            $params[] = $category;
        }
        
        if ($featured_only) {
            $whereClause .= " AND featured = 1";
        }
        
        $projects = $this->db->fetchAll("
            SELECT id, title, slug, short_description, image_url, category, 
                   technologies, project_url, github_url, demo_url, featured
            FROM projects 
            $whereClause
            ORDER BY featured DESC, sort_order ASC, created_at DESC
        ", $params);
        
        // Parse JSON fields
        foreach ($projects as &$project) {
            $project['technologies'] = json_decode($project['technologies'] ?? '[]', true);
        }
        
        return $this->sendResponse(true, 'Projects retrieved successfully', 200, [
            'projects' => $projects
        ]);
    }
    
    private function getServices() {
        $services = $this->db->fetchAll("
            SELECT title, slug, short_description, icon_class, features, is_featured
            FROM services 
            WHERE is_active = 1
            ORDER BY is_featured DESC, sort_order ASC
        ");
        
        // Parse JSON fields
        foreach ($services as &$service) {
            $service['features'] = json_decode($service['features'] ?? '[]', true);
        }
        
        return $this->sendResponse(true, 'Services retrieved successfully', 200, [
            'services' => $services
        ]);
    }
    
    private function getStats() {
        $stats = $this->db->fetchOne("
            SELECT 
                COUNT(DISTINCT p.id) as total_projects,
                COUNT(DISTINCT CASE WHEN p.featured = 1 THEN p.id END) as featured_projects,
                COUNT(DISTINCT s.id) as total_skills,
                COUNT(DISTINCT CASE WHEN s.is_featured = 1 THEN s.id END) as featured_skills,
                COUNT(DISTINCT srv.id) as total_services,
                COUNT(DISTINCT t.id) as total_testimonials,
                COUNT(DISTINCT cm.id) as total_messages,
                COUNT(DISTINCT CASE WHEN cm.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN cm.id END) as recent_messages
            FROM projects p
            CROSS JOIN skills s
            CROSS JOIN services srv
            LEFT JOIN testimonials t ON t.is_approved = 1
            LEFT JOIN contact_messages cm ON 1=1
            WHERE p.is_published = 1 AND srv.is_active = 1
        ");
        
        // Calculate years of experience (from first project or fixed date)
        $experienceYears = date('Y') - 2021; // Adjust start year as needed
        
        $stats['years_experience'] = $experienceYears;
        $stats['client_satisfaction'] = 100; // Can be calculated from testimonials
        
        return $this->sendResponse(true, 'Stats retrieved successfully', 200, [
            'stats' => $stats
        ]);
    }
    
    private function getPublicSettings() {
        $settings = $this->db->fetchAll("
            SELECT setting_key, setting_value, setting_type
            FROM site_settings 
            WHERE is_public = 1
        ");
        
        $settingsArray = [];
        foreach ($settings as $setting) {
            $value = $setting['setting_value'];
            
            // Convert based on type
            switch ($setting['setting_type']) {
                case 'number':
                    $value = (float) $value;
                    break;
                case 'boolean':
                    $value = (bool) $value;
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }
            
            $settingsArray[$setting['setting_key']] = $value;
        }
        
        return $this->sendResponse(true, 'Settings retrieved successfully', 200, [
            'settings' => $settingsArray
        ]);
    }
    
    private function getAllContent() {
        // Get all content in one request
        $data = [];
        
        // Get featured content only for initial load
        $data['featured_projects'] = $this->db->fetchAll("
            SELECT id, title, slug, short_description, image_url, category, technologies, project_url, github_url
            FROM projects 
            WHERE is_published = 1 AND featured = 1
            ORDER BY sort_order ASC
        ");
        
        $data['featured_skills'] = $this->db->fetchAll("
            SELECT name, category, proficiency_level, icon_class
            FROM skills 
            WHERE is_featured = 1
            ORDER BY category, sort_order ASC
        ");
        
        $data['services'] = $this->db->fetchAll("
            SELECT title, slug, short_description, icon_class, features
            FROM services 
            WHERE is_active = 1 AND is_featured = 1
            ORDER BY sort_order ASC
        ");
        
        // Parse JSON fields
        foreach ($data['featured_projects'] as &$project) {
            $project['technologies'] = json_decode($project['technologies'] ?? '[]', true);
        }
        
        foreach ($data['services'] as &$service) {
            $service['features'] = json_decode($service['features'] ?? '[]', true);
        }
        
        return $this->sendResponse(true, 'Content retrieved successfully', 200, $data);
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
            $log = date('Y-m-d H:i:s') . " - PortfolioAPI: " . $message . PHP_EOL;
            @file_put_contents(ERROR_LOG_FILE, $log, FILE_APPEND | LOCK_EX);
        }
    }
}

// Initialize and handle the request
$portfolioAPI = new PortfolioAPI();
$portfolioAPI->handleRequest();