<?php

view('partials/start.php', [
  'pageTitle' => $pageTitle,
  'users' => $users,
  'currentUserId' => $currentUserId,
  'currentCategory' => $currentCategory ?? null,
  'userDocCounts' => $userDocCounts
]);
?>
<aside class="sidebar">
  <h2 class="sidebar__title">Categories</h2>
  <ul class="category-tree">
    <?php
    /**
     * Recursive function to render the category tree
     */
    function renderCategoryTree($categories, $parentId = null, $level = 0, $params = [])
    {
      // Debug at beginning of function
      if ($level === 0) {
        error_log("In renderCategoryTree - selectedCategoryId: " . var_export($params['selectedCategoryId'], true));
        error_log("In renderCategoryTree - currentCategory: " . var_export($params['currentCategory'], true));
      }

      foreach ($categories as $category):
        // Check if this category has children
        $hasChildren = isset($category['children']) && count($category['children']) > 0;

        // Determine if this category should be expanded by default
        $isExpanded = true; // Default to expanded

        if ($hasChildren) {
          // Also expand if the current selection is a descendant of this category
          if ($params['selectedCategoryId']) {
            foreach ($categories as $cat) {
              if (
                $cat['id'] == $params['selectedCategoryId'] &&
                isset($cat['path']) &&
                strpos($cat['path'], $category['id'] . '/') === 0
              ) {
                $isExpanded = true;
              }
            }
          }
        }

        error_log("selectedCategoryId: " . var_export($params['selectedCategoryId'], true));
        error_log("category['id']: " . var_export($category['id'], true));
        error_log("selectedCategoryId is set: " . var_export(isset($params['selectedCategoryId']), true));
        error_log("currentCategory: " . var_export($params['currentCategory'], true));
        error_log("category['name']: " . var_export($category['name'], true));

        // Determine if this category is active - compare both ID and name
        $isActive = (int)$params['selectedCategoryId'] === (int)$category['id'] ||
          (!empty($params['currentCategory']) && strcasecmp($params['currentCategory'], $category['name']) === 0);
    ?>
        <li class="category-tree__item <?= isset($category['parent_id']) && !empty($category['parent_id']) ? 'category-tree__subcategory' : '' ?>"
          data-id="<?= $category['id'] ?>"
          data-level="<?= $level ?>">

          <div class="category-tree__item-content">
            <?php if ($hasChildren): ?>
              <span class="category-tree__toggle <?= $isExpanded ? 'category-tree__toggle--expanded' : 'category-tree__toggle--collapsed' ?>"
                data-id="<?= $category['id'] ?>">
                <?= $isExpanded ? '▼' : '►' ?>
              </span>
            <?php endif; ?>

            <a href="/?category=<?= urlencode(htmlspecialchars($category['name'])) ?>&user_id=<?= $params['currentUserId'] ?: 1 ?>"
              class="category-tree__link <?= $isActive ? 'category-tree__link--active' : '' ?>">
              <?= htmlspecialchars($category['name']) ?>
              <span class="category-count"><?= isset($params['categoryDocCounts'][$category['id']]) ? $params['categoryDocCounts'][$category['id']] : 0 ?></span>
            </a>
          </div>

          <?php if ($hasChildren): ?>
            <ul class="category-tree__subcategories <?= $isExpanded ? 'category-tree__subcategories--expanded' : 'category-tree__subcategories--collapsed' ?>">
              <?php renderCategoryTree($category['children'], null, $level + 1, $params); ?>
            </ul>
          <?php endif; ?>
        </li>
    <?php endforeach;
    }

    // Start rendering from root categories (parent_id is NULL)
    // Sort categories to ensure consistent ordering - place all root categories first
    usort($categories, function($a, $b) {
      // If one is a root category and the other isn't, the root category comes first
      if (empty($a['parent_id']) && !empty($b['parent_id'])) {
        return -1; // a should come before b
      }
      if (!empty($a['parent_id']) && empty($b['parent_id'])) {
        return 1; // b should come before a
      }

      // If they're both root categories or both subcategories, sort by display_order
      $orderA = isset($a['display_order']) ? (int)$a['display_order'] : 0;
      $orderB = isset($b['display_order']) ? (int)$b['display_order'] : 0;

      if ($orderA !== $orderB) {
        return $orderA - $orderB;
      }

      // Finally sort by name if display order is the same
      return strcmp($a['name'], $b['name']);
    });

    renderCategoryTree($categories, null, 0, [
      'selectedCategoryId' => $selectedCategoryId,
      'currentCategory' => $currentCategory,
      'currentUserId' => $currentUserId,
      'categoryDocCounts' => $categoryDocCounts
    ]);
    ?>
    <li class="category-tree__add-item">
      <a href="#" id="add-category-btn" class="category-tree__add-link">
        <span class="purple-plus">➕</span> Category <span class="delete-icon">❌</span>
      </a>
    </li>
  </ul>
</aside>

<main class="content">
  <?php
  $documentCount = count($documents);

  error_log("Document count in view: " . $documentCount . " for user: " . $currentUserId . " and category: " . ($currentCategory ?? 'All'));
  ?>
  <div class="content-header">
    <h1>Documents</h1>
    <?php if (isset($currentCategory) && !empty($currentCategory)): ?>
      <div class="category-header">
        <h2>
          Category: <?= htmlspecialchars($currentCategory) ?>
          <div class="document-counter">
            <div class="document-counter-inner">
              <span class="counter-number"><?= $documentCount ?></span>
              <span class="counter-text">DOCUMENTS</span>
            </div>
          </div>
        </h2>
        <a href="/?route=list&user_id=<?= $currentUserId ?>" class="show-all-btn">
          📄 Show All Documents
        </a>
      </div>
    <?php else: ?>
      <div class="category-header">
        <h2>
          All Documents
          <div class="document-counter">
            <div class="document-counter-inner">
              <span class="counter-number"><?= $documentCount ?></span>
              <span class="counter-text">DOCUMENTS</span>
            </div>
          </div>
        </h2>
      </div>
    <?php endif; ?>
    <div class="search-container">
      <form action="/" method="GET" class="search-form">
        <input type="hidden" name="route" value="list">
        <input type="hidden" name="user_id" value="<?= $currentUserId ?>">
        <?php if (isset($currentCategory) && !empty($currentCategory)): ?>
          <input type="hidden" name="category" value="<?= htmlspecialchars($currentCategory) ?>">
        <?php endif; ?>
        <input type="text" name="search" placeholder="Search documents..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" class="form-control">
        <button type="submit" class="btn btn--primary">Search</button>
        <a href="/document/create<?= isset($currentUserId) ? '?user_id=' . $currentUserId : '' ?><?= isset($currentCategory) && !empty($currentCategory) ? '&category=' . urlencode($currentCategory) : '' ?>" class="upload-button">Upload Document</a>
      </form>
    </div>
  </div>

  <?php if (empty($documents)): ?>
    <?php if (isset($currentCategory) && !empty($currentCategory)): ?>
      <div class="empty-state">
        <div class="document-icon">📄</div>
        <p>No documents found in category "<?= htmlspecialchars($currentCategory) ?>".</p>
        <p class="sub-message">Upload a document to this category to see it here.</p>
        <a href="/document/create<?= isset($currentUserId) ? '?user_id=' . $currentUserId : '' ?>&category=<?= urlencode($currentCategory) ?>" class="btn-primary">Upload to this category</a>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div class="document-icon">📄</div>
        <p>No documents found.</p>
        <p class="sub-message">Start by uploading a document using the form below.</p>
        <a href="/document/create<?= isset($currentUserId) ? '?user_id=' . $currentUserId : '' ?>" class="btn-primary">Upload a document</a>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div class="document-list" id="documentList">
      <?php foreach ($documents as $doc): ?>
        <div class="document-item" data-id="<?= $doc['id'] ?>" data-user-id="<?= $currentUserId ?>">
          <div class="document-item-content" onclick="window.location='/document/?id=<?= $doc['id'] ?>&user_id=<?= $currentUserId ?>'">
            <div class="document-icon">
              <?php
              $fileType = $doc['file_type'] ?? '';
              $fileSymbol = '📄'; // Default document symbol

              if (strpos($fileType, 'pdf') !== false) {
                $fileSymbol = '📕'; // PDF symbol
              } elseif (strpos($fileType, 'image') !== false) {
                $fileSymbol = '🖼️'; // Image symbol
              } elseif (strpos($fileType, 'word') !== false || strpos($fileType, 'document') !== false) {
                $fileSymbol = '📝'; // Word document symbol
              } elseif (strpos($fileType, 'excel') !== false || strpos($fileType, 'spreadsheet') !== false) {
                $fileSymbol = '📊'; // Excel/spreadsheet symbol
              } elseif (strpos($fileType, 'text') !== false) {
                $fileSymbol = '📃'; // Text file symbol
              }
              ?>
              <span class="document-icon-symbol"><?= $fileSymbol ?></span>
            </div>
            <div class="document-details">
              <h3 class="document-title"><?= htmlspecialchars($doc['title']) ?></h3>
              <p class="document-description"><?= htmlspecialchars($doc['description'] ?? '') ?></p>
              <div class="document-meta">
                <span class="document-date">
                  📅 <?= date('M d, Y', strtotime($doc['upload_date'])) ?>
                </span>
                <span class="document-category">
                  📁 <?= htmlspecialchars($doc['category_path'] ?? ($doc['category_name'] ?? 'Uncategorized')) ?>
                </span>
                <span class="document-size">
                  📦 <?= number_format($doc['file_size'] / 1024, 2) ?> KB
                </span>
              </div>
            </div>
          </div>
          <div class="document-item__actions">
            <a href="/document/edit?id=<?= $doc['id'] ?>&user_id=<?= $currentUserId ?>" class="document-item__btn document-item__btn--edit">
              ✏️
            </a>
            <form method="POST" action="/document" onsubmit="return confirm('Are you sure you want to delete this document?');" onclick="event.stopPropagation();">
              <input type="hidden" name="_method" value="DELETE">
              <input type="hidden" name="id" value="<?= $doc['id'] ?>">
              <input type="hidden" name="user_id" value="<?= $currentUserId ?>">
              <?php if (isset($currentCategory)): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($currentCategory) ?>">
              <?php endif; ?>
              <button type="submit" class="document-item__btn document-item__btn--delete" title="Delete document" onclick="event.stopPropagation();">🗑️</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="document-upload">
      <a href="/document/create<?= isset($currentUserId) ? '?user_id=' . $currentUserId : '' ?>&category=<?= urlencode($currentCategory) ?>" class="btn btn--primary">
        ➕ Upload New Document
      </a>
    </div>
  <?php endif; ?>
</main>

<!-- Category Management Modal -->
<div id="category-modal" class="modal">
  <div class="modal__content">
    <span class="modal__close">❌</span>
    <h2 class="modal__title">Manage Categories</h2>

    <form id="add-category-form">
      <div class="form-group">
        <input type="text" id="new-category-name" placeholder="New category name" required class="form-control">
        <select id="parent-category-select" class="form-select">
          <option value="">-- Root Category --</option>
          <?php foreach ($allCategories as $category): ?>
            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn--primary">➕ Save Category</button>
      </div>
    </form>

    <div class="categories-list">
      <h3 class="categories-list__title">Current Categories</h3>
      <ul class="categories-list__items">
        <?php foreach ($allCategories as $category): ?>
          <li class="categories-list__item">
            <?php
            // Show indentation based on level
            if (isset($category['parent_id']) && $category['parent_id']) {
              // Find parent to display as reference
              $parentName = "";
              foreach ($allCategories as $parentCat) {
                if ($parentCat['id'] == $category['parent_id']) {
                  $parentName = $parentCat['name'];
                  break;
                }
              }
              echo htmlspecialchars($category['name']);
              echo ' <span class="categories-list__parent-info">(under: ' . htmlspecialchars($parentName) . ')</span>';
            } else {
              echo '<strong>' . htmlspecialchars($category['name']) . '</strong>';
            }
            ?>
            <button class="categories-list__delete-btn" data-id="<?= $category['id'] ?>">🗑️</button>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<div class="confirmation-dialog" id="category-delete-confirmation">
  <div class="confirmation-dialog__content">
    <h3 class="confirmation-dialog__title">Confirm Category Deletion</h3>
    <p class="confirmation-dialog__message">Are you sure you want to delete this category?</p>
    <p id="category-delete-warning" class="confirmation-dialog__message"></p>
    <div class="confirmation-dialog__actions">
      <button id="confirm-category-delete" class="btn btn--confirm">➕ Delete</button>
      <button id="cancel-category-delete" class="btn btn--cancel">❌ Cancel</button>
    </div>
  </div>
</div>

<script>
  // Show/hide the category modal
  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('category-modal');
    const addCategoryBtn = document.getElementById('add-category-btn');
    const closeBtn = document.querySelector('.modal__close');

    // Event listeners for category tree toggle icons
    const toggleIcons = document.querySelectorAll('.category-tree__toggle');

    toggleIcons.forEach(icon => {
      icon.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const categoryId = this.getAttribute('data-id');
        const isExpanded = this.classList.contains('category-tree__toggle--expanded');

        // Toggle the icon appearance
        if (isExpanded) {
          this.classList.remove('category-tree__toggle--expanded');
          this.classList.add('category-tree__toggle--collapsed');
          this.innerHTML = '►';
        } else {
          this.classList.remove('category-tree__toggle--collapsed');
          this.classList.add('category-tree__toggle--expanded');
          this.innerHTML = '▼';
        }

        // Toggle the subcategory visibility
        const parentItem = this.closest('.category-tree__item');
        const subcategoriesList = parentItem.querySelector('.category-tree__subcategories');

        if (subcategoriesList) {
          if (isExpanded) {
            subcategoriesList.classList.remove('category-tree__subcategories--expanded');
            subcategoriesList.classList.add('category-tree__subcategories--collapsed');
          } else {
            subcategoriesList.classList.remove('category-tree__subcategories--collapsed');
            subcategoriesList.classList.add('category-tree__subcategories--expanded');
          }
        }
      });
    });

    addCategoryBtn.addEventListener('click', function() {
      modal.style.display = 'block';
    });

    closeBtn.addEventListener('click', function() {
      modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    });

    // Add category form submission
    const addCategoryForm = document.getElementById('add-category-form');
    addCategoryForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const categoryName = document.getElementById('new-category-name').value;
      const parentCategoryId = document.getElementById('parent-category-select').value;

      // Add the category via AJAX
      fetch('/api/categories', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            name: categoryName,
            parent_id: parentCategoryId || null
          }),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Reload the page to show the new category
            window.location.reload();
          } else {
            alert('Error adding category: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while adding the category.');
        });
    });

    // Delete category confirmation
    const deleteCategoryBtns = document.querySelectorAll('.categories-list__delete-btn');
    const categoryDeleteConfirmation = document.getElementById('category-delete-confirmation');
    const categoryDeleteWarning = document.getElementById('category-delete-warning');
    const confirmCategoryDelete = document.getElementById('confirm-category-delete');
    const cancelCategoryDelete = document.getElementById('cancel-category-delete');

    let categoryIdToDelete = null;

    deleteCategoryBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const categoryId = this.getAttribute('data-id');
        categoryIdToDelete = categoryId;

        // First get the document count for this category
        fetch(`/api/categories/${categoryId}/documents/count`)
          .then(response => response.json())
          .then(data => {
            let warningMessage = '';

            if (data.count > 0) {
              warningMessage = `This will also delete ${data.count} document(s) in this category.`;
            }

            if (data.subcategories > 0) {
              warningMessage += ` This will also delete ${data.subcategories} subcategories and all their documents.`;
            }

            categoryDeleteWarning.textContent = warningMessage || 'No documents will be affected.';
            categoryDeleteConfirmation.style.display = 'block';
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while checking category documents.');
          });
      });
    });

    confirmCategoryDelete.addEventListener('click', function() {
      if (categoryIdToDelete) {
        fetch(`/api/categories/${categoryIdToDelete}`, {
            method: 'DELETE',
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              window.location.reload();
            } else {
              alert('Error deleting category: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the category.');
          });
      }

      categoryDeleteConfirmation.style.display = 'none';
    });

    cancelCategoryDelete.addEventListener('click', function() {
      categoryDeleteConfirmation.style.display = 'none';
    });
  });

  function changeUser(userId) {
    // Update user input
    document.getElementById('userInput').value = userId;

    // Submit form
    document.getElementById('userForm').submit();
  }
</script>

<?php view('partials/end.php'); ?>