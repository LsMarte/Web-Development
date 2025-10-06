<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Admin - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .admin-header {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .admin-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .admin-nav {
            background: #34495e;
            padding: 0 2rem;
        }

        .nav-list {
            list-style: none;
            display: flex;
            gap: 0;
        }

        .nav-item {
            border-right: 1px solid #3a546b;
        }

        .nav-link {
            display: block;
            padding: 1rem 1.5rem;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #3a546b;
        }

        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .dashboard-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #3498db;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .card-description {
            color: #7f8c8d;
            margin-bottom: 1.5rem;
        }

        .card-button {
            background: #3498db;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
            cursor: pointer;
        }

        .card-button:hover {
            background: #2980b9;
        }

        .stats-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1.5rem;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            display: block;
        }

        .stat-label {
            color: #7f8c8d;
            margin-top: 0.5rem;
        }

        .recent-messages {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #2c3e50;
        }

        .message-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        .message-content h4 {
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .message-content p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .message-meta {
            font-size: 0.8rem;
            color: #95a5a6;
            text-align: right;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-new {
            background: #e74c3c;
            color: white;
        }

        .status-read {
            background: #95a5a6;
            color: white;
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 0 1rem;
            }

            .nav-list {
                flex-direction: column;
            }

            .nav-item {
                border-right: none;
                border-bottom: 1px solid #3a546b;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><i class="fas fa-tachometer-alt"></i> Portfolio Admin Dashboard</h1>
    </header>

    <nav class="admin-nav">
        <ul class="nav-list">
            <li class="nav-item"><a href="#" class="nav-link active">Dashboard</a></li>
            <li class="nav-item"><a href="messages.php" class="nav-link">Messages</a></li>
            <li class="nav-item"><a href="projects.php" class="nav-link">Projects</a></li>
            <li class="nav-item"><a href="skills.php" class="nav-link">Skills</a></li>
            <li class="nav-item"><a href="services.php" class="nav-link">Services</a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link">Settings</a></li>
        </ul>
    </nav>

    <div class="admin-container">
        <!-- Stats Overview -->
        <div class="stats-section">
            <h2 class="section-title">Overview Statistics</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number" id="totalMessages">-</span>
                    <span class="stat-label">Total Messages</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="totalProjects">-</span>
                    <span class="stat-label">Projects</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="totalSkills">-</span>
                    <span class="stat-label">Skills</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="totalServices">-</span>
                    <span class="stat-label">Services</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="card-title">Messages</h3>
                <p class="card-description">View and manage contact form messages from visitors.</p>
                <a href="messages.php" class="card-button">View Messages</a>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h3 class="card-title">Projects</h3>
                <p class="card-description">Add, edit, and manage your portfolio projects.</p>
                <a href="projects.php" class="card-button">Manage Projects</a>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-code"></i>
                </div>
                <h3 class="card-title">Skills</h3>
                <p class="card-description">Update your technical skills and proficiency levels.</p>
                <a href="skills.php" class="card-button">Edit Skills</a>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3 class="card-title">Services</h3>
                <p class="card-description">Manage the services you offer to clients.</p>
                <a href="services.php" class="card-button">Update Services</a>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="recent-messages">
            <h2 class="section-title">Recent Messages</h2>
            <div id="recentMessages">
                <p>Loading recent messages...</p>
            </div>
        </div>
    </div>

    <script>
        // Load dashboard data
        async function loadDashboardData() {
            try {
                // Load statistics
                const statsResponse = await fetch('../api/portfolio.php?endpoint=stats');
                if (statsResponse.ok) {
                    const statsData = await statsResponse.json();
                    if (statsData.success) {
                        const stats = statsData.data;
                        document.getElementById('totalProjects').textContent = stats.projects || 0;
                        document.getElementById('totalSkills').textContent = stats.skills || 0;
                        document.getElementById('totalServices').textContent = stats.services || 0;
                    }
                }

                // Load recent messages count (you would implement this endpoint)
                const messagesResponse = await fetch('../api/contact.php?action=count');
                if (messagesResponse.ok) {
                    const messagesData = await messagesResponse.json();
                    if (messagesData.success) {
                        document.getElementById('totalMessages').textContent = messagesData.count || 0;
                    }
                }

                // Load recent messages preview
                loadRecentMessages();

            } catch (error) {
                console.error('Failed to load dashboard data:', error);
                // Set fallback values
                document.getElementById('totalMessages').textContent = '0';
                document.getElementById('totalProjects').textContent = '0';
                document.getElementById('totalSkills').textContent = '0';
                document.getElementById('totalServices').textContent = '0';
            }
        }

        async function loadRecentMessages() {
            try {
                const response = await fetch('../api/contact.php?action=recent&limit=5');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.messages) {
                        const messagesHTML = data.messages.map(message => `
                            <div class="message-item">
                                <div class="message-content">
                                    <h4>${message.name}</h4>
                                    <p>${message.subject}</p>
                                </div>
                                <div class="message-meta">
                                    <div class="status-badge ${message.is_read ? 'status-read' : 'status-new'}">
                                        ${message.is_read ? 'Read' : 'New'}
                                    </div>
                                    <div>${new Date(message.created_at).toLocaleDateString()}</div>
                                </div>
                            </div>
                        `).join('');
                        
                        document.getElementById('recentMessages').innerHTML = messagesHTML;
                    } else {
                        document.getElementById('recentMessages').innerHTML = '<p>No messages yet.</p>';
                    }
                } else {
                    throw new Error('Failed to load messages');
                }
            } catch (error) {
                console.error('Failed to load recent messages:', error);
                document.getElementById('recentMessages').innerHTML = '<p>Unable to load messages. <a href="messages.php">View all messages</a></p>';
            }
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', loadDashboardData);

        // Refresh data every 30 seconds
        setInterval(loadDashboardData, 30000);
    </script>
</body>
</html>
