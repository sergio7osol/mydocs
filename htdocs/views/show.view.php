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

<div class="container" style="padding: 1em;">
  <?php if (isset($document) && $document): ?>
    <div class="document-details">
      <h2><?= htmlspecialchars($document->title); ?></h2>

      <table style="border-collapse: separate; border-spacing: 0 18px; margin-bottom: 15px; width: auto;">
        <?php if (!empty($document->created_date)): ?>
          <tr style="margin-bottom: 12px;">
            <td style="padding-right: 15px; color: #555; vertical-align: middle;">Created at:</td>
            <td>
              <span style="display: inline-block; background-color: transparent; border-bottom: 2px solid #2196f3; color: #0d47a1; font-weight: 600; padding: 4px 2px;"><?= htmlspecialchars($document->getFormattedCreatedDate()); ?></span>
            </td>
          </tr>
        <?php endif; ?>
        <tr style="margin-bottom: 12px;">
          <td style="padding-right: 15px; color: #555;">Uploaded at:</td>
          <td>
            <?php
            $formattedUploadDate = htmlspecialchars($document->getFormattedUploadDate());
            // Split the date and time parts based on the dash separator
            $dateParts = explode(' - ', $formattedUploadDate);
            if (count($dateParts) > 1) {
              echo $dateParts[0] . ' <span style="font-size: 85%; color: #777;">- ' . $dateParts[1] . '</span>';
            } else {
              echo $formattedUploadDate;
            }
            ?>
          </td>
        </tr>
        <?php if (!empty($document->description)): ?>
          <tr style="margin-bottom: 12px;">
            <td style="padding-right: 15px; color: #555; vertical-align: top; padding-top: 8px;">Description:</td>
            <td>
              <div style="border: 1px solid #ddd; border-radius: 4px; padding: 8px 12px; line-height: 1.4; max-width: 500px; display: inline-block;">
                <?= nl2br(htmlspecialchars($document->description)); ?>
              </div>
            </td>
          </tr>
        <?php endif; ?>
        <tr style="margin-bottom: 12px;">
          <td style="padding-right: 15px; color: #555;">Category:</td>
          <td><span style="display: inline-block; background-color: #e3f2fd; color: #0d47a1; border-radius: 16px; padding: 3px 10px; font-size: 90%;"><?= htmlspecialchars($document->category); ?></span></td>
        </tr>
        <tr>
          <td style="padding-right: 15px; color: #555;">Owner:</td>
          <td>
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

      <div class="document-content" style="margin-top: 1em; padding: 1em; border: 1px solid #ddd; background-color: #f9f9f9;">
        <?php if (isset($document->filename) && pathinfo($document->filename, PATHINFO_EXTENSION) === 'txt'): ?>
          <h3>Document Content:</h3>
          <pre style="white-space: pre-wrap;"><?= htmlspecialchars(file_get_contents($localPath)); ?></pre>
        <?php else: ?>
          <p style="display: flex; align-items: center;">
            <strong>File Path:</strong>
            <span class="path-container" style="margin-left: .5rem;">
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


          <style>
            .path-container {
              display: flex;
              align-items: flex-start;
              gap: 10px;
              flex-wrap: wrap;
            }

            .button-group {
              display: flex;
              gap: .5rem;
              height: 3rem;
              justify-content: space-between;
              align-items: stretch;
            }

            .copyable-path {
              background-color: #f5f5f5;
              padding: 3px 6px;
              border: 1px solid #e1e1e1;
              border-radius: 3px;
              font-family: monospace;
              word-break: break-all;
              transition: all 0.2s ease-in-out;
              display: inline-block;
            }

            .copyable-path:hover {
              background-color: #e9f5ff;
              border-color: #7fb5ff;
            }

            .copy-button {
              background-color: #e6e6e6;
              border: 1px solid #ccc;
              border-radius: 3px;
              padding: 4px 8px;
              cursor: pointer;
              font-size: 0.85em;
              transition: all 0.2s ease;
              white-space: nowrap;
            }

            .copy-button:hover {
              background-color: #d4d4d4;
            }

            .copy-button:active {
              background-color: #c4c4c4;
              transform: translateY(1px);
            }
          </style>

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

              // Add click event for file path button
              if (copyFileBtn) {
                copyFileBtn.addEventListener('click', function(e) {
                  e.preventDefault();
                  const textToCopy = pathElement.getAttribute('data-path');
                  copyToClipboard(textToCopy, 'Copied file path to clipboard!');
                });
              }

              // Add click event for directory path button
              if (copyDirBtn) {
                copyDirBtn.addEventListener('click', function(e) {
                  e.preventDefault();
                  // For debugging, log the exact value being attempted to copy
                  const dirPath = pathElement.getAttribute('data-dir-path');
                  console.log('Directory path before copy attempt:', dirPath);

                  if (!dirPath) {
                    console.error('Directory path is empty or undefined!');
                    alert('Error: Could not find directory path to copy');
                    return;
                  }

                  copyToClipboard(dirPath, 'Copied folder path to clipboard!');
                });
              }

              function showCopyMessage(text) {
                message.textContent = text || 'Copied to clipboard!';
                message.style.display = 'inline';
                setTimeout(function() {
                  message.style.display = 'none';
                }, 2000);
              }
            });
          </script>
        <?php endif; ?>
      </div>

      <div style="margin-top: 2em; display: flex; gap: 10px;">
        <a href="/doc/download?id=<?= $document->id; ?>&user_id=<?= $currentUserId; ?>" class="download-button">
          Download Document
        </a>

        <?php /* "Open in Explorer" button removed as it doesn't work with Docker */ ?>
      </div>
    </div>
  <?php else: ?>
    <p>Document not found.</p>
  <?php endif; ?>
  <p style="margin-top: 1em;"><a href="/?user_id=<?= $currentUserId ?>">Back to Document List</a></p>
</div>

<?php view('partials/end.php'); ?>