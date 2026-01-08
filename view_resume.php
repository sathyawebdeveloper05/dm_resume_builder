<?php
session_start();
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: view_resumes.php');
    exit;
}

$resume_id = (int)$_GET['id'];

$database = new Database();
$db = $database->getConnection();

// Get resume with user info
$query = "SELECT r.*, u.email, u.full_name, u.phone 
          FROM resumes r 
          JOIN users u ON r.user_id = u.id 
          WHERE r.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $resume_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    header('Location: view_resumes.php');
    exit;
}

$resume = $stmt->fetch(PDO::FETCH_ASSOC);

// Decode JSON fields
$resume['personal_info'] = json_decode($resume['personal_info'], true);
$resume['education'] = json_decode($resume['education'], true);
$resume['experience'] = json_decode($resume['experience'], true);
$resume['projects'] = json_decode($resume['projects'], true);
$resume['skills'] = json_decode($resume['skills'], true);
$resume['certifications'] = json_decode($resume['certifications'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Resume - Admin</title>
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
        
        .resume-header {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .resume-header h2 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .header-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .info-card h3 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .info-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-item strong {
            color: #555;
            display: inline-block;
            min-width: 150px;
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
            padding: 10px 15px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section-content {
            margin-left: 20px;
        }
        
        .section-content p {
            margin: 5px 0;
            line-height: 1.6;
        }
        
        .json-view {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-top: 10px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-file-alt"></i> Resume Details</h1>
        <div class="admin-nav">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="view_resumes.php"><i class="fas fa-file-alt"></i> Resumes</a>
            <a href="view_users.php"><i class="fas fa-users"></i> Users</a>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="dashboard-container">
        <div class="resume-header">
            <h2>Resume #<?php echo $resume['id']; ?> - <?php echo htmlspecialchars($resume['resume_name']); ?></h2>
            <div class="header-grid">
                <div class="info-item">
                    <strong>User:</strong> <?php echo htmlspecialchars($resume['full_name']); ?>
                </div>
                <div class="info-item">
                    <strong>Email:</strong> <?php echo htmlspecialchars($resume['email']); ?>
                </div>
                <div class="info-item">
                    <strong>Template:</strong> <?php echo htmlspecialchars($resume['template_used']); ?>
                </div>
                <div class="info-item">
                    <strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($resume['created_at'])); ?>
                </div>
                <div class="info-item">
                    <strong>Updated:</strong> <?php echo date('M d, Y H:i', strtotime($resume['updated_at'])); ?>
                </div>
                <div class="info-item">
                    <strong>Font:</strong> <?php echo htmlspecialchars($resume['font_family']); ?>
                </div>
                <div class="info-item">
                    <strong>Primary Color:</strong> 
                    <span style="color: <?php echo htmlspecialchars($resume['primary_color']); ?>">
                        <?php echo htmlspecialchars($resume['primary_color']); ?>
                    </span>
                </div>
                <div class="info-item">
                    <strong>Text Align:</strong> <?php echo htmlspecialchars($resume['text_align']); ?>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="view_resumes.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Resumes
                </a>
                <a href="view_user_resumes.php?user_id=<?php echo $resume['user_id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-user"></i> View User's Resumes
                </a>
                <button class="btn btn-danger" onclick="deleteResume(<?php echo $resume['id']; ?>)">
                    <i class="fas fa-trash"></i> Delete Resume
                </button>
            </div>
        </div>
        
        <!-- Personal Information -->
        <?php if ($resume['personal_info']): ?>
        <div class="info-card">
            <h3>Personal Information</h3>
            <div class="section-content">
                <?php foreach ($resume['personal_info'] as $key => $value): ?>
                    <?php if (!empty($value)): ?>
                    <div class="info-item">
                        <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</strong>
                        <?php echo htmlspecialchars($value); ?>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Education -->
        <?php if ($resume['education'] && is_array($resume['education'])): ?>
        <div class="info-card">
            <h3>Education</h3>
            <?php foreach ($resume['education'] as $index => $education): ?>
                <div class="section-content" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                    <h4>Education #<?php echo $index + 1; ?></h4>
                    <?php foreach ($education as $key => $value): ?>
                        <?php if (!empty($value)): ?>
                        <div class="info-item">
                            <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</strong>
                            <?php echo htmlspecialchars($value); ?>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Experience -->
        <?php if ($resume['experience'] && is_array($resume['experience'])): ?>
        <div class="info-card">
            <h3>Work Experience</h3>
            <?php if (isset($resume['experience'][0]['is_fresher'])): ?>
                <div class="section-content">
                    <div class="info-item">
                        <strong>Status:</strong> Fresher (No work experience)
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($resume['experience'] as $index => $experience): ?>
                    <div class="section-content" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                        <h4>Experience #<?php echo $index + 1; ?></h4>
                        <?php foreach ($experience as $key => $value): ?>
                            <?php if (!empty($value)): ?>
                            <div class="info-item">
                                <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</strong>
                                <?php echo htmlspecialchars($value); ?>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Projects -->
        <?php if ($resume['projects'] && is_array($resume['projects'])): ?>
        <div class="info-card">
            <h3>Projects</h3>
            <?php foreach ($resume['projects'] as $index => $project): ?>
                <div class="section-content" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                    <h4>Project #<?php echo $index + 1; ?></h4>
                    <?php foreach ($project as $key => $value): ?>
                        <?php if (!empty($value)): ?>
                        <div class="info-item">
                            <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</strong>
                            <?php echo htmlspecialchars($value); ?>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Skills -->
        <?php if ($resume['skills']): ?>
        <div class="info-card">
            <h3>Skills</h3>
            <div class="section-content">
                <?php if (!empty($resume['skills']['technical'])): ?>
                <div class="info-item">
                    <strong>Technical Skills:</strong>
                    <?php echo htmlspecialchars($resume['skills']['technical']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($resume['skills']['soft'])): ?>
                <div class="info-item">
                    <strong>Soft Skills:</strong>
                    <?php echo htmlspecialchars($resume['skills']['soft']); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Certifications -->
        <?php if ($resume['certifications'] && is_array($resume['certifications'])): ?>
        <div class="info-card">
            <h3>Certifications</h3>
            <?php foreach ($resume['certifications'] as $index => $certification): ?>
                <div class="section-content" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                    <h4>Certification #<?php echo $index + 1; ?></h4>
                    <?php foreach ($certification as $key => $value): ?>
                        <?php if (!empty($value)): ?>
                        <div class="info-item">
                            <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</strong>
                            <?php echo htmlspecialchars($value); ?>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Raw JSON View -->
        <div class="info-card">
            <h3>Raw JSON Data</h3>
            <div class="json-view">
                <?php 
                $full_resume_data = [
                    'id' => $resume['id'],
                    'resume_name' => $resume['resume_name'],
                    'user' => [
                        'full_name' => $resume['full_name'],
                        'email' => $resume['email'],
                        'phone' => $resume['phone']
                    ],
                    'personal_info' => $resume['personal_info'],
                    'education' => $resume['education'],
                    'experience' => $resume['experience'],
                    'projects' => $resume['projects'],
                    'skills' => $resume['skills'],
                    'certifications' => $resume['certifications'],
                    'template_used' => $resume['template_used'],
                    'font_family' => $resume['font_family'],
                    'primary_color' => $resume['primary_color'],
                    'text_align' => $resume['text_align'],
                    'created_at' => $resume['created_at'],
                    'updated_at' => $resume['updated_at']
                ];
                echo json_encode($full_resume_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                ?>
            </div>
        </div>
        
        <div class="back-link">
            <a href="view_resumes.php"><i class="fas fa-arrow-left"></i> Back to All Resumes</a>
        </div>
    </div>
    
    <script>
    function deleteResume(resumeId) {
        if (confirm('Are you sure you want to delete this resume? This action cannot be undone.')) {
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
                    window.location.href = 'view_resumes.php';
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
    </script>
</body>
</html>