<?php

$pageTitle = 'Upload Document';
include 'partials/start.php';
$currentUserId = isset($_GET['user_id']) ? $_GET['user_id'] : 1;

?>

<div class="container" style="padding: 1em;">
  <?php if (isset($message)) { ?><div class="message"><?= htmlspecialchars($message); ?></div><?php } ?>
  <form action="index.php?route=upload_post&user_id=<?= $currentUserId ?>" method="POST" enctype="multipart/form-data" style="max-width: 500px; margin: 2em auto;">
    <div style="margin-bottom: 1em;">
      <label for="title">Document Title:</label>
      <input type="text" name="title" id="title" required style="margin-top: 0.5em; width: 100%; padding: 0.5em;">
    </div>
    <div style="margin-bottom: 1em;">
      <label for="document">Select document to upload:</label>
      <input type="file" name="document" id="document" required style="margin-top: 0.5em; width: 100%;" accept=".pdf,.doc,.docx,.txt">
      <small style="color: #666; display: block; margin-top: 0.3em;">Allowed file types: PDF, DOC, DOCX, TXT. Max size: 5MB</small>
    </div>
    <div style="margin-bottom: 1em;">
      <label for="category">Category:</label>
      <select name="category" id="category" required style="margin-top: 0.5em; width: 100%; padding: 0.5em;">
        <option value="Personal">Personal</option>
        <option value="Work">Work</option>
        <option value="Others">Others</option>
      </select>
    </div>
    <div style="margin-bottom: 1em;">
      <label for="description">Description (optional):</label>
      <textarea name="description" id="description" style="margin-top: 0.5em; width: 100%; padding: 0.5em; height: 80px;"></textarea>
    </div>
    <input type="hidden" name="user_id" value="<?= $currentUserId ?>">
    <button type="submit" style="background-color: #28a745; color: white; border: none; padding: 0.5em 1em; border-radius: 4px; cursor: pointer;">Upload Document</button>
  </form>
  <p style="text-align: center;"><a href="index.php?route=list&user_id=<?= $currentUserId ?>">Back to Document List</a></p>
</div>

<?php include 'partials/end.php'; ?>
