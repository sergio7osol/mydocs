<?php
view('partials/start.php', ['pageTitle' => $pageTitle ?? 'Upload Successful']);

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

<div class="upload-success">
  <div class="upload-success__message">
    <h2 class="upload-success__title">Document Successfully Uploaded!</h2>
    <p class="upload-success__text">Your document has been uploaded and is now available in the document list.</p>
  </div>
  
  <div class="upload-success__details">
    <h3 class="upload-success__subtitle">Document Details</h3>
    <table class="upload-success__table">
      <tr class="upload-success__row">
        <td class="upload-success__label">Title:</td>
        <td class="upload-success__value"><?= htmlspecialchars($documentDetails['title']) ?></td>
      </tr>
      <tr class="upload-success__row">
        <td class="upload-success__label">Category:</td>
        <td class="upload-success__value"><?= htmlspecialchars($documentDetails['category']) ?></td>
      </tr>
      <tr class="upload-success__row">
        <td class="upload-success__label">File Name:</td>
        <td class="upload-success__value"><?= htmlspecialchars($documentDetails['original_filename']) ?></td>
      </tr>
      <?php if ($documentDetails['original_filename'] != $documentDetails['filename']): ?>
      <tr class="upload-success__row">
        <td class="upload-success__label">Stored As:</td>
        <td class="upload-success__value">
          <?= htmlspecialchars($documentDetails['filename']) ?> 
          <span class="upload-success__note">(Renamed to avoid conflicts)</span>
        </td>
      </tr>
      <?php endif; ?>
      <tr class="upload-success__row">
        <td class="upload-success__label">File Size:</td>
        <td class="upload-success__value"><?= htmlspecialchars($documentDetails['file_size']); ?></td>
      </tr>
      <tr class="upload-success__row">
        <td class="upload-success__label">Upload Date:</td>
        <td class="upload-success__value"><?= htmlspecialchars($documentDetails['upload_date']) ?></td>
      </tr>
      <?php if (!empty($documentDetails['created_date'])): ?>
      <tr class="upload-success__row">
        <td class="upload-success__label">Document Creation Date:</td>
        <td class="upload-success__value"><?= htmlspecialchars($documentDetails['created_date']) ?></td>
      </tr>
      <?php endif; ?>
      <?php if (!empty($documentDetails['description'])): ?>
      <tr class="upload-success__row">
        <td class="upload-success__label">Description:</td>
        <td class="upload-success__value"><?= nl2br(htmlspecialchars($documentDetails['description'])) ?></td>
      </tr>
      <?php endif; ?>
    </table>
  </div>
  
  <div class="upload-success__actions">
    <a href="/?route=list&category=<?= htmlspecialchars($documentDetails['category']) ?>&user_id=<?= htmlspecialchars($documentDetails['user_id']) ?>" class="btn btn-primary">View Documents</a>
    <a href="/document/create?user_id=<?= htmlspecialchars($documentDetails['user_id']) ?>" class="btn btn-success">Upload Another Document</a>
  </div>
</div>

<?php view('partials/end.php'); ?>