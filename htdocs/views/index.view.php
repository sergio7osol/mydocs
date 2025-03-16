<?php
$pageTitle = 'Document Management System';
include base_path('views/partials/start.php');

// Categories are now loaded from the controller
?>


<aside class="sidebar">
  <h2>Categories</h2>
  <ul>
    <?php
    $currentCategory = isset($_GET['category']) ? $_GET['category'] : '';

    foreach ($categories as $category): ?>
      <li>
        <a href="index.php?route=list&category=<?= urlencode($category['name']) ?>&user_id=<?= $currentUserId ?>"
          class="<?= ($currentCategory === $category['name']) ? 'active-category' : '' ?>">
          <?= htmlspecialchars($category['name']) ?>
        </a>
      </li>
    <?php endforeach; ?>
    <li class="add-category-item">
      <a href="#" id="add-category-btn">
        ➕ Category ❌
      </a>
    </li>
  </ul>
</aside>
<main class="content">
  <?php
  // Ensure we're accurately counting documents 
  $currentCategory = isset($_GET['category']) ? $_GET['category'] : '';
  $documentCount = count($documents);
  
  // Debugging
  error_log("Document count in view: " . $documentCount . " for user: " . $currentUserId . " and category: " . $currentCategory);
  ?>
  <div class="content-header">
    <h1>Documents</h1>
    <?php if (isset($_GET['category'])): ?>
      <div class="category-header">
        <h2>
          Category: <?= htmlspecialchars($_GET['category']) ?>
          <div class="document-counter">
            <div class="document-counter-inner">
              <span class="counter-number"><?= $documentCount ?></span>
              <span class="counter-text">Documents</span>
            </div>
          </div>
        </h2>
        <div class="category-actions">
          <a href="index.php?route=list&user_id=<?= $currentUserId ?>" class="btn btn-outline show-all-btn">
            📑 Show All Documents
          </a>
        </div>
      </div>
    <?php else: ?>
      <div class="category-header">
        <h2>
          All Documents
          <div class="document-counter">
            <div class="document-counter-inner">
              <span class="counter-number"><?= $documentCount ?></span>
              <span class="counter-text">Documents</span>
            </div>
          </div>
        </h2>
      </div>
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
      <a href="/doc/upload<?= isset($currentUserId) ? '?user_id=' . $currentUserId : '' ?>" class="upload-button">Upload Document</a>
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
      <?php foreach ($documents as $doc): ?>
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
            <span class="document-date"><span class="light-text">Created on:</span> <?= htmlspecialchars(!empty($doc['created_date']) ? $doc['created_date'] : $doc['upload_date']) ?></span>
            <span class="document-category"><?= htmlspecialchars($doc['category']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
      
      <?php if (isset($_GET['category'])): ?>
        <div class="category-upload-button-container">
          <a href="/doc/upload<?= isset($_GET['user_id']) ? '?user_id=' . $_GET['user_id'] : '' ?><?= isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : '' ?>" class="btn btn-primary">Upload to this category</a>
        </div>
      <?php endif; ?>
      
    <?php elseif (isset($_GET['category'])): ?>
      <div class="empty-state">
        📁
        <p>No documents found in category "<?= htmlspecialchars($_GET['category']) ?>".</p>
        <p class="sub-message">Upload a document to this category to see it here.</p>
        <a href="/doc/upload<?= isset($_GET['user_id']) ? '?user_id=' . $_GET['user_id'] : '' ?>&category=<?= htmlspecialchars($_GET['category']) ?>" class="btn btn-primary">Upload to this category</a>
      </div>
    <?php else: ?>
      <div class="empty-state">
        📄
        <p>No documents found.</p>
        <p class="sub-message">Start by uploading a document using the form below.</p>
        <a href="/doc/upload<?= isset($_GET['user_id']) ? '?user_id=' . $_GET['user_id'] : '' ?>" class="btn btn-primary">Upload a document</a>
      </div>
    <?php endif; ?>
  </div>
</main>

<!-- Category Modal -->
<div id="category-modal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Manage Categories</h2>
    
    <form id="add-category-form">
      <div class="form-group">
        <input type="text" id="new-category-name" placeholder="New category name" required>
        <button type="submit" class="btn btn-primary">Add Category</button>
      </div>
    </form>
    
    <div id="categories-list">
      <h3>Current Categories</h3>
      <ul>
        <?php foreach ($categories as $category): ?>
          <li>
            <?= htmlspecialchars($category['name']) ?>
            <button class="delete-category-btn" data-id="<?= $category['id'] ?>">🗑️</button>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<style>
  /* New styles for the upload button when documents exist */
  .category-upload-button-container {
    margin-top: 3rem;
    padding: 15px;
    text-align: center;
    border-top: 1px solid #e0e0e0;
  }
  
  .category-upload-button-container .btn-primary {
    display: inline-block;
    padding: 8px 16px;
    background-color: #4a6da7;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.3s;
  }
  
  .category-upload-button-container .btn-primary:hover {
    background-color: #3a5a8f;
  }
  
  /* Add category item styles */
  .add-category-item {
    margin-top: 20px;
    border-top: 1px solid #eee;
    padding-top: 10px;
  }
  
  .add-category-item a {
    color: #4a6da7;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  
  .add-category-item a:hover {
    color: #3a5a8f;
  }
  
  /* Modal styles */
  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
  }
  
  .modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 50%;
    max-width: 500px;
  }
  
  .close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
  }
  
  .close:hover {
    color: #333;
  }
  
  #categories-list {
    margin-top: 20px;
  }
  
  #categories-list ul {
    list-style: none;
    padding: 0;
  }
  
  #categories-list li {
    padding: 10px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .delete-category-btn {
    color: white;
    border: none;
    border-radius: 4px;
    padding: 5px 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .delete-category-btn:hover {
    background-color: #ff0000;
  }
  
  .form-group {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
  }
  
  #new-category-name {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
  }

  /* Confirmation dialog styles */
  .confirmation-dialog {
    display: none; 
    position: fixed;
    z-index: 1001;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
  }

  .confirmation-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 80%;
    max-width: 450px;
    text-align: center;
  }

  .confirmation-content h3 {
    margin-top: 0;
    color: #d32f2f;
  }

  .confirmation-content p {
    margin: 15px 0;
  }

  .confirmation-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
  }

  .btn-confirm {
    background-color: #d32f2f;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    cursor: pointer;
  }

  .btn-cancel {
    background-color: #757575;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    cursor: pointer;
  }

  .btn-confirm:hover {
    background-color: #b71c1c;
  }

  .btn-cancel:hover {
    background-color: #616161;
  }
</style>

<!-- Confirmation Dialog for Category Deletion -->
<div id="confirmation-dialog" class="confirmation-dialog">
  <div class="confirmation-content">
    <h3>Delete Category</h3>
    <p id="confirmation-message">Are you sure you want to delete this category?</p>
    <div class="confirmation-actions">
      <button id="confirm-delete" class="btn-confirm">Delete</button>
      <button id="cancel-delete" class="btn-cancel">Cancel</button>
    </div>
  </div>
</div>

<script>
  // Show/hide the category modal
  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('category-modal');
    const addCategoryBtn = document.getElementById('add-category-btn');
    const closeBtn = document.querySelector('.close');
    
    addCategoryBtn.addEventListener('click', function(e) {
      e.preventDefault();
      modal.style.display = 'block';
    });
    
    closeBtn.addEventListener('click', function() {
      modal.style.display = 'none';
    });
    
    window.addEventListener('click', function(e) {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });
    
    // Add new category
    const addCategoryForm = document.getElementById('add-category-form');
    
    addCategoryForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const categoryName = document.getElementById('new-category-name').value;
      if (!categoryName.trim()) return;
      
      // Send AJAX request to add category
      fetch('index.php?route=add_category', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'name=' + encodeURIComponent(categoryName),
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Refresh the page to show the new category
          window.location.reload();
        } else {
          alert(data.error || 'Error adding category');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the category');
      });
    });
    
    // Handle confirmation dialog
    const confirmationDialog = document.getElementById('confirmation-dialog');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const confirmationMessage = document.getElementById('confirmation-message');
    
    let categoryToDelete = null;
    
    // Cancel delete
    cancelDeleteBtn.addEventListener('click', function() {
      confirmationDialog.style.display = 'none';
      categoryToDelete = null;
    });
    
    // Confirm delete
    confirmDeleteBtn.addEventListener('click', function() {
      if (!categoryToDelete) return;
      
      // Send AJAX request to delete category
      fetch('index.php?route=delete_category', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + encodeURIComponent(categoryToDelete),
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Refresh the page to update the categories list
          window.location.reload();
        } else {
          alert(data.error || 'Error deleting category');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the category');
      });
      
      // Hide confirmation dialog
      confirmationDialog.style.display = 'none';
    });
    
    // Click outside to close confirmation dialog
    window.addEventListener('click', function(e) {
      if (e.target === confirmationDialog) {
        confirmationDialog.style.display = 'none';
        categoryToDelete = null;
      }
    });
    
    // Delete category - show confirmation dialog with document count
    const deleteBtns = document.querySelectorAll('.delete-category-btn');
    
    deleteBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const categoryId = this.getAttribute('data-id');
        
        // First get the document count for this category
        fetch('index.php?route=get_category_count', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'id=' + encodeURIComponent(categoryId),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const categoryName = data.name;
            const documentCount = data.count;
            
            // Update confirmation message with document count
            let message = `Are you sure you want to delete the category "${categoryName}"?`;
            
            if (documentCount > 0) {
              message += `<br><br>This category contains <strong>${documentCount} document${documentCount !== 1 ? 's' : ''}</strong> that will also be deleted.`;
            }
            
            confirmationMessage.innerHTML = message;
            categoryToDelete = categoryId;
            confirmationDialog.style.display = 'block';
          } else {
            alert(data.error || 'Error getting category information');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while getting category information');
        });
      });
    });
  });
  
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