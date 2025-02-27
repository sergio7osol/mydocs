<?php
$pageTitle = isset($pageTitle) ? $pageTitle . ' - Documents Management' : 'Documents Management';
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/public/base.css">
    <?php if (isset($styles)) echo $styles; ?>
</head>

<body>
    <?php include 'header.php' ?>

    <main class="main-content">