<header class="main-header" style="background-color: #4CAF50; color: white; padding: 1em; display: flex; justify-content: space-between; align-items: center;">
    <h1 style="margin: 0; font-size: 1.8em;"><a href="/" style="color: white; text-decoration: none;">MyDocs Document Management</a></h1>
    <div class="user-selector" style="display: flex; align-items: center;">
        <?php foreach ($users as $user): ?>
            <div class="user-badge <?= ($currentUserId == $user->id) ? 'active' : ''; ?>">
                <a href="/?route=list&user_id=<?= $user->id ?><?= isset($currentCategory) && !empty($currentCategory) ? '&category=' . htmlspecialchars($currentCategory) : '' ?>"
                    id="user-<?= $user->id ?>"
                    style="color: white; text-decoration: none; margin-left: 1em;">
                    <span><?= htmlspecialchars($user->firstname) ?></span>
                    <span class="user-button__icon">
                        <?= $userDocCounts[$user->id] ?? 0 ?>
                    </span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</header>