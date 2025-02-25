<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Document Details</title>
  <link rel="stylesheet" href="/public/base.css">
</head>
<body>
  <header>
    <h1>Document Details</h1>
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
    <?php if (isset($document) && $document): ?>
      <div class="document-details">
        <h2><?= htmlspecialchars($document['title']); ?></h2>
        <p><strong>Date:</strong> <?= htmlspecialchars($document['date']); ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($document['category']); ?></p>
        <?php if (isset($document['file_size'])): ?>
        <p><strong>File Size:</strong> <?= $this->formatFileSize($document['file_size']); ?></p>
        <?php endif; ?>
        <p><?= htmlspecialchars($document['description']); ?></p>
        
        <?php if (isset($document['can_download']) && $document['can_download']): ?>
        <div style="margin-top: 2em;">
          <a href="index.php?route=download&id=<?= $document['id']; ?>&user=<?= $document['user']; ?>" class="download-button">
            Download Document
          </a>
        </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <p>Document not found.</p>
    <?php endif; ?>
    <p><a href="index.php?route=list&user=<?= $currentUser ?>">Back to Document List</a></p>
  </div>
</body>
</html>
