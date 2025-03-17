<?php
view('partials/start.php', ['pageTitle' => $pageTitle ?? '404 - Page Not Found']);
?>

<div class="container">
  <div class="error-container">
    <p class="error-code">404</p>
    <p class="error-message">Oops! The page or document you're looking for doesn't exist.</p>
    <div class="error-action">
      <a href="/" class="download-button">Back to Home</a>
    </div>
  </div>
</div>

<?php view('partials/end.php'); ?>
