<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

$db = (new Database())->getConnection();

// Reset admin password to 'sparrow123'
$new_hash = password_hash('sparrow123', PASSWORD_DEFAULT);

$stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$new_hash]);

echo "Admin password reset to: sparrow123\n";
echo "New hash: " . $new_hash . "\n";

// Verify it works
if (password_verify('sparrow123', $new_hash)) {
    echo "✅ Verification: SUCCESS\n";
} else {
    echo "❌ Verification: FAILED\n";
}
?>