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
      <h2><?= htmlspecialchars($document['title']); ?></h2>
      <p><strong>Upload Date:</strong> <?= htmlspecialchars($document['upload_date']); ?></p>
      <?php if (!empty($document['created_date'])): ?>
      <p><strong>Document Creation Date:</strong> <?= htmlspecialchars($document['created_date']); ?></p>
      <?php endif; ?>
      <p><strong>Category:</strong> <?= htmlspecialchars($document['category']); ?></p>
      <p><strong>Owner:</strong> 
        <?php 
        try {
          $owner = User::getById($document['user_id']);
          echo htmlspecialchars($owner ? ($owner->firstname . ' ' . $owner->lastname) : 'Unknown');
        } catch (Exception $e) {
          echo 'Unknown';
        }
        ?>
      </p>
      
      <div class="document-content" style="margin-top: 1em; padding: 1em; border: 1px solid #ddd; background-color: #f9f9f9;">
        <?php if (isset($document['filename']) && pathinfo($document['filename'], PATHINFO_EXTENSION) === 'txt'): ?>
          <h3>Document Content:</h3>
          <pre style="white-space: pre-wrap;"><?= htmlspecialchars(file_get_contents($document['filepath'])); ?></pre>
        <?php else: ?>
          <p>This document can be downloaded using the link below.</p>
        <?php endif; ?>
      </div>
      
      <div style="margin-top: 2em;">
        <a href="index.php?route=download&id=<?= $document['id']; ?>&user_id=<?= $currentUserId; ?>" class="download-button">
          Download Document
        </a>
      </div>
    </div>
  <?php else: ?>
    <p>Document not found.</p>
  <?php endif; ?>
  <p style="margin-top: 1em;"><a href="index.php?route=list&user_id=<?= $currentUserId ?>">Back to Document List</a></p>
</div>

<?php include 'partials/end.php'; ?>
