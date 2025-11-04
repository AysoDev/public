<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$announcements = getAnnouncements();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sparrow Intranet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #1a252f;
            --success: #2ecc71;
            --warning: #f39c12;
        }

        body {
            background: #f8f9fa;
            color: var(--dark);
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            font-size: 28px;
            color: var(--secondary);
        }

        .logo h1 {
            font-size: 24px;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline {
            border: 2px solid var(--secondary);
            color: var(--secondary);
            background: transparent;
        }

        .btn-outline:hover {
            background: var(--secondary);
            color: white;
        }

        /* Navigation */
        .nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            gap: 10px;
            overflow-x: auto;
        }

        .nav a {
            color: var(--primary);
            text-decoration: none;
            padding: 15px 20px;
            white-space: nowrap;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }

        .nav a:hover, .nav a.active {
            color: var(--secondary);
            border-bottom-color: var(--secondary);
            background: rgba(52, 152, 219, 0.1);
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--secondary) 0%, #2980b9 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.3);
        }

        .welcome-banner h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .stat-icon i {
            font-size: 24px;
            color: white;
        }

        .stat-card h3 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .card-header h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 25px;
        }

        .announcement-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }

        .announcement-item:hover {
            background: #f8f9fa;
        }

        .announcement-item:last-child {
            border-bottom: none;
        }

        .announcement-item h4 {
            color: var(--primary);
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .announcement-meta {
            color: #666;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            background: white;
            border: 2px solid var(--light);
            padding: 20px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--primary);
            text-align: center;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .action-btn:hover {
            border-color: var(--secondary);
            color: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .action-btn i {
            font-size: 24px;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .nav-content {
                flex-wrap: wrap;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-dove"></i>
                <h1>Sparrow Intranet</h1>
            </div>
            <div class="user-info">
                <div class="user-badge">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                    <?php if ($auth->isAdmin()): ?>
                        <span style="background: var(--success); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">Admin</span>
                    <?php endif; ?>
                </div>
                <a href="?logout" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="nav">
        <div class="nav-content">
            <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="employees.php"><i class="fas fa-users"></i> Employee Directory</a>
            <a href="documents/"><i class="fas fa-folder"></i> Documents</a>
            <a href="search.php"><i class="fas fa-search"></i> Search</a>
            <a href="tickets.php"><i class="fas fa-ticket-alt"></i> IT Tickets</a>
            <?php if ($auth->isAdmin()): ?>
                <a href="admin/"><i class="fas fa-cog"></i> Admin Panel</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h2>
            <p>Here's what's happening with Sparrow Consulting today.</p>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--success);">
                    <i class="fas fa-users"></i>
                </div>
                <h3>47</h3>
                <p>Total Employees</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--warning);">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3>128</h3>
                <p>Documents</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--accent);">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3>5</h3>
                <p>Open Tickets</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--secondary);">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h3>12</h3>
                <p>Announcements</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <!-- Left Column -->
            <div>
                <!-- Recent Announcements -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bullhorn"></i> Recent Announcements</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-item">
                                <h4><?php echo htmlspecialchars($announcement['title']); ?></h4>
                                <p><?php echo htmlspecialchars($announcement['content']); ?></p>
                                <div class="announcement-meta">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($announcement['username']); ?> 
                                    • <i class="fas fa-clock"></i> <?php echo date('M j, Y', strtotime($announcement['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="search.php" class="action-btn">
                                <i class="fas fa-search"></i>
                                <span>Search</span>
                            </a>
                            <a href="documents/" class="action-btn">
                                <i class="fas fa-upload"></i>
                                <span>Upload File</span>
                            </a>
                            <a href="tickets.php" class="action-btn">
                                <i class="fas fa-plus"></i>
                                <span>New Ticket</span>
                            </a>
                            <a href="employees.php" class="action-btn">
                                <i class="fas fa-address-book"></i>
                                <span>Directory</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-server"></i> System Status</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee;">
                            <span>Web Server</span>
                            <span style="color: var(--success);"><i class="fas fa-circle"></i> Online</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee;">
                            <span>Database</span>
                            <span style="color: var(--success);"><i class="fas fa-circle"></i> Online</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0;">
                            <span>File Storage</span>
                            <span style="color: var(--success);"><i class="fas fa-circle"></i> Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_GET['logout'])) {
        $auth->logout();
    }
    ?>
</body>
</html>