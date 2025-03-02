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
      
      <table style="border-collapse: separate; border-spacing: 0 18px; margin-bottom: 15px; width: auto;">
        <?php if (!empty($document->created_date)): ?>
        <tr style="margin-bottom: 12px;">
          <td style="padding-right: 15px; color: #555; vertical-align: middle;">Created at:</td>
          <td>
            <span style="display: inline-block; background-color: transparent; border-bottom: 2px solid #2196f3; color: #0d47a1; font-weight: 600; padding: 4px 2px;"><?= htmlspecialchars($document->getFormattedCreatedDate()); ?></span>
          </td>
        </tr>
        <?php endif; ?> 
        <tr style="margin-bottom: 12px;">
          <td style="padding-right: 15px; color: #555;">Uploaded at:</td>
          <td>
            <?php 
            $formattedUploadDate = htmlspecialchars($document->getFormattedUploadDate());
            // Split the date and time parts based on the dash separator
            $dateParts = explode(' - ', $formattedUploadDate);
            if (count($dateParts) > 1) {
              echo $dateParts[0] . ' <span style="font-size: 85%; color: #777;">- ' . $dateParts[1] . '</span>';
            } else {
              echo $formattedUploadDate;
            }
            ?>
          </td>
        </tr>
        <?php if (!empty($document->description)): ?>
        <tr style="margin-bottom: 12px;">
          <td style="padding-right: 15px; color: #555; vertical-align: top; padding-top: 8px;">Description:</td>
          <td>
            <div style="border: 1px solid #ddd; border-radius: 4px; padding: 8px 12px; line-height: 1.4; max-width: 500px; display: inline-block;">
              <?= nl2br(htmlspecialchars($document->description)); ?>
            </div>
          </td>
        </tr>
        <?php endif; ?>
        <tr style="margin-bottom: 12px;">
          <td style="padding-right: 15px; color: #555;">Category:</td>
          <td><span style="display: inline-block; background-color: #e3f2fd; color: #0d47a1; border-radius: 16px; padding: 3px 10px; font-size: 90%;"><?= htmlspecialchars($document->category); ?></span></td>
        </tr>
        <tr>
          <td style="padding-right: 15px; color: #555;">Owner:</td>
          <td>
            <?php 
            try {
              $owner = User::getById($document->user_id);
              echo htmlspecialchars($owner ? ($owner->firstname . ' ' . $owner->lastname) : 'Unknown');
            } catch (Exception $e) {
              echo 'Unknown';
            }
            ?>
          </td>
        </tr>
      </table>
      
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
          <p><strong>File Path:</strong> <code style="background-color: #f5f5f5; padding: 3px 6px; border: 1px solid #e1e1e1; border-radius: 3px; font-family: monospace; word-break: break-all;"><?= htmlspecialchars($localPath); ?></code></p>
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
