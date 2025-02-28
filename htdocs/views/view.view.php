<?php
$pageTitle = 'View Document';
include 'partials/start.php';

// Get current user ID from URL or default to 1 (Sergey)
$currentUserId = isset($_GET['user_id']) ? $_GET['user_id'] : 1; 
      
// Try to get user object from the database
try {
  $user = User::getById($currentUserId);
  $userName = $user ? $user->firstname : 'User ' . $currentUserId;
} catch (Exception $e) {
  $userName = 'User ' . $currentUserId;
}
?>

<div class="container" style="padding: 1em;">
  <?php if (isset($document) && $document): ?>
    <div class="document-details">
      <h2><?= htmlspecialchars($document->title); ?></h2>
      <?php if (!empty($document->created_date)): ?>
      <p><strong>Document Creation Date:</strong> <?= htmlspecialchars($document->created_date); ?></p>
      <?php endif; ?>
      <p><strong>Upload Date:</strong> <?= htmlspecialchars($document->upload_date); ?></p>
      <p><strong>Category:</strong> <?= htmlspecialchars($document->category); ?></p>
      <p><strong>Owner:</strong> 
        <?php 
        try {
          $owner = User::getById($document->user_id);
          echo htmlspecialchars($owner ? ($owner->firstname . ' ' . $owner->lastname) : 'Unknown');
        } catch (Exception $e) {
          echo 'Unknown';
        }
        ?>
      </p>
      
      <?php 
      // Convert Docker path to local path for display
      $dockerPath = $document->file_path;
      $localPath = str_replace('/var/www/html/', '', $dockerPath);
      
      // For debugging
      error_log("Original file path: " . $dockerPath);
      error_log("Simplified path: " . $localPath);
      
      // Get the absolute path on disk - let PHP handle path resolution
      $absolutePath = realpath(__DIR__ . '/../../' . $localPath);
      
      // Store raw path for directory navigation
      $rawPath = dirname(__DIR__) . '/../' . $localPath;
      
      error_log("Calculated absolute path: " . $absolutePath);
      error_log("Raw path for explorer: " . $rawPath);
      ?>
      
      <div class="document-content" style="margin-top: 1em; padding: 1em; border: 1px solid #ddd; background-color: #f9f9f9;">
        <?php if (isset($document->filename) && pathinfo($document->filename, PATHINFO_EXTENSION) === 'txt'): ?>
          <h3>Document Content:</h3>
          <pre style="white-space: pre-wrap;"><?= htmlspecialchars(file_get_contents($localPath)); ?></pre>
        <?php else: ?>
          <p><strong>File Path:</strong> <?= htmlspecialchars($localPath); ?></p>
        <?php endif; ?>
      </div>
      
      <div style="margin-top: 2em; display: flex; gap: 10px;">
        <a href="index.php?route=download&id=<?= $document->id; ?>&user_id=<?= $currentUserId; ?>" class="download-button">
          Download Document
        </a>
        
        <?php /* "Open in Explorer" button removed as it doesn't work with Docker */ ?>
      </div>
    </div>
  <?php else: ?>
    <p>Document not found.</p>
  <?php endif; ?>
  <p style="margin-top: 1em;"><a href="index.php?route=list&user_id=<?= $currentUserId ?>">Back to Document List</a></p>
</div>

<?php include 'partials/end.php'; ?>

<script>
  <?php /* All "Open in Explorer" related JavaScript code removed */ ?>
</script>
