<?php

$pageTitle = 'Upload Document';

require_once 'views/partials/start.php';

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
            <input type="text" name="title" id="title" class="upload-form__line-input <?= isset($errors['title']) ? 'is-invalid' : '' ?>" required maxlength="70" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            <?php if (isset($errors['title'])) : ?>
            <div class="error-message">
              <?= htmlspecialchars($errors['title']) ?>
            </div>
            <?php endif; ?>
            <small class="upload-form__line-clarification">The title of the document being uploaded.</small>
          </div>
          
          <div id="PPP" class="upload-form__line upload-form__line--file">
            <label for="document" class="upload-form__line-title">Select document to upload:</label>
            <div class="upload-form__line-input">
              <input type="file" name="document" id="document" required accept=".pdf,.doc,.docx,.txt" class="<?= isset($errors['document']) ? 'is-invalid' : '' ?>">
              <?php if (isset($errors['document'])) : ?>
              <div class="error-message">
                <?= htmlspecialchars($errors['document']) ?>
              </div>
              <?php endif; ?>
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
            <select name="category" id="category" required class="upload-form__line-input <?= isset($errors['category']) ? 'is-invalid' : '' ?>">
              <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['name']) ?>" 
                  <?= ($preselectedCategory === $category['name'] || ($_POST['category'] ?? '') === $category['name']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($category['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['category'])) : ?>
            <div class="error-message">
              <?= htmlspecialchars($errors['category']) ?>
            </div>
            <?php endif; ?>
          </div>
          
          <div class="upload-form__line">
            <label for="created_date" class="upload-form__line-title">Created at (optional):</label>
            <input type="date" name="created_date" id="created_date" class="upload-form__line-input <?= isset($errors['created_date']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['created_date'] ?? '') ?>">
            <?php if (isset($errors['created_date'])) : ?>
            <div class="error-message">
              <?= htmlspecialchars($errors['created_date']) ?>
            </div>
            <?php endif; ?>
            <small class="upload-form__line-clarification">The date when this document was originally created (not when you're uploading it)</small>
          </div>
          
          <div class="upload-form__line upload-form__line--textarea">
            <label for="description" class="upload-form__line-title">Description (optional):</label>
            <textarea name="description" id="description" class="upload-form__line-input <?= isset($errors['description']) ? 'is-invalid' : '' ?>" maxlength="300"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            <?php if (isset($errors['description'])) : ?>
            <div class="error-message">
              <?= htmlspecialchars($errors['description']) ?>
            </div>
            <?php endif; ?>
            <small class="upload-form__line-clarification">Brief description of the document (maximum 300 characters)</small>
          </div>
          
          <div class="upload-form__line upload-form__line--hidden">
            <input type="hidden" name="user_id" value="<?= $currentUserId ?>">
            <?php if (isset($errors['user_id'])) : ?>
            <div class="error-message">
              <?= htmlspecialchars($errors['user_id']) ?>
            </div>
            <?php endif; ?>
          </div>
          
          <div class="upload-form__line upload-form__line--button">
            <button type="submit" class="upload-form__line-button">Upload Document</button>
          </div>
        </form>
      </div>
    </article>
  </div>
</div>

<?php include 'views/partials/end.php'; ?>
