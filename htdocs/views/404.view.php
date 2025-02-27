<?php
$pageTitle = '404 - Page Not Found';
include 'partials/start.php';
?>

<div class="container">
  <div class="error-container">
    <p class="error-code">404</p>
    <p class="error-message">Oops! The page or document you're looking for doesn't exist.</p>
    <div class="error-action">
      <a href="index.php" class="download-button">Back to Home</a>
    </div>
  </div>
</div>

<?php include 'partials/end.php'; ?>
