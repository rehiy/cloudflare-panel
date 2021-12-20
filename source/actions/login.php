<?php
/*
 * login form
 */

if (isset($_POST['submit'])) {
	$user = new \Cloudflare\API\Endpoints\User($adapter);
	try {
		$user_details = $user->getUserDetails();
		setcookie('cf_email', $_POST['cf_email']);
		setcookie('cf_api_key', $_POST['cf_api_key']);
		header('location: ./');
		return;
	} catch (Exception $e) {
		echo '<p class="alert alert-danger" role="alert">' . l('An error occurred. You might have provided an error email and API key pair.') . '</p>';
		echo '<p class="alert alert-warning" role="alert">' . $e->getMessage() . '</p>';
	}
}
?>

<h1 class="login-h1 text-center"><?php echo l('Cloudflare CNAME/IP/NS Setup'); ?></h1>

<form class="form-signin text-center" method="POST" action="">
	<h1 class="h3 mb-3 font-weight-normal">
		<?php echo l('Please sign in'); ?>
	</h1>
	<input type="email" name="cf_email" class="form-control" placeholder="<?php echo l('Your email address on cloudflare.com'); ?>" required autofocus>
	<input type="password" name="cf_api_key" class="form-control" minlength="37" maxlength="37" pattern="[0-9a-fA-F]{37}" title="<?php echo l('Your global API key. NOT your password.'); ?>" placeholder="<?php echo l('Your global API key on cloudflare.com'); ?>" required>
	<button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo l('Sign in'); ?></button>
	<p class="mt-3 text-muted">
		<a href="https://support.cloudflare.com/hc/en-us/articles/200167836-Managing-API-Tokens-and-Keys#12345682">
			<?php echo l('How to get my global API key?'); ?>
		</a>
	</p>
	<p class="text-muted">
		<?php echo l('We will not store any of your Cloudflare data'); ?>
	</p>
</form>