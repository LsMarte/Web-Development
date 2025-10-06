<?php
/**
 * Simple Admin Interface for Contact Messages
 * Luis Marte Portfolio Backend
 */

session_start();

require_once '../config/database.php';
require_once '../config/config.php';

// Simple authentication (enhance this for production)
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple hardcoded check (use database in production)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $login_error = 'Invalid credentials';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Handle message actions
if ($admin_logged_in && isset($_POST['action'])) {
    $db = Database::getInstance();
    $messageId = $_POST['message_id'] ?? 0;
    
    switch ($_POST['action']) {
        case 'mark_read':
            $db->update("UPDATE contact_messages SET status = 'read' WHERE id = ?", [$messageId]);
            break;
        case 'mark_replied':
            $db->update("UPDATE contact_messages SET status = 'replied', replied_at = NOW() WHERE id = ?", [$messageId]);
            break;
        case 'archive':
            $db->update("UPDATE contact_messages SET status = 'archived' WHERE id = ?", [$messageId]);
            break;
        case 'delete':
            $db->delete("DELETE FROM contact_messages WHERE id = ?", [$messageId]);
            break;
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Messages</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .admin-header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .messages-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .messages-header {
            background: #667eea;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .message-item {
            border-bottom: 1px solid #eee;
            padding: 20px;
            transition: background 0.3s;
        }
        
        .message-item:hover {
            background: #f8f9fa;
        }
        
        .message-item.new {
            background: #e8f4fd;
            border-left: 4px solid #667eea;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .message-info {
            flex: 1;
        }
        
        .message-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .message-email {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .message-date {
            color: #999;
            font-size: 0.8rem;
        }
        
        .message-status {
            background: #6c757d;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        
        .message-status.new { background: #007bff; }
        .message-status.read { background: #28a745; }
        .message-status.replied { background: #17a2b8; }
        .message-status.archived { background: #6c757d; }
        
        .message-subject {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .message-content {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
            max-height: 100px;
            overflow: hidden;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        
        .btn:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        .error {
            color: #dc3545;
            margin-bottom: 15px;
        }
        
        .no-messages {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .filter-tabs {
            display: flex;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 5px;
            margin-bottom: 20px;
        }
        
        .filter-tab {
            flex: 1;
            background: none;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .filter-tab.active {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php if (!$admin_logged_in): ?>
        <!-- Login Form -->
        <div class="login-container">
            <h2 style="text-align: center; margin-bottom: 30px; color: #333;">Admin Login</h2>
            
            <?php if (isset($login_error)): ?>
                <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary" style="width: 100%; padding: 12px;">Login</button>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 5px; font-size: 0.9rem;">
                <strong>Default Credentials:</strong><br>
                Username: admin<br>
                Password: admin123<br>
                <small style="color: #666;">Please change these in production!</small>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Dashboard -->
        <div class="container">
            <div class="admin-header">
                <h1>Contact Messages Dashboard</h1>
                <div>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="?logout=1" class="btn btn-secondary" style="margin-left: 10px;">Logout</a>
                </div>
            </div>
            
            <?php
            $db = Database::getInstance();
            
            // Get statistics
            $stats = $db->fetchOne("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                    SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
                    SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count,
                    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_count
                FROM contact_messages
            ");
            
            // Get filter
            $filter = $_GET['filter'] ?? 'all';
            $whereClause = '';
            $params = [];
            
            if ($filter !== 'all') {
                $whereClause = ' WHERE status = ?';
                $params[] = $filter;
            }
            
            // Get messages
            $messages = $db->fetchAll("
                SELECT * FROM contact_messages 
                $whereClause
                ORDER BY created_at DESC 
                LIMIT 50
            ", $params);
            ?>
            
            <!-- Statistics -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div>Total Messages</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['new_count']; ?></div>
                    <div>New Messages</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['replied_count']; ?></div>
                    <div>Replied</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['week_count']; ?></div>
                    <div>This Week</div>
                </div>
            </div>
            
            <!-- Messages -->
            <div class="messages-container">
                <div class="messages-header">
                    <h2>Contact Messages</h2>
                    <div>
                        <select onchange="location = this.value" style="padding: 8px; border-radius: 4px; border: none;">
                            <option value="?filter=all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Messages</option>
                            <option value="?filter=new" <?php echo $filter === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="?filter=read" <?php echo $filter === 'read' ? 'selected' : ''; ?>>Read</option>
                            <option value="?filter=replied" <?php echo $filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                            <option value="?filter=archived" <?php echo $filter === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                </div>
                
                <?php if (empty($messages)): ?>
                    <div class="no-messages">
                        <h3>No messages found</h3>
                        <p>There are no contact messages to display.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message-item <?php echo $message['status']; ?>">
                            <div class="message-header">
                                <div class="message-info">
                                    <div class="message-name"><?php echo htmlspecialchars($message['name']); ?></div>
                                    <div class="message-email">
                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" style="color: #667eea; text-decoration: none;">
                                            <?php echo htmlspecialchars($message['email']); ?>
                                        </a>
                                    </div>
                                    <div class="message-date"><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></div>
                                </div>
                                <span class="message-status <?php echo $message['status']; ?>"><?php echo $message['status']; ?></span>
                            </div>
                            
                            <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                            <div class="message-content"><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                            
                            <div class="message-actions">
                                <?php if ($message['status'] === 'new'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <input type="hidden" name="action" value="mark_read">
                                        <button type="submit" class="btn btn-primary">Mark as Read</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($message['status'] !== 'replied'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <input type="hidden" name="action" value="mark_replied">
                                        <button type="submit" class="btn btn-success">Mark as Replied</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($message['status'] !== 'archived'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <input type="hidden" name="action" value="archive">
                                        <button type="submit" class="btn btn-secondary">Archive</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                                
                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" 
                                   class="btn btn-info">Reply</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>