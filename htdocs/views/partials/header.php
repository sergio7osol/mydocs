<header class="main-header">
    <h1><a href="/" class="main-header__title">MyDocs Document Management</a></h1>
    <div class="user-selector">

        <?php if($_SESSION['user'] ?? false) : ?>
            <div class="user-avatar">
                <div class="user-avatar__image-container">
                    <img src="/img/user-avatar-256x256.jpg" alt="User Avatar" class="user-avatar__image">
                </div>
            </div>
        <?php else : ?>
            <a href="/register" class="document-view__back-link-simple" style="color: #fff;">Register</a>
        <?php endif; ?>

        <?php foreach ($users as $user): ?>
            <div class="<?= ($currentUserId == $user->id) ? 'active' : ''; ?>">
                <a href="/?route=list&user_id=<?= $user->id ?><?= isset($currentCategory) && !empty($currentCategory) ? '&category=' . htmlspecialchars($currentCategory) : '' ?>"
                    id="user-<?= $user->id ?>"
                    class="user-button">
                    <span><?= htmlspecialchars($user->firstname) ?></span>
                    <span class="user-button__icon">
                        <?= $userDocCounts[$user->id] ?? 0 ?>
                    </span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</header>