<?php
// START OUTPUT BUFFERING - THIS IS CRITICAL
ob_start();

// Disable error display to prevent HTML output
ini_set('display_errors', 0);
error_reporting(0);

session_start();

// Handle actions via POST (delete, view details)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any output buffer for clean JSON response
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    
    // Include files AFTER setting headers to prevent output
    try {
        require_once '../includes/auth.php';
        require_once '../config/database.php';
        
        // Check if admin is logged in
        requireAdmin();
        
        $database = new Database();
        $db = $database->getConnection();
        
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
            // Delete resume
            $id = (int)$_POST['id'];
            
            // Check if resume exists
            $checkQuery = "SELECT id FROM resumes WHERE id = :id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Resume not found']);
                exit;
            }
            
            // Delete the resume
            $deleteQuery = "DELETE FROM resumes WHERE id = :id";
            $deleteStmt = $db->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $deleteResult = $deleteStmt->execute();
            
            if ($deleteResult) {
                echo json_encode(['success' => true, 'message' => 'Resume deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete resume']);
            }
            exit;
            
        } elseif (isset($_POST['action']) && $_POST['action'] === 'get_details' && isset($_POST['id'])) {
            // Get resume details
            $id = (int)$_POST['id'];
            
            // Get resume details
            $query = "SELECT r.*, u.email, u.full_name, u.phone 
                      FROM resumes r 
                      JOIN users u ON r.user_id = u.id 
                      WHERE r.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $resume = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resume) {
                echo json_encode(['success' => false, 'message' => 'Resume not found']);
                exit;
            }
            
            // Prepare user data
            $userData = [
                'full_name' => $resume['full_name'] ?? '',
                'email' => $resume['email'] ?? '',
                'phone' => $resume['phone'] ?? ''
            ];
            
            // Remove user columns from resume data
            unset($resume['full_name'], $resume['email'], $resume['phone']);
            
            echo json_encode([
                'success' => true,
                'resume' => $resume,
                'user' => $userData
            ]);
            exit;
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        exit;
    }
}

// Normal page load (GET request)
// Clear output buffer for HTML
ob_clean();

// Include files for normal page load
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Pagination for GET requests (normal page load)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total count
$query = "SELECT COUNT(*) as total FROM resumes";
$stmt = $db->prepare($query);
$stmt->execute();
$total_resumes = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$total_pages = ceil($total_resumes / $limit);

// Get resumes with pagination
$query = "SELECT r.*, u.email, u.full_name, u.phone 
          FROM resumes r 
          JOIN users u ON r.user_id = u.id 
          ORDER BY r.created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Continue with HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Resumes - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .admin-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .admin-header h1 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-nav {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .admin-nav a {
            text-decoration: none;
            color: #555;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover {
            background: #f0f0f0;
        }
        
        .admin-nav a.active {
            background: #667eea;
            color: white;
        }
        
        .admin-nav span {
            margin-left: auto;
            color: #666;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white !important;
        }
        
        .logout-btn:hover {
            background: #c82333 !important;
        }
        
        .dashboard-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .filter-bar {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-bar input, .filter-bar select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 150px;
        }
        
        .action-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .action-btn:hover {
            background: #5a67d8;
        }
        
        .recent-activity {
            background: white;
        }
        
        .recent-activity h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .btn-view, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .btn-view {
            background: #28a745;
            color: white;
        }
        
        .btn-view:hover {
            background: #218838;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        
        .pagination a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .json-preview {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        @media (max-width: 768px) {
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .admin-nav {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .admin-nav span {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-file-alt"></i> Manage Resumes</h1>
        <div class="admin-nav">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="view_resumes.php" class="active"><i class="fas fa-file-alt"></i> Resumes</a>
            <a href="view_users.php"><i class="fas fa-users"></i> Users</a>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="dashboard-container">
        <div class="filter-bar">
            <input type="text" id="searchInput" placeholder="Search resumes..." onkeyup="searchResumes()">
            <select id="templateFilter" onchange="filterByTemplate()">
                <option value="">All Templates</option>
                <option value="template-1">Template 1</option>
                <option value="template-2">Template 2</option>
                <option value="template-3">Template 3</option>
                <option value="template-4">Template 4</option>
            </select>
            <input type="date" id="dateFrom" placeholder="From Date">
            <input type="date" id="dateTo" placeholder="To Date">
            <button onclick="resetFilters()" class="action-btn">Reset</button>
        </div>
        
        <div class="recent-activity">
            <h2>All Resumes (<?php echo $total_resumes; ?>)</h2>
            <table id="resumesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resume Name</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Template</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resumes)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px;">
                            No resumes found.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($resumes as $resume): ?>
                    <tr>
                        <td>#<?php echo $resume['id']; ?></td>
                        <td><?php echo htmlspecialchars($resume['resume_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($resume['full_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($resume['email'] ?? ''); ?></td>
                        <td>
                            <span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px;">
                                <?php echo htmlspecialchars($resume['template_used'] ?? ''); ?>
                            </span>
                        </td>
                        <td><?php echo isset($resume['created_at']) ? date('M d, Y', strtotime($resume['created_at'])) : 'N/A'; ?></td>
                        <td>
                            <button class="btn-view" onclick="viewResumeDetails(<?php echo $resume['id']; ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn-delete" onclick="deleteResume(<?php echo $resume['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal for viewing resume details -->
    <div id="resumeModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>
    
    <script>
    function viewResumeDetails(id) {
        // Show loading
        document.getElementById('modalContent').innerHTML = '<p style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading resume details...</p>';
        document.getElementById('resumeModal').style.display = 'block';
        
        // Fetch resume details with error handling
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_details&id=' + id
        })
        .then(response => {
            // First, check if the response is OK
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            
            // Get response as text first
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text); // Debug log
            
            try {
                // Try to parse JSON
                const data = JSON.parse(text);
                
                if (data.success) {
                    // Display resume details
                    let content = `
                        <h2>Resume Details - #${data.resume.id || 'N/A'}</h2>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                            <div>
                                <h3><i class="fas fa-info-circle"></i> Basic Info</h3>
                                <p><strong>Resume Name:</strong> ${data.resume.resume_name || 'N/A'}</p>
                                <p><strong>User:</strong> ${data.user.full_name || 'N/A'}</p>
                                <p><strong>Email:</strong> ${data.user.email || 'N/A'}</p>
                                <p><strong>Phone:</strong> ${data.user.phone || 'N/A'}</p>
                                <p><strong>Template:</strong> 
                                    <span style="background: #e9ecef; padding: 2px 8px; border-radius: 4px;">
                                        ${data.resume.template_used || 'N/A'}
                                    </span>
                                </p>
                                <p><strong>Created:</strong> ${data.resume.created_at ? new Date(data.resume.created_at).toLocaleString() : 'N/A'}</p>
                            </div>
                            <div>
                                <h3><i class="fas fa-paint-brush"></i> Style Settings</h3>
                                <p><strong>Font:</strong> ${data.resume.font_family || 'Default'}</p>
                                <p><strong>Primary Color:</strong> 
                                    <span style="color: ${data.resume.primary_color || '#000'}">
                                        ${data.resume.primary_color || 'Default'}
                                    </span>
                                </p>
                                <p><strong>Text Alignment:</strong> ${data.resume.text_align || 'Left'}</p>
                            </div>
                        </div>
                    `;
                    
                    // Add personal info
                    if (data.resume.personal_info) {
                        try {
                            const personal = JSON.parse(data.resume.personal_info);
                            content += `
                                <h3 style="margin-top: 20px;"><i class="fas fa-user"></i> Personal Information</h3>
                                <div class="json-preview">${JSON.stringify(personal, null, 2)}</div>
                            `;
                        } catch (e) {
                            console.error('Error parsing personal info:', e);
                            content += `
                                <h3 style="margin-top: 20px;"><i class="fas fa-user"></i> Personal Information</h3>
                                <div class="json-preview">${data.resume.personal_info}</div>
                            `;
                        }
                    }
                    
                    // Add education
                    if (data.resume.education) {
                        try {
                            const education = JSON.parse(data.resume.education);
                            content += `
                                <h3 style="margin-top: 20px;"><i class="fas fa-graduation-cap"></i> Education</h3>
                                <div class="json-preview">${JSON.stringify(education, null, 2)}</div>
                            `;
                        } catch (e) {
                            console.error('Error parsing education:', e);
                            content += `
                                <h3 style="margin-top: 20px;"><i class="fas fa-graduation-cap"></i> Education</h3>
                                <div class="json-preview">${data.resume.education}</div>
                            `;
                        }
                    }
                    
                    // Add experience if exists
                    if (data.resume.experience) {
                        try {
                            const experience = JSON.parse(data.resume.experience);
                            content += `
                                <h3 style="margin-top: 20px;"><i class="fas fa-briefcase"></i> Experience</h3>
                                <div class="json-preview">${JSON.stringify(experience, null, 2)}</div>
                            `;
                        } catch (e) {
                            console.error('Error parsing experience:', e);
                            content += `
                                <h3 style="margin-top: 20px;"><i class="fas fa-briefcase"></i> Experience</h3>
                                <div class="json-preview">${data.resume.experience}</div>
                            `;
                        }
                    }
                    
                    // Add skills if exists
                    if (data.resume.skills) {
                        try {
                            const skills = JSON.parse(data.resume.skills);
                            content += `
                                <h3 style="margin-top: 20px;"><i class="fas fa-star"></i> Skills</h3>
                                <div class="json-preview">${JSON.stringify(skills, null, 2)}</div>
                            `;
                        } catch (e) {
                            console.error('Error parsing skills:', e);
                            content += `
                                <h3 style="margin-top: 20px;"><i class="fas fa-star"></i> Skills</h3>
                                <div class="json-preview">${data.resume.skills}</div>
                            `;
                        }
                    }
                    
                    document.getElementById('modalContent').innerHTML = content;
                } else {
                    document.getElementById('modalContent').innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 48px; margin-bottom: 20px;"></i>
                            <h3>Error</h3>
                            <p>${data.message || 'Failed to load resume details'}</p>
                            <button onclick="closeModal()" class="action-btn" style="margin-top: 20px;">Close</button>
                        </div>
                    `;
                }
            } catch (e) {
                console.error('JSON Parse Error:', e);
                console.error('Response text:', text);
                
                document.getElementById('modalContent').innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 48px; margin-bottom: 20px;"></i>
                        <h3>JSON Parse Error</h3>
                        <p>Server returned invalid JSON. This usually means there's a PHP error.</p>
                        <p><small>${text.substring(0, 200)}...</small></p>
                        <button onclick="closeModal()" class="action-btn" style="margin-top: 20px;">Close</button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            document.getElementById('modalContent').innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Network Error</h3>
                    <p>${error.message}</p>
                    <button onclick="closeModal()" class="action-btn" style="margin-top: 20px;">Close</button>
                </div>
            `;
        });
    }
    
    function deleteResume(id) {
        if (confirm('Are you sure you want to delete this resume? This action cannot be undone.')) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=delete&id=' + id
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert('Resume deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Response:', text);
                    alert('Server returned invalid response. Check console for details.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete resume. Please try again.');
            });
        }
    }
    
    function closeModal() {
        document.getElementById('resumeModal').style.display = 'none';
    }
    
    function searchResumes() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const table = document.getElementById('resumesTable');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell.textContent.toLowerCase().includes(input)) {
                    found = true;
                    break;
                }
            }
            
            row.style.display = found ? '' : 'none';
        }
    }
    
    function filterByTemplate() {
        const filter = document.getElementById('templateFilter').value;
        const table = document.getElementById('resumesTable');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            
            if (filter === '') {
                row.style.display = '';
            } else {
                const templateCell = cells[4];
                const template = templateCell.textContent.trim().toLowerCase();
                row.style.display = template.includes(filter) ? '' : 'none';
            }
        }
    }
    
    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('templateFilter').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        
        const table = document.getElementById('resumesTable');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            rows[i].style.display = '';
        }
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('resumeModal');
        if (event.target == modal) {
            closeModal();
        }
    }
    </script>
</body>
</html>