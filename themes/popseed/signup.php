<?php require 'header.php'; ?>

<div id="container">
	<div id="content" class="clearfix">
		<div class="dialog clearfix">
		    <div class="sign clearfix">
		    	<h2>Sign Up to Lovewithyummy</h2>
				<div class="signin">
			    	<form id="signin-form" method="post" action="<?php echo BYENDS_AUTH_SIGNIN_URL; ?>">
			    	<p><input type="text" name="username" id="username" class="text" placeholder="Username" tabindex="1" /></p>
				    <p><input type="text" name="mail" id="mail" class="text" placeholder="Email" tabindex="2" /></p>
				    <p><input type="text" name="password" id="password" class="text" placeholder="Password" tabindex="3" /></p>
					<p><input type="text" name="repassword" id="repassword" class="text" placeholder="Repassword" tabindex="4" /></p>
				    <p class="btn-area">
						<button class="btn-signin" type="submit" tabindex="5">Sign Up</button>
					</p>
				    </form>
			    </div>
			    <div class="signin-auth clearfix">
			    	<div class="logo-button-block"><a href="#" class="logo-btn fb"><div class="logo-outer"><span class="logo"></span></div><span>Sign in with <strong>Facebook</strong></span></a></div>
			    	<div class="logo-button-block"><a href="#" class="logo-btn google"><div class="logo-outer"><span class="logo"></span></div><span>Sign in with <strong>Google</strong></span></a></div>
			    	<div class="logo-button-block"><a href="#" class="logo-btn twitter"><div class="logo-outer"><span class="logo"></span></div><span>Sign in with <strong>Twitter</strong></span></a></div>
			    </div>
		    </div>
		    <p class="sign-bottom">
				Been here before? <a href="<?php echo BYENDS_AUTH_SIGNIN_URL; ?>">Sign In</a>
			</p>
		</div>
	</div>
</div>

<?php require 'footer.php'; ?>