<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1"> 
  <title>Document Management System</title>
  <link rel="stylesheet" href="/public/base.css">
</head>
<body>
  <header>
    <h1>Document Management System</h1>
    <div class="user-selector">
      <?php $currentUser = isset($_GET['user']) ? $_GET['user'] : 'sergey'; ?>
      <input type="radio" id="user-sergey" name="current-user" value="sergey" 
             <?= ($currentUser === 'sergey') ? 'checked' : ''; ?> 
             onchange="changeUser('sergey')">
      <label for="user-sergey">Sergey</label>
      <input type="radio" id="user-galina" name="current-user" value="galina" 
             <?= ($currentUser === 'galina') ? 'checked' : ''; ?> 
             onchange="changeUser('galina')">
      <label for="user-galina">Galina</label>
    </div>
  </header>
  <div class="container">
    <aside class="sidebar">
      <h2>Categories</h2>
      <ul>
        <li><a href="#" onclick="loadDocuments('Personal'); return false;">Personal</a></li>
        <li><a href="#" onclick="loadDocuments('Work'); return false;">Work</a></li>
        <li><a href="#" onclick="loadDocuments('Others'); return false;">Others</a></li>
      </ul>
    </aside>
    <main class="content">
      <div class="search-bar">
        <form method="GET" action="index.php" id="searchForm">
          <input type="hidden" name="route" value="list">
          <input type="hidden" name="category" id="categoryInput" value="<?= isset($_GET['category']) ? $_GET['category'] : '' ?>">
          <input type="hidden" name="user" id="userInput" value="<?= $currentUser ?>">
          <input type="text" name="search" placeholder="Search documents...">
          <button class="search-bar__submit" type="submit">Search</button>
          <a href="index.php?route=upload&user=<?= $currentUser ?>"><button type="button">Upload Document</button></a>
        </form>
      </div>
      <div class="document-list" id="documentList">
        <?php if (!empty($documents) && isset($_GET['category'])): ?>
          <?php foreach($documents as $doc): ?>
            <div class="document-item" onclick="window.location='index.php?route=view&id=<?= $doc['id'] ?>&user=<?= $currentUser ?>'">
              <div class="document-item-content">
                <h3><?= htmlspecialchars($doc['title']) ?></h3>
                <span class="document-date">Uploaded on: <?= htmlspecialchars($doc['upload_date']) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php elseif (isset($_GET['category'])): ?>
          <p>No documents found in this category.</p>
        <?php else: ?>
          <p>Select a category to view documents.</p>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script>
    function changeUser(user) {
      // Update user input
      document.getElementById('userInput').value = user;
      
      // If a category is already selected, preserve it when changing users
      const currentCategory = "<?= isset($_GET['category']) ? $_GET['category'] : '' ?>";
      if (currentCategory) {
        document.getElementById('categoryInput').value = currentCategory;
      }
      
      // Submit the form
      document.getElementById('searchForm').submit();
    }
    
    function loadDocuments(category) {
      // Get the currently selected user
      const selectedUser = document.querySelector('input[name="current-user"]:checked').value;
      
      // Redirect to the list route with the selected category and user
      window.location.href = 'index.php?route=list&category=' + category + '&user=' + selectedUser;
    }
  </script>
</body>
</html>