<!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($pageTitle) ?></title>
	<link rel="stylesheet" href="/base.css">
	<?php if (isset($styles)) echo $styles; ?>
</head>

<body>
	<div class="main-container">
		<?php view('partials/header.php', [
			'users' => $users ?? [],
			'currentUserId' => $currentUserId ?? null,
			'currentCategory' => $currentCategory ?? null,
			'userDocCounts' => $userDocCounts ?? []
		]) ?>

		<main class="main-content">