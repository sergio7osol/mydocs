<?php
$currentUserId = $currentUserId ?? (isset($_GET['user_id']) ? $_GET['user_id'] : 1);
$userName = $userName ?? ('User ' . $currentUserId);

view('partials/start.php', [
    'pageTitle' => $pageTitle ?? 'View Document',
    'users' => $users,
    'currentUserId' => $currentUserId,
    'currentCategory' => $currentCategory ?? null,
    'userDocCounts' => $userDocCounts
]);
?>

<div class="document-view">
  <?php if (isset($document) && $document): ?>
    <div class="document-view__details">
      <h3 class="document-view__subtitle"><?= htmlspecialchars($document->title); ?></h3>
      <table class="document-view__table">
        <?php if (!empty($document->created_date)): ?>
          <tr class="document-view__row">
            <td class="document-view__label">Created at:</td>
            <td class="document-view__value">
              <span class="document-view__date"><?= htmlspecialchars($document->getFormattedCreatedDate()); ?></span>
            </td>
          </tr>
        <?php endif; ?>
        <tr class="document-view__row">
          <td class="document-view__label">Uploaded at:</td>
          <td class="document-view__value">
            <?php
            $formattedUploadDate = htmlspecialchars($document->getFormattedUploadDate());
            // Split the date and time parts based on the dash separator
            $dateParts = explode(' - ', $formattedUploadDate);
            if (count($dateParts) > 1) {
              echo $dateParts[0] . ' <span class="document-view__time">' . $dateParts[1] . '</span>';
            } else {
              echo $formattedUploadDate;
            }
            ?>
          </td>
        </tr>
        <?php if (!empty($document->description)): ?>
          <tr class="document-view__row">
            <td class="document-view__label">Description:</td>
            <td class="document-view__value">
              <div class="document-view__description">
                <?= nl2br(htmlspecialchars($document->description)); ?>
              </div>
            </td>
          </tr>
        <?php endif; ?>
        <tr class="document-view__row">
          <td class="document-view__label">Category:</td>
          <td class="document-view__value">
            <span class="document-view__category"><?= htmlspecialchars($document->category); ?></span>
          </td>
        </tr>
        <tr class="document-view__row">
          <td class="document-view__label">Owner:</td>
          <td class="document-view__value">
            <?php
            try {
              $owner = User::getById($document->user_id);
              echo htmlspecialchars($owner ? ($owner->firstname . ' ' . $owner->lastname) : 'Unknown');
            } catch (Exception $e) {
              echo 'Unknown';
            }
            ?>
          </td>
        </tr>
      </table>
    </div>

    <div class="document-view__content">
      <?php if (isset($document->filename) && pathinfo($document->filename, PATHINFO_EXTENSION) === 'txt'): ?>
        <h3 class="document-view__subtitle">Document Content:</h3>
        <pre class="document-view__text-content"><?= htmlspecialchars(file_get_contents($localPath)); ?></pre>
      <?php else: ?>
        <div class="document-view__file-info">
          <p class="document-view__file-path">
            <strong>File Path:</strong>
            <span class="path-container">
              <code id="filePath"
                class="copyable-path"
                data-path="<?= htmlspecialchars($windowsFilePath); ?>"
                data-dir-path="<?= htmlspecialchars($windowsDirectoryPath); ?>"
                title="Click buttons to copy paths">
                <?= htmlspecialchars($localPath); ?>
              </code>
          </p>
          <div class="button-group">
            <button id="copyFileBtn" class="copy-button">
              &#128196; Copy File Path
            </button>
            <button id="copyDirBtn" class="copy-button">
              &#128193; Copy Folder Path
            </button>
          </div>
          </span>
          <span id="copyMessage" style="display: none; color: green; margin-left: 10px; font-size: 0.9em;">Copied to clipboard!</span>
        </div>

        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const copyFileBtn = document.getElementById('copyFileBtn');
            const copyDirBtn = document.getElementById('copyDirBtn');
            const pathElement = document.getElementById('filePath');
            const message = document.getElementById('copyMessage');

            // Debug log the data attributes
            console.log('File path data attribute:', pathElement.getAttribute('data-path'));
            console.log('Folder path data attribute:', pathElement.getAttribute('data-dir-path'));

            // Function to copy text to clipboard
            function copyToClipboard(text, successMessage) {
              // Log to console for debugging
              console.log('Attempting to copy:', text);

              // Use the modern clipboard API
              if (navigator.clipboard) {
                navigator.clipboard.writeText(text)
                  .then(() => {
                    console.log('Successfully copied to clipboard');
                    showCopyMessage(successMessage);
                  })
                  .catch(err => {
                    console.error('Failed to copy text: ', err);
                    alert('Could not copy to clipboard. Your browser might be blocking this feature.');
                  });
              } else {
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = 0;
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();

                try {
                  const successful = document.execCommand('copy');
                  if (successful) {
                    console.log('Fallback copy successful');
                    showCopyMessage(successMessage);
                  } else {
                    console.error('Fallback copy failed');
                    alert('Could not copy to clipboard');
                  }
                } catch (err) {
                  console.error('Fallback copy error: ', err);
                  alert('Could not copy to clipboard');
                }
                
                document.body.removeChild(textarea);
              }
            }

            // Show copy message
            function showCopyMessage(successMsg) {
              message.textContent = successMsg || 'Copied to clipboard!';
              message.style.display = 'inline-block';
              setTimeout(() => {
                message.style.display = 'none';
              }, 2000);
            }

            // Event listeners for copy buttons
            copyFileBtn.addEventListener('click', function() {
              const filePath = pathElement.getAttribute('data-path');
              copyToClipboard(filePath, 'File path copied to clipboard!');
            });

            copyDirBtn.addEventListener('click', function() {
              const dirPath = pathElement.getAttribute('data-dir-path');
              copyToClipboard(dirPath, 'Folder path copied to clipboard!');
            });
          });
        </script>
      <?php endif; ?>
    </div>

    <section class="document-actions">
      <a href="/document/edit?id=<?= $document->id; ?>&user_id=<?= $currentUserId; ?>" class="document-actions__button document-actions__button--edit">
        <span class="document-actions__icon document-actions__icon--edit">✏️</span>
        <span class="document-actions__text">Edit Document</span>
      </a>
      <a href="/doc/download?id=<?= $document->id; ?>&user_id=<?= $currentUserId; ?>" class="document-actions__button document-actions__button--download">
        <span class="document-actions__icon document-actions__icon--download">📥</span>
        <span class="document-actions__text">Download Document</span>
      </a>
    </section>
  <?php else: ?>
    <p class="document-view__not-found">Document not found.</p>
  <?php endif; ?>
  <div class="document-view__back">
    <a href="/?user_id=<?= $currentUserId ?>" class="document-view__back-link-simple">Back to Document List</a>
  </div>
</div>

<?php view('partials/end.php'); ?>