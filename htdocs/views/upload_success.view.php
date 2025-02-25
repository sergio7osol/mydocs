<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Successful</title>
  <link rel="stylesheet" href="/public/base.css">
</head>
<body>
  <header>
    <h1>Upload Successful</h1>
    <div class="user-selector">
      <?php $currentUser = isset($_GET['user']) ? $_GET['user'] : 'sergey'; ?>
      <div>
        <input type="radio" id="user-sergey" name="current-user" value="sergey" <?= $currentUser === 'sergey' ? 'checked' : '' ?> disabled>
        <label for="user-sergey">Sergey</label>
      </div>
      <div>
        <input type="radio" id="user-galina" name="current-user" value="galina" <?= $currentUser === 'galina' ? 'checked' : '' ?> disabled>
        <label for="user-galina">Galina</label>
      </div>
    </div>
  </header>
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
        <tr>
          <td style="padding: 0.5em; font-weight: bold;">File Size:</td>
          <td style="padding: 0.5em;"><?= $this->formatFileSize($documentDetails['file_size']) ?></td>
        </tr>
        <tr>
          <td style="padding: 0.5em; font-weight: bold;">Upload Date:</td>
          <td style="padding: 0.5em;"><?= htmlspecialchars($documentDetails['upload_date']) ?></td>
        </tr>
        <?php if (!empty($documentDetails['description'])): ?>
        <tr>
          <td style="padding: 0.5em; font-weight: bold;">Description:</td>
          <td style="padding: 0.5em;"><?= nl2br(htmlspecialchars($documentDetails['description'])) ?></td>
        </tr>
        <?php endif; ?>
      </table>
    </div>
    
    <div style="text-align: center; margin-top: 2em;">
      <a href="index.php?route=list&category=<?= htmlspecialchars($documentDetails['category']) ?>&user=<?= htmlspecialchars($documentDetails['user']) ?>" style="display: inline-block; background-color: #4a6da7; color: white; text-decoration: none; padding: 0.5em 1em; border-radius: 4px;">View Documents</a>
      <a href="index.php?route=upload&user=<?= htmlspecialchars($documentDetails['user']) ?>" style="display: inline-block; background-color: #28a745; color: white; text-decoration: none; padding: 0.5em 1em; border-radius: 4px; margin-left: 1em;">Upload Another Document</a>
    </div>
  </div>
</body>
</html>
