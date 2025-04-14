<?php

view('partials/start.php', [
	'pageTitle' => $pageTitle ?? 'Registration',
	'users' => $users ?? []
]);

?>

<main class="content">
	<form class="registration-form" action="/register" method="POST">
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
            <label class="registration-form__label" for="firstname">First Name</label>
            <input class="registration-form__input" id="firstname" name="firstname" autocomplete="given-name" placeholder="Enter your first name" required type="text">

            <?php if (isset($errors['firstname'])) : ?>
                <div class="error-message">
                    <?= $errors['firstname'] ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="registration-form__field">
            <label class="registration-form__label" for="lastname">Last Name</label>
            <input class="registration-form__input" id="lastname" name="lastname" autocomplete="given-name" placeholder="Enter your last name" required type="text">

            <?php if (isset($errors['lastname'])) : ?>
                <div class="error-message">
                    <?= $errors['lastname'] ?>
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
			<button class="registration-form__button registration-form__button--primary" type="submit">Create Account</button>
		</div>
	</form>
</main>

<?php view('partials/end.php'); ?>