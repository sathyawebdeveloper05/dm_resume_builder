<?php
session_start();
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/database.php';

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    header('Location: view_users.php');
    exit;
}

$user_id = (int)$_GET['user_id'];

$database = new Database();
$db = $database->getConnection();

// Get user info
$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: view_users.php');
    exit;
}

// Get user's resumes
$query = "SELECT * FROM resumes WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Resumes - Admin</title>
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
        
        .user-info-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .user-info-card h2 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .user-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .detail-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .detail-item strong {
            color: #555;
            display: block;
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 15px;
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
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            margin-right: 5px;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .back-link {
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-user"></i> User Resumes</h1>
        <div class="admin-nav">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="view_resumes.php"><i class="fas fa-file-alt"></i> Resumes</a>
            <a href="view_users.php"><i class="fas fa-users"></i> Users</a>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="dashboard-container">
        <div class="user-info-card">
            <h2>User Information</h2>
            <div class="user-details">
                <div class="detail-item">
                    <strong>Full Name</strong>
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </div>
                <div class="detail-item">
                    <strong>Email</strong>
                    <?php echo htmlspecialchars($user['email']); ?>
                </div>
                <div class="detail-item">
                    <strong>Phone</strong>
                    <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?>
                </div>
                <div class="detail-item">
                    <strong>Joined</strong>
                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                </div>
                <div class="detail-item">
                    <strong>Last Login</strong>
                    <?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?>
                </div>
                <div class="detail-item">
                    <strong>Total Resumes</strong>
                    <?php echo count($resumes); ?>
                </div>
            </div>
        </div>
        
        <h2 style="margin-bottom: 15px;">Resumes Created</h2>
        
        <?php if (count($resumes) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Resume Name</th>
                    <th>Template</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resumes as $resume): ?>
                <tr>
                    <td>#<?php echo $resume['id']; ?></td>
                    <td><?php echo htmlspecialchars($resume['resume_name']); ?></td>
                    <td><?php echo htmlspecialchars($resume['template_used']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($resume['created_at'])); ?></td>
                    <td><?php echo date('M d, Y', strtotime($resume['updated_at'])); ?></td>
                    <td>
                        <button class="btn-view" onclick="viewResume(<?php echo $resume['id']; ?>)">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn-delete" onclick="deleteResume(<?php echo $resume['id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <h3>No Resumes Found</h3>
            <p>This user hasn't created any resumes yet.</p>
        </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="view_users.php"><i class="fas fa-arrow-left"></i> Back to Users</a>
        </div>
    </div>
    
    <script>
    function viewResume(resumeId) {
        window.location.href = 'view_resume.php?id=' + resumeId;
    }
    
    function deleteResume(resumeId) {
        if (confirm('Are you sure you want to delete this resume?')) {
            fetch('delete_resume.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + resumeId
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
    </script>
</body>
</html>