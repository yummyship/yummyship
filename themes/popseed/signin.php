<?php require 'header.php'; ?>

<div id="container">
	<div id="content" class="clearfix">
		<div class="dialog clearfix">
			<div class="sign relative clearfix">
			    <h2>Sign In to Lovewithyummy</h2>
				<?php if($notice->have() && in_array($notice->noticeType, array('success', 'notice', 'error'))): ?>
				<div class="radius message <?php $notice->noticeType(); ?>">
				<ul>
					<?php $notice->lists(); ?>
				</ul>
				</div>
				<?php endif; ?>
			    <div class="signin">
			    	<form id="signin-form" method="post" action="<?php echo BYENDS_AUTH_SIGNIN_URL; ?>">
				    <p><input type="text" name="mail" id="mail" class="text" placeholder="Email" tabindex="1"  value="<?php echo Byends_Cookie::get('__byends_remember_mail'); ?>" /></p>
				    <p><input type="password" name="password" id="password" class="text" placeholder="Password" tabindex="2" /></p>
				    <p class="btn-area">
						<button class="btn-signin" type="submit" tabindex="4">Sign In</button>
						<input type="hidden" name="referer" value="<?php echo htmlspecialchars($widget->request->filter('trim')->referer); ?>" />
						<!-- <a class="forgot" href="<?php echo BYENDS_AUTH_FORGOT_URL; ?>">Forgot password?</a> -->
					</p>
				    </form>
			    </div>
			    <div class="signin-auth clearfix">
			    	<div class="logo-button-block"><a href="#" class="logo-btn fb"><div class="logo-outer"><span class="logo"></span></div><span>Sign in with <strong>Facebook</strong></span></a></div>
			    	<div class="logo-button-block"><a href="#" class="logo-btn google"><div class="logo-outer"><span class="logo"></span></div><span>Sign in with <strong>Google</strong></span></a></div>
			    	<div class="logo-button-block"><a href="#" class="logo-btn twitter"><div class="logo-outer"><span class="logo"></span></div><span>Sign in with <strong>Twitter</strong></span></a></div>
			    </div>
		    </div>
		    <!-- 
		    <p class="sign-bottom">
				Need an account? <a href="<?php echo BYENDS_AUTH_SIGNUP_URL; ?>">Sign up</a>
		    </p>
		     -->
		</div>
	</div>
</div>

<?php require 'footer.php'; ?>