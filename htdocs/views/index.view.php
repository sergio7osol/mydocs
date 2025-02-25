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
        <li><a href="index.php?route=list&category=Personal">Personal</a></li>
        <li><a href="index.php?route=list&category=Work">Work</a></li>
        <li><a href="index.php?route=list&category=Others">Others</a></li>
      </ul>
    </aside>
    <main class="content">
      <div class="search-bar">
        <form method="GET" action="index.php">
          <input type="hidden" name="route" value="list">
          <input type="text" name="search" placeholder="Search documents...">
          <button class="search-bar__submit" type="submit">Search</button>
          <a href="index.php?route=upload"><button type="button">Upload Document</button></a>
        </form>
      </div>
      <div class="document-list">
        <?php
          // Use the documents passed from the controller
          if(empty($documents)) {
            echo "<p>No documents found.</p>";
          } else {
            foreach($documents as $doc) {
              echo "<div class='document-item'>";
              echo "<h3><a href='index.php?route=view&id=" . $doc['id'] . "'>" . htmlspecialchars($doc['title']) . "</a></h3>";
              echo "<p>Uploaded on: " . htmlspecialchars($doc['date']) . "</p>";
              echo "<p>Category: " . htmlspecialchars($doc['category']) . "</p>";
              echo "</div>";
            }
          }
        ?>
      </div>
    </main>
  </div>

  <script>
    function changeUser(user) {
      window.location.href = 'index.php?route=list&user=' + user;
    }
  </script>
</body>
</html>