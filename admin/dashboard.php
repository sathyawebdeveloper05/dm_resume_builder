<?php
session_start();
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total resumes
$query = "SELECT COUNT(*) as total FROM resumes";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_resumes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total users
$query = "SELECT COUNT(*) as total FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent resumes (last 7 days)
$query = "SELECT COUNT(*) as total FROM resumes WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['recent_resumes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Most used template
$query = "SELECT template_used, COUNT(*) as count FROM resumes GROUP BY template_used ORDER BY count DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$popular_template = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['popular_template'] = $popular_template['template_used'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dragon Media Resume Builder</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f7fa;
            color: #333;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-header h1 {
            font-size: 24px;
        }
        
        .admin-nav {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        .admin-nav a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card i {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .quick-actions {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .quick-actions h2 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }
        
        .action-btn:hover {
            background: #764ba2;
        }
        
        .recent-activity {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .recent-activity h2 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .btn-view {
            background: #3498db;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-user-shield"></i> Admin Dashboard</h1>
        <div class="admin-nav">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="view_resumes.php"><i class="fas fa-file-alt"></i> Resumes</a>
            <a href="view_users.php"><i class="fas fa-users"></i> Users</a>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="dashboard-container">
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-file-alt"></i>
                <div class="stat-number"><?php echo $stats['total_resumes']; ?></div>
                <div class="stat-label">Total Resumes</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <div class="stat-number"><?php echo $stats['recent_resumes']; ?></div>
                <div class="stat-label">Resumes (Last 7 Days)</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-palette"></i>
                <div class="stat-number"><?php echo $stats['popular_template']; ?></div>
                <div class="stat-label">Most Popular Template</div>
            </div>
        </div>
        
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="view_resumes.php" class="action-btn">
                    <i class="fas fa-eye"></i> View All Resumes
                </a>
                <a href="view_users.php" class="action-btn">
                    <i class="fas fa-user-friends"></i> Manage Users
                </a>
                <a href="../../frontend/index.html" class="action-btn" target="_blank">
                    <i class="fas fa-plus"></i> Create New Resume
                </a>
                <a href="#" class="action-btn" onclick="exportData()">
                    <i class="fas fa-download"></i> Export Data
                </a>
            </div>
        </div>
        
        <div class="recent-activity">
            <h2>Recent Resumes</h2>
            <?php
            $query = "SELECT r.*, u.email, u.full_name 
                     FROM resumes r 
                     JOIN users u ON r.user_id = u.id 
                     ORDER BY r.created_at DESC 
                     LIMIT 10";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $recent_resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($recent_resumes) > 0):
            ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resume Name</th>
                        <th>User</th>
                        <th>Template</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_resumes as $resume): ?>
                    <tr>
                        <td>#<?php echo $resume['id']; ?></td>
                        <td><?php echo htmlspecialchars($resume['resume_name']); ?></td>
                        <td><?php echo htmlspecialchars($resume['full_name'] . ' (' . $resume['email'] . ')'); ?></td>
                        <td><?php echo htmlspecialchars($resume['template_used']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($resume['created_at'])); ?></td>
                        <td>
                            <a href="view_resume.php?id=<?php echo $resume['id']; ?>" class="btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <button class="btn-delete" onclick="deleteResume(<?php echo $resume['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No resumes found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function deleteResume(id) {
        if (confirm('Are you sure you want to delete this resume?')) {
            fetch('delete_resume.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Resume deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
    
    function exportData() {
        alert('Export functionality will be implemented soon!');
    }
    </script>
</body>
</html>