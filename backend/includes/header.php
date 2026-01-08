<?php
// Common header for admin pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Dragon Media Resume Builder Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background: var(--secondary-color);
            color: white;
            position: fixed;
            width: 250px;
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--primary-color);
        }
        
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left-color: var(--primary-color);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .stat-label {
            color: #666;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        
        .btn-custom {
            padding: 8px 20px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-custom-primary {
            background: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-custom-primary:hover {
            background: #2980b9;
            color: white;
        }
        
        .btn-custom-danger {
            background: var(--accent-color);
            color: white;
            border: none;
        }
        
        .btn-custom-danger:hover {
            background: #c0392b;
            color: white;
        }
        
        .alert-custom {
            border: none;
            border-radius: 5px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .table-custom {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .table-custom th {
            border-top: none;
            font-weight: 600;
            color: var(--dark-color);
            background: #f8f9fa;
        }
        
        .table-custom td {
            vertical-align: middle;
        }
        
        .badge-custom {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .form-control-custom {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        
        .form-control-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .pagination-custom .page-link {
            color: var(--primary-color);
            border: 1px solid #dee2e6;
        }
        
        .pagination-custom .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-custom alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>