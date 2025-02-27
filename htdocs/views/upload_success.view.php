<?php
$pageTitle = 'Upload Successful';
include 'partials/start.php';

// Get current user ID from documentDetails
$currentUserId = $documentDetails['user_id']; 

// Try to get user object from the database
try {
  $user = User::getById($currentUserId);
  $userName = $user ? $user->firstname : 'User ' . $currentUserId;
} catch (Exception $e) {
  $userName = 'User ' . $currentUserId;
}
?>

<div class="container" style="padding: 1em;">
  <div class="success-message" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1em; border-radius: 4px; margin-bottom: 2em;">
    <h2 style="margin-top: 0;">Document Successfully Uploaded!</h2>
    <p>Your document has been uploaded and is now available in the document list.</p>
  </div>
  
  <div class="document-details" style="max-width: 600px; margin: 0 auto; padding: 1em; background-color: #f8f9fa; border-radius: 4px; border: 1px solid #ddd;">
    <h3>Document Details</h3>
    <table style="width: 100%; border-collapse: collapse;">
      <tr>
        <td style="padding: 0.5em; font-weight: bold; width: 30%;">Title:</td>
        <td style="padding: 0.5em;"><?= htmlspecialchars($documentDetails['title']) ?></td>
      </tr>
      <tr>
        <td style="padding: 0.5em; font-weight: bold;">Category:</td>
        <td style="padding: 0.5em;"><?= htmlspecialchars($documentDetails['category']) ?></td>
      </tr>
      <tr>
        <td style="padding: 0.5em; font-weight: bold;">File Name:</td>
        <td style="padding: 0.5em;"><?= htmlspecialchars($documentDetails['original_filename']) ?></td>
      </tr>
      <?php if ($documentDetails['original_filename'] != $documentDetails['filename']): ?>
      <tr>
        <td style="padding: 0.5em; font-weight: bold;">Stored As:</td>
        <td style="padding: 0.5em;"><?= htmlspecialchars($documentDetails['filename']) ?> <small>(Renamed to avoid conflicts)</small></td>
      </tr>
      <?php endif; ?>
      <tr>
        <td style="padding: 0.5em; font-weight: bold;">File Size:</td>
        <td style="padding: 0.5em;"><?= htmlspecialchars($documentDetails['file_size']); ?></td>
      </tr>
      <tr>
        <td style="padding: 0.5em; font-weight: bold;">Upload Date:</td>
        <td style="padding: 0.5em;"><?= htmlspecialchars($documentDetails['upload_date']) ?></td>
      </tr>
      <?php if (!empty($documentDetails['created_date'])): ?>
      <tr>
        <td style="padding: 0.5em; font-weight: bold;">Document Creation Date:</td>
        <td style="padding: 0.5em;"><?= htmlspecialchars($documentDetails['created_date']) ?></td>
      </tr>
      <?php endif; ?>
      <?php if (!empty($documentDetails['description'])): ?>
      <tr>
        <td style="padding: 0.5em; font-weight: bold;">Description:</td>
        <td style="padding: 0.5em;"><?= nl2br(htmlspecialchars($documentDetails['description'])) ?></td>
      </tr>
      <?php endif; ?>
    </table>
  </div>
  
  <div style="text-align: center; margin-top: 2em;">
    <a href="index.php?route=list&category=<?= htmlspecialchars($documentDetails['category']) ?>&user_id=<?= htmlspecialchars($documentDetails['user_id']) ?>" class="btn btn-primary">View Documents</a>
    <a href="index.php?route=upload&user_id=<?= htmlspecialchars($documentDetails['user_id']) ?>" class="btn btn-success">Upload Another Document</a>
  </div>
</div>

<?php include 'partials/end.php'; ?>
