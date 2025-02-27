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
                <a href="javascript:void(0)"
                    id="user-<?= $user->id ?>"
                    class="user-button <?= ($currentUserId == $user->id) ? 'active' : ''; ?>"
                    onclick="changeUser(<?= $user->id ?>)"
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

<script>
    function changeUser(userId) {
        // Get current URL
        let currentUrl = window.location.href;

        // Remove any existing onload parameters that might interfere
        currentUrl = currentUrl.replace(/[?&]onload=[^&]*/, '');

        // Regular expression to check if user_id is already in the URL
        let userIdRegex = /([?&])user_id=([^&]*)/;
        let match = currentUrl.match(userIdRegex);

        if (match) {
            // Replace existing user_id parameter
            let oldValue = match[0];
            let newValue = match[1] + 'user_id=' + userId;
            currentUrl = currentUrl.replace(oldValue, newValue);
        } else {
            // Add user_id parameter
            if (currentUrl.indexOf('?') !== -1) {
                currentUrl += '&user_id=' + userId;
            } else {
                currentUrl += '?user_id=' + userId;
            }
        }

        // Navigate to the updated URL
        window.location.href = currentUrl;
    }

    // Make sure this script only runs once the page is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Add click handlers to each user button for better mobile experience
        let userButtons = document.querySelectorAll('.user-button');
        userButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                let userId = this.id.replace('user-', '');
                changeUser(userId);
            });
        });
    });
</script>