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
  <div class="search-bar">
    <form method="GET" action="index.php" id="searchForm">
      <input type="hidden" name="route" value="list">
      <input type="hidden" name="category" id="categoryInput" value="<?= isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '' ?>">
      <input type="hidden" name="user_id" id="userInput" value="<?= $currentUserId ?>">
      <input type="text" name="search" placeholder="Search documents..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button class="search-bar__submit" type="submit">Search</button>
      <a href="index.php?route=upload&user_id=<?= $currentUserId ?>"><button type="button">Upload Document</button></a>
    </form>
  </div>
  <div class="document-list" id="documentList">
    <?php if (!empty($documents)): ?>
      <?php foreach ($documents as $doc): ?>
        <div class="document-item" onclick="window.location='index.php?route=view&id=<?= $doc['id'] ?>&user_id=<?= $currentUserId ?>'">
          <div class="document-item-content">
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