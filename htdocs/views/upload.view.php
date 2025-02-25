<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Document</title>
  <link rel="stylesheet" href="/public/base.css">
</head>
<body>
  <header>
    <h1>Upload Document</h1>
    <div class="user-selector">
      <?php $currentUser = isset($_GET['user']) ? $_GET['user'] : 'sergey'; ?>
      <div>
        <input type="radio" id="user-sergey" name="current-user" value="sergey" <?= $currentUser === 'sergey' ? 'checked' : '' ?> onchange="changeUser('sergey')">
        <label for="user-sergey">Sergey</label>
      </div>
      <div>
        <input type="radio" id="user-galina" name="current-user" value="galina" <?= $currentUser === 'galina' ? 'checked' : '' ?> onchange="changeUser('galina')">
        <label for="user-galina">Galina</label>
      </div>
    </div>
  </header>
  <div class="container" style="padding: 1em;">
    <?php if (isset($message)) { ?><div class="message"><?= htmlspecialchars($message); ?></div><?php } ?>
    <form action="index.php?route=upload&user=<?= $currentUser ?>" method="POST" enctype="multipart/form-data" style="max-width: 500px; margin: 2em auto;">
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
      <button type="submit" style="background-color: #28a745; color: white; border: none; padding: 0.5em 1em; border-radius: 4px; cursor: pointer;">Upload Document</button>
    </form>
    <p style="text-align: center;"><a href="index.php?route=list&user=<?= $currentUser ?>">Back to Document List</a></p>
  </div>

  <script>
    function changeUser(user) {
      window.location.href = 'index.php?route=upload&user=' + user;
    }
  </script>
</body>
</html>
