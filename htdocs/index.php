<?php

include 'utils.php';
include 'Database.php';
// include 'router.php';

$config = include 'config.php';

$db = new Database($config['database']);

$count = $_GET['count'];
$query = "SELECT * FROM detailed_date_info where count = :count";

$dates = $db->query($query, [':count' => $count])->fetchAll();

// dd($dates);

echo '<ol>';
foreach ($dates as $date) {
    echo '<li>' . $date['date'] . '</li>';
}
echo '</ol>';

// dd($count);