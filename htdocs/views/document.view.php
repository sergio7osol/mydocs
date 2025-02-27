<?php
$pageTitle = 'Document Details';
include 'partials/start.php';

// Get current user ID from URL or default to 1 (Sergey)
$currentUserId = isset($_GET['user_id']) ? $_GET['user_id'] : 1; 
?>

<div class="container" style="padding: 1em;">
  <?php if (isset($document) && $document): ?>
    <div class="document-details">
      <h2><?= htmlspecialchars($document['title']); ?></h2>
      <p><strong>Upload Date:</strong> <?= htmlspecialchars($document['date'] ?? $document['upload_date']); ?></p>
      <?php if (!empty($document['created_date'])): ?>
      <p><strong>Document Creation Date:</strong> <?= htmlspecialchars($document['created_date']); ?></p>
      <?php endif; ?>
      <p><strong>Category:</strong> <?= htmlspecialchars($document['category']); ?></p>
      <?php if (isset($document['file_size'])): ?>
      <p><strong>File Size:</strong> <?= $this->formatFileSize($document['file_size']); ?></p>
      <?php endif; ?>
      <p><?= htmlspecialchars($document['description']); ?></p>
      
      <?php if (isset($document['can_download']) && $document['can_download']): ?>
      <div style="margin-top: 2em;">
        <a href="index.php?route=download&id=<?= $document['id']; ?>&user_id=<?= $currentUserId; ?>" class="download-button">
          Download Document
        </a>
      </div>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <p>Document not found.</p>
  <?php endif; ?>
  <p style="margin-top: 1em;"><a href="index.php?route=list&user_id=<?= $currentUserId ?>">Back to Document List</a></p>
</div>

<?php include 'partials/end.php'; ?>
