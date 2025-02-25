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
  </header>
  <div class="container" style="padding: 1em;">
    <?php if (isset($document) && $document): ?>
      <div class="document-details">
        <h2><?= htmlspecialchars($document['title']); ?></h2>
        <p><strong>Date:</strong> <?= htmlspecialchars($document['date']); ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($document['category']); ?></p>
        <p><?= htmlspecialchars($document['description']); ?></p>
      </div>
    <?php else: ?>
      <p>Document not found.</p>
    <?php endif; ?>
    <p><a href="index.php?route=list">Back to Document List</a></p>
  </div>
</body>
</html>
