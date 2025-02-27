<header class="main-header">
    <h1 style="margin: 0; font-size: 1.8em;"><a href="index.php" style="color: white; text-decoration: none;">MyDocs Document Management</a></h1>
    <div class="user-selector" style="display: flex; gap: 15px;">
        <?php
        $currentUserId = isset($_GET['user_id']) ? $_GET['user_id'] : 1;

        try {
            $users = User::getAll();

            // Get document counts per user
            $userDocCounts = [];
            foreach ($users as $user) {
                try {
                    $docs = Document::getAll($user->id);
                    $userDocCounts[$user->id] = count($docs);
                } catch (Exception $e) {
                    $userDocCounts[$user->id] = 0;
                }
            }
        } catch (Exception $e) {
            $users = [
                new User(1, 'sergey@example.com', 'Sergey', 'Osokin'),
                new User(2, 'galina@example.com', 'Galina', 'Treneva')
            ];
            $userDocCounts = [1 => 0, 2 => 0];
        }

        foreach ($users as $user):
        ?>
            <div>
                <a href="index.php?route=list&user_id=<?= $user->id ?><?= isset($_GET['category']) && !empty($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : '' ?>"
                    id="user-<?= $user->id ?>"
                    class="user-button <?= ($currentUserId == $user->id) ? 'active' : ''; ?>"
                    style="<?= ($currentUserId == $user->id) ? 'background-color: #28a745; color: white; font-weight: bold;' : 'background-color: #6c757d; color: white;' ?>">
                    <span><?= htmlspecialchars($user->firstname) ?></span>
                    <span class="user-button__icon">
                        <?= $userDocCounts[$user->id] ?? 0 ?>
                    </span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</header>