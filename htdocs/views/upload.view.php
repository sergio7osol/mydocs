<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Document</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <h1>Upload Document</h1>
  <?php if (isset($message)) { ?><p><?= htmlspecialchars($message); ?></p><?php } ?>
  <form action="index.php?route=upload" method="POST" enctype="multipart/form-data">
    <label for="document">Select document to upload:</label>
    <input type="file" name="document" id="document" required>
    <button type="submit">Upload</button>
  </form>
  <p><a href="index.php?route=list">Back to Document List</a></p>
</body>
</html>
