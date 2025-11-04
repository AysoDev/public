<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$results = [];
if ($_GET['q']) {
    $query = $_GET['q'];
    $results = searchDocuments($query);
    logAction("Search performed: $query");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Sparrow Intranet</title>
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
        }

        body {
            background: #f8f9fa;
            color: var(--dark);
        }

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

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .search-hero {
            background: linear-gradient(135deg, var(--secondary) 0%, #2980b9 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.3);
        }

        .search-hero h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .search-box {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 20px 60px 20px 30px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--secondary);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-btn:hover {
            background: #2980b9;
            transform: translateY(-50%) scale(1.1);
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

        .results-grid {
            display: grid;
            gap: 20px;
        }

        .result-item {
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 10px;
            transition: all 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .result-item:hover {
            border-color: var(--secondary);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .file-info h4 {
            color: var(--primary);
            margin-bottom: 5px;
        }

        .file-meta {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            gap: 15px;
        }

        .file-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn.view {
            background: var(--secondary);
            color: white;
        }

        .action-btn.download {
            background: var(--success);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .no-results i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }

        .search-stats {
            color: #666;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .search-hero h2 {
                font-size: 2rem;
            }

            .search-input {
                font-size: 16px;
                padding: 15px 50px 15px 20px;
            }

            .result-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .file-actions {
                width: 100%;
                justify-content: flex-end;
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
                </div>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="nav">
        <div class="nav-content">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="employees.php"><i class="fas fa-users"></i> Employee Directory</a>
            <a href="documents/"><i class="fas fa-folder"></i> Documents</a>
            <a href="search.php" class="active"><i class="fas fa-search"></i> Search</a>
            <a href="tickets.php"><i class="fas fa-ticket-alt"></i> IT Tickets</a>
            <?php if ($auth->isAdmin()): ?>
                <a href="admin/"><i class="fas fa-cog"></i> Admin Panel</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Search Hero -->
        <div class="search-hero">
            <h2><i class="fas fa-search"></i> Search Documents</h2>
            <p>Find files, documents, and resources across the intranet</p>
            
            <form method="get" class="search-box">
                <input type="search" name="q" class="search-input" placeholder="Search for documents, reports, or files..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <?php if ($_GET['q']): ?>
            <div class="search-stats">
                <i class="fas fa-info-circle"></i> 
                Found <?php echo count($results); ?> result(s) for "<?php echo htmlspecialchars($_GET['q']); ?>"
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-file-alt"></i> Search Results</h3>
                </div>
                <div class="card-body">
                    <?php if ($results): ?>
                        <div class="results-grid">
                            <?php foreach ($results as $doc): ?>
                                <div class="result-item">
                                    <div class="file-info">
                                        <h4><?php echo htmlspecialchars($doc['original_name']); ?></h4>
                                        <div class="file-meta">
                                            <span><i class="fas fa-file"></i> <?php echo strtoupper($doc['file_type']); ?> File</span>
                                            <span><i class="fas fa-database"></i> <?php echo number_format($doc['file_size'] / 1024, 2); ?> KB</span>
                                            <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($doc['uploaded_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="file-actions">
                                        <a href="uploads/<?php echo htmlspecialchars($doc['filename']); ?>" target="_blank" class="action-btn view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="uploads/<?php echo htmlspecialchars($doc['filename']); ?>" download class="action-btn download">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>No documents found</h3>
                            <p>Try different keywords or check the spelling</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-search" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                        <h3 style="color: #666; margin-bottom: 10px;">Ready to search?</h3>
                        <p style="color: #888;">Enter your search terms above to find documents and files</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>