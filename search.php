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
<html>
<head>
    <title>Search Documents</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .search-box { margin: 20px 0; }
        input[type="search"] { padding: 10px; width: 300px; }
        button { padding: 10px 15px; background: #007cba; color: white; border: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <h2>Search Documents</h2>
    <a href="dashboard.php">← Back to Dashboard</a>
    
    <div class="search-box">
        <form method="get">
            <input type="search" name="q" placeholder="Search documents..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
            <button type="submit">Search</button>
        </form>
    </div>
    
    <?php if ($_GET['q']): ?>
        <h3>Search Results for "<?php echo htmlspecialchars($_GET['q']); ?>"</h3>
        <?php if ($results): ?>
            <table>
                <tr>
                    <th>Filename</th>
                    <th>Type</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($results as $doc): ?>
                <tr>
                    <td><?php echo htmlspecialchars($doc['original_name']); ?></td>
                    <td><?php echo htmlspecialchars($doc['file_type']); ?></td>
                    <td><?php echo $doc['uploaded_at']; ?></td>
                    <td>
                        <a href="uploads/<?php echo htmlspecialchars($doc['filename']); ?>" target="_blank">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No documents found.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>