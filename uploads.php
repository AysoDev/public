<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$db = (new Database())->getConnection();

// VULNERABLE FILE UPLOAD - No proper validation
if ($_POST['upload']) {
    $uploadDir = UPLOAD_PATH;
    $fileName = $_FILES['document']['name'];
    $fileTmp = $_FILES['document']['tmp_name'];
    $fileSize = $_FILES['document']['size'];
    
    // Weak validation - only checks if file has an extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if ($fileSize > 0) {
        // Generate unique filename but preserve extension
        $newFileName = uniqid() . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            // Log the upload
            logAction("File uploaded: $fileName");
            
            $message = "File uploaded successfully!";
            
            // Store in database
            $stmt = $db->prepare("INSERT INTO documents (filename, original_name, file_path, uploaded_by, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$newFileName, $fileName, $uploadPath, $_SESSION['user_id'], $fileExt, $fileSize]);
        } else {
            $message = "Upload failed!";
        }
    }
}

// Get uploaded files
$stmt = $db->query("SELECT * FROM documents ORDER BY uploaded_at DESC");
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management - Sparrow Intranet</title>
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

        .btn-primary {
            background: var(--secondary);
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
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

        .admin-header {
            background: linear-gradient(135deg, var(--warning) 0%, #e67e22 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(243, 156, 18, 0.3);
        }

        .admin-header h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            background: #fafafa;
        }

        .upload-area:hover {
            border-color: var(--secondary);
            background: #f0f8ff;
        }

        .upload-area i {
            font-size: 48px;
            color: var(--secondary);
            margin-bottom: 15px;
        }

        .file-input {
            width: 100%;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid var(--success);
            color: #27ae60;
        }

        .message.error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid var(--accent);
            color: #c0392b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--primary);
        }

        tr:hover {
            background: #f8f9fa;
        }

        .file-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            background: #e9ecef;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .action-btns {
            display: flex;
            gap: 5px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .action-btn.view {
            background: var(--secondary);
            color: white;
        }

        .action-btn.download {
            background: var(--success);
            color: white;
        }

        .action-btn.delete {
            background: var(--accent);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .file-size {
            color: #666;
            font-family: monospace;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            .action-btns {
                flex-direction: column;
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
                    <span style="background: var(--warning); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">Admin</span>
                </div>
                <a href="../dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="nav">
        <div class="nav-content">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="index.php"><i class="fas fa-cog"></i> Admin Panel</a>
            <a href="uploads.php" class="active"><i class="fas fa-upload"></i> Document Management</a>
            <a href="manage_users.php"><i class="fas fa-users"></i> User Management</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h2><i class="fas fa-upload"></i> Document Management</h2>
            <p>Upload and manage company documents and files</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <i class="fas <?php echo strpos($message, 'successfully') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Upload Section -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-cloud-upload-alt"></i> Upload New Document</h3>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="upload-area">
                        <i class="fas fa-file-upload"></i>
                        <h3>Drop files here or click to browse</h3>
                        <p>Maximum file size: 10MB</p>
                        <input type="file" name="document" class="file-input" required>
                        <button type="submit" name="upload" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Document
                        </button>
                    </div>
                </form>
                <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <h4><i class="fas fa-info-circle"></i> Allowed File Types</h4>
                    <p><?php echo implode(', ', ALLOWED_EXTENSIONS); ?></p>
                </div>
            </div>
        </div>

        <!-- Documents List -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-files"></i> Uploaded Documents</h3>
            </div>
            <div class="card-body">
                <?php if ($documents): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($doc['original_name']); ?></strong>
                                </td>
                                <td>
                                    <span class="file-type">
                                        <i class="fas fa-file"></i> <?php echo strtoupper($doc['file_type']); ?>
                                    </span>
                                </td>
                                <td class="file-size"><?php echo number_format($doc['file_size'] / 1024, 2); ?> KB</td>
                                <td><?php echo date('M j, Y g:i A', strtotime($doc['uploaded_at'])); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="../uploads/<?php echo htmlspecialchars($doc['filename']); ?>" target="_blank" class="action-btn view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="../uploads/<?php echo htmlspecialchars($doc['filename']); ?>" download class="action-btn download">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-folder-open" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <h3>No documents uploaded yet</h3>
                        <p>Upload your first document using the form above</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>