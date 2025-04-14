<?php

view('partials/start.php', [
	'pageTitle' => $pageTitle ?? 'Login'
]);

?>

<main class="content">
  <h2>Log In</h2>

	<form class="registration-form" action="/sessions" method="POST">
        <div class="registration-form__field">
            <label class="registration-form__label" for="email">Email Address</label>
            <input class="registration-form__input" id="email" name="email" autocomplete="email" placeholder="Enter your email" required type="email">

            <?php if (isset($errors['email'])) : ?>
                <div class="error-message">
                    <?= $errors['email'] ?>
                </div>
            <?php endif; ?>

        </div>

		<div class="registration-form__field">
			<label class="registration-form__label" for="password">Password</label>
			<input class="registration-form__input" id="password" name="password" autocomplete="current-password " placeholder="Enter your password" required type="password">

			<?php if (isset($errors['password'])) : ?>
				<div class="error-message">
					<?= $errors['password'] ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="registration-form__actions">
			<button class="registration-form__button registration-form__button--primary" type="submit">Login</button>
		</div>
	</form>
</main>

<?php view('partials/end.php'); ?>