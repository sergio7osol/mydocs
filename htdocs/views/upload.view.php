<?php

$pageTitle = 'Upload Document';

include 'partials/start.php';

$currentUserId = isset($_GET['user_id']) ? $_GET['user_id'] : 1;
$preselectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Categories are now loaded from the controller
$categories = $categories ?? [];

?>

<div class="upload-box">
  <div class="upload-form__container">
    <?php if (isset($message)) { ?>
      <div class="upload-form__alert <?= strpos($message, 'Error') !== false ? 'upload-form__alert--danger' : 'upload-form__alert--success' ?>">
        <?= htmlspecialchars($message); ?>
      </div>
    <?php } ?>
    
    <article class="card">
      <div class="card__header">
        <h2 class="card__header-title">Upload New Document</h2>
        <a class="upload-form__header-button" href="index.php?route=list&user_id=<?= $currentUserId ?>">Back to Document List</a>
      </div>
      
      <div class="card__body">
        <form class="upload-form" action="/doc/upload?user_id=<?= $currentUserId ?>" method="POST" enctype="multipart/form-data">
          <div class="upload-form__line">
            <label for="title" class="upload-form__line-title">Document Title:</label>
            <input type="text" name="title" id="title" class="upload-form__line-input" required>
            <small class="upload-form__line-clarification">The title of the document being uploaded.</small>
          </div>
          
          <div id="PPP" class="upload-form__line upload-form__line--file">
            <label for="document" class="upload-form__line-title">Select document to upload:</label>
            <div class="upload-form__line-input">
              <input type="file" name="document" id="document" required accept=".pdf,.doc,.docx,.txt">
              <div class="upload-form__line-formats">
                <span class="upload-form__line-format">PDF</span>
                <span class="upload-form__line-format">DOC</span>
                <span class="upload-form__line-format">DOCX</span>
                <span class="upload-form__line-format">TXT</span>
                <span class="upload-form__line-size">Max: 15MB</span>
              </div>
            </div>
            <small class="upload-form__line-clarification">Upload a document in PDF, DOC, DOCX, or TXT format (max 15MB).</small>
          </div>
          
          <div class="upload-form__line upload-form__line--select">
            <label for="category" class="upload-form__line-title">Category:</label>
            <select name="category" id="category" required class="upload-form__line-input">
              <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['name']) ?>" <?= ($preselectedCategory === $category['name']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($category['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="upload-form__line">
            <label for="created_date" class="upload-form__line-title">Created at (optional):</label>
            <input type="date" name="created_date" id="created_date" class="upload-form__line-input">
            <small class="upload-form__line-clarification">The date when this document was originally created (not when you're uploading it)</small>
          </div>
          
          <div class="upload-form__line upload-form__line--textarea">
            <label for="description" class="upload-form__line-title">Description (optional):</label>
            <textarea name="description" id="description" class="upload-form__line-input"></textarea>
          </div>
          
          <div class="upload-form__line upload-form__line--hidden">
            <input type="hidden" name="user_id" value="<?= $currentUserId ?>">
          </div>
          
          <div class="upload-form__line upload-form__line--button">
            <button type="submit" class="upload-form__line-button">Upload Document</button>
          </div>
        </form>
      </div>
    </article>
  </div>
</div>

<?php include 'partials/end.php'; ?>
