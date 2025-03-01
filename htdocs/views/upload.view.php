<?php

$pageTitle = 'Upload Document';
include 'partials/start.php';
$currentUserId = isset($_GET['user_id']) ? $_GET['user_id'] : 1;

?>

<div class="container" style="padding: 2em; max-width: 800px; margin: 0 auto;">
  <?php if (isset($message)) { ?>
    <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?>">
      <?= htmlspecialchars($message); ?>
    </div>
  <?php } ?>
  
  <div class="card" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; background-color: white; padding: 0; margin-bottom: 30px;">
    <div class="card-header" style="background-color: #4a6da7; color: white; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center;">
      <h2 style="margin: 0; font-size: 1.5rem;">Upload New Document</h2>
      <a href="index.php?route=list&user_id=<?= $currentUserId ?>" class="btn" style="background-color: white; color: #4a6da7; border: none; padding: 8px 15px; border-radius: 4px; font-weight: bold; text-decoration: none;">
        Back to Document List
      </a>
    </div>
    
    <div class="card-body" style="padding: 25px;">
      <form action="index.php?route=upload_post&user_id=<?= $currentUserId ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group" style="margin-bottom: 20px;">
          <label for="title" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Document Title:</label>
          <input type="text" name="title" id="title" required style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; transition: border-color 0.2s, box-shadow 0.2s;" 
                 onFocus="this.style.borderColor='#4a6da7'; this.style.boxShadow='0 0 0 3px rgba(74, 109, 167, 0.1)';" 
                 onBlur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
          <label for="document" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Select document to upload:</label>
          <div style="border: 1px dashed #ccc; background: #f9f9f9; padding: 15px; border-radius: 4px; text-align: center;">
            <input type="file" name="document" id="document" required accept=".pdf,.doc,.docx,.txt" style="width: 100%;">
            <div style="margin-top: 10px; font-size: 13px; color: #666;">
              <span style="background: #eee; padding: 3px 6px; border-radius: 3px; margin-right: 5px;">PDF</span>
              <span style="background: #eee; padding: 3px 6px; border-radius: 3px; margin-right: 5px;">DOC</span>
              <span style="background: #eee; padding: 3px 6px; border-radius: 3px; margin-right: 5px;">DOCX</span>
              <span style="background: #eee; padding: 3px 6px; border-radius: 3px;">TXT</span>
              <span style="margin-left: 10px;">Max: 15MB</span>
            </div>
          </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
          <label for="category" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Category:</label>
          <select name="category" id="category" required style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill=\"%23333\" height=\"24\" viewBox=\"0 0 24 24\" width=\"24\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M7 10l5 5 5-5z\"/></svg>'); background-repeat: no-repeat; background-position: right 10px center; transition: border-color 0.2s, box-shadow 0.2s;"
                 onFocus="this.style.borderColor='#4a6da7'; this.style.boxShadow='0 0 0 3px rgba(74, 109, 167, 0.1)';" 
                 onBlur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
            <option value="Personal">Personal</option>
            <option value="Work">Work</option>
            <option value="Others">Others</option>
          </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
          <label for="created_date" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Document Creation Date (optional):</label>
          <input type="date" name="created_date" id="created_date" style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; transition: border-color 0.2s, box-shadow 0.2s;" 
                 onFocus="this.style.borderColor='#4a6da7'; this.style.boxShadow='0 0 0 3px rgba(74, 109, 167, 0.1)';" 
                 onBlur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
          <small style="color: #666; display: block; margin-top: 8px; font-style: italic;">The date when this document was originally created (not when you're uploading it)</small>
        </div>
        
        <div class="form-group" style="margin-bottom: 25px;">
          <label for="description" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Description (optional):</label>
          <textarea name="description" id="description" style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; min-height: 120px; resize: vertical; transition: border-color 0.2s, box-shadow 0.2s;" 
                    onFocus="this.style.borderColor='#4a6da7'; this.style.boxShadow='0 0 0 3px rgba(74, 109, 167, 0.1)';" 
                    onBlur="this.style.borderColor='#ddd'; this.style.boxShadow='none';"></textarea>
        </div>
        
        <input type="hidden" name="user_id" value="<?= $currentUserId ?>">
        
        <div style="text-align: center; margin-top: 30px;">
          <button type="submit" style="background-color: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 4px; font-size: 18px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 6px rgba(40, 167, 69, 0.2);" 
                  onmouseover="this.style.backgroundColor='#218838'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 8px rgba(40, 167, 69, 0.3)';" 
                  onmouseout="this.style.backgroundColor='#28a745'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(40, 167, 69, 0.2)';">
            Upload Document
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'partials/end.php'; ?>
