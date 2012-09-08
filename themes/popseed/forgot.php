<?php require 'header.php'; ?>

<div id="container">
	<div id="content" class="clearfix">
		<div class="dialog sign clearfix">
		    <h2>Forgot Password</h2>
		    <div class="forgot">
		    	<form id="forgot-form" method="post" action="<?php echo BYENDS_AUTH_FORGOT_URL; ?>">
				<p>
					<input type="text" name="mail" id="mail" class="text" placeholder="Email" tabindex="1" />
					<button class="btn-forgot" type="submit" tabindex="2">Reset Password</button>
				</p>
				</form>
		    </div>
		</div>
	</div>
</div>

<?php require 'footer.php'; ?>