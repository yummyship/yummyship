<?php require 'header.php'; ?>

<div id="page">
	<div id="page-box" class="clearfix">
		<div class="logo">
			<a href="<?php echo BYENDS_SITE_URL; ?>"><?php echo $options->title; ?></a>
		</div>
		<div class="sign">
			<div class="oauth">
				<div class="logo-button-block"><span class="logo-btn fb"><div class="logo-outer"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/facebook_32.png" /></div><span>Sign in with <strong>Facebook</strong></span></span></div>
			    <div class="logo-button-block"><span class="logo-btn twitter"><div class="logo-outer"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/twitter_48.png" /></div><span>Sign in with <strong>Twitter</strong></span></span></div>
			</div>
			<div class="separate">Or with Email</div>
			<div class="sign-box relative">
				<?php if($notice->have() && in_array($notice->noticeType, array('success', 'notice', 'error'))): ?>
				<div class="radius message <?php $notice->noticeType(); ?>">
				<ul>
					<?php $notice->lists(); ?>
				</ul>
				</div>
				<?php endif; ?>
		    	<form id="signin-form" method="post" action="<?php echo BYENDS_AUTH_SIGNIN_URL; ?>">
			    <p class="relative">
			    	<label for="mail">Email</label>
			    	<input type="text" name="mail" id="mail" class="ipt-medium text-34" tabindex="1"  value="<?php echo Byends_Cookie::get('__byends_remember_mail'); ?>" />
			    </p>
			    <p class="relative">
			    	<label for="password">Password</label>
			    	<input type="password" name="password" id="password" class="ipt-medium text-34" tabindex="2" />
			    </p>
			    <p class="btn-area">
					<input class="btn-medium" type="submit" tabindex="4" value="Sign In" />
					<input type="hidden" name="referer" value="<?php echo htmlspecialchars($widget->request->filter('trim')->referer); ?>" />
					<?php if (false) {?>
					<a class="forgot" href="<?php echo BYENDS_AUTH_FORGOT_URL; ?>">Forgot password ?</a>
					<?php }?>
				</p>
			    </form>
			    <?php if (false) {?>
			    <p class="signin-bottom">
					<a href="<?php echo BYENDS_AUTH_SIGNUP_URL; ?>">Need an account ? Sign up</a>
			    </p>
			    <?php }?>
		    </div>
		</div>
		
	</div>
</div>

<?php require 'footer.php'; ?>