<?php
$pageTitle = 'Document Management System';
include 'partials/start.php';
?>


<aside class="sidebar">
  <h2>Categories</h2>
  <ul>
    <?php
    $categories = ['Personal', 'Work', 'Others'];
    $currentCategory = isset($_GET['category']) ? $_GET['category'] : '';

    foreach ($categories as $category):
    ?>
      <li>
        <a href="index.php?route=list&category=<?= urlencode($category) ?>&user_id=<?= $currentUserId ?>"
          class="<?= ($currentCategory === $category) ? 'active-category' : '' ?>">
          <?= htmlspecialchars($category) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</aside>
<main class="content">
  <div class="content-header">
    <h1>Documents</h1>
    <?php if (isset($_GET['category'])): ?>
      <h2>Category: <?= htmlspecialchars($_GET['category']) ?></h2>
    <?php endif; ?>
    
    <!-- Search form -->
    <form class="search-form" action="index.php" method="get">
      <input type="hidden" name="route" value="list">
      <input type="hidden" name="user_id" value="<?= $currentUserId ?>">
      <?php if (isset($_GET['category'])): ?>
        <input type="hidden" name="category" value="<?= htmlspecialchars($_GET['category']) ?>">
      <?php endif; ?>
      <input type="text" name="search" placeholder="Search documents" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit" class="search-button">Search</button>
      <a href="index.php?route=upload&user_id=<?= $currentUserId ?>" class="upload-button">Upload Document</a>
    </form>
  </div>

  <!-- Messages section -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($_SESSION['success']) ?>
      <?php unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($_SESSION['error']) ?>
      <?php unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <div class="document-list" id="documentList">
    <?php if (!empty($documents)): ?>
      <?php 
        // Additional safeguard against duplicates in the view
        $renderedDocIds = [];
      ?>
      <?php foreach ($documents as $doc): ?>
        <?php
          // Skip if we've already rendered this document
          if (isset($renderedDocIds[$doc['id']])) {
            continue;
          }
          $renderedDocIds[$doc['id']] = true;
        ?>
        <div class="document-item" data-id="<?= $doc['id'] ?>" data-user-id="<?= $currentUserId ?>">
          <a href="index.php?route=delete&id=<?= $doc['id'] ?>&user_id=<?= $currentUserId ?><?= isset($_GET['category']) && !empty($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : '' ?>" 
             class="delete-document" 
             title="Delete document"
             onclick="return confirm('Are you sure you want to delete this document?');">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              <line x1="10" y1="11" x2="10" y2="17"></line>
              <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
          </a>
          <div class="document-item-content" onclick="window.location='index.php?route=view&id=<?= $doc['id'] ?>&user_id=<?= $currentUserId ?>'">
            <div class="document-item-title-area">
              <h3><?= htmlspecialchars($doc['title']) ?></h3>
              <?php if (!empty($doc['description'])): ?>
                <p class="document-description"><?= htmlspecialchars($doc['description']) ?></p>
              <?php endif; ?>
            </div>
            <span class="document-date"><span class="light-text">Uploaded on:</span> <?= htmlspecialchars($doc['upload_date']) ?></span>
            <span class="document-category"><?= htmlspecialchars($doc['category']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    <?php elseif (isset($_GET['category'])): ?>
      <p>No documents found in this category.</p>
    <?php else: ?>
      <p>No documents found. Upload some documents to get started.</p>
    <?php endif; ?>
  </div>
</main>


<script>
  function changeUser(userId) {
    // Update user input
    document.getElementById('userInput').value = userId;

    // If a category is already selected, preserve it when changing users
    const currentCategory = "<?= isset($_GET['category']) ? $_GET['category'] : '' ?>";
    if (currentCategory) {
      document.getElementById('categoryInput').value = currentCategory;
    }

    // Submit the form
    document.getElementById('searchForm').submit();
  }
</script>

<?php include 'partials/end.php'; ?>