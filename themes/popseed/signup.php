<?php require 'header.php'; ?>

<div id="page">
	<div id="page-box" class="clearfix">
		<div class="sign signup">
			<h2>Create Yummyship Account</h2>
			<div class="sign-box relative">
				<?php if($notice->have() && in_array($notice->noticeType, array('success', 'notice', 'error'))): ?>
				<div class="radius message <?php $notice->noticeType(); ?>">
				<ul>
					<?php $notice->lists(); ?>
				</ul>
				</div>
				<?php endif; ?>
		    	<form id="signup-form" method="post">
			    <p class="relative">
			    	<label for="mail">Email</label>
			    	<input type="text" name="mail" id="mail" class="ipt-medium text-34" value="<?php echo Byends_Cookie::get('__byends_remember_signup_mail', @$_SESSION['__byends_oAuth_mail']); ?>" <?php if($current=='auth_sign_fbcallback'){?>readonly="readonly"<?php }?> tabindex="1" />
			    </p>
			    <p class="relative">
			    	<label for="fullname">Full Name</label>
			    	<input type="text" name="fullname" id="fullname" class="ipt-medium text-34" value="<?php echo Byends_Cookie::get('__byends_remember_signup_fullname', @$_SESSION['__byends_oAuth_fullname']); ?>" maxlength="30" tabindex="2" />
			    </p>
			    <p class="relative">
			    	<label for="username">Username</label>
			    	<input type="text" name="username" id="username" class="ipt-medium text-34" value="<?php echo Byends_Cookie::get('__byends_remember_signup_username', @$_SESSION['__byends_oAuth_username']); ?>" maxlength="25" tabindex="3" />
			    	<span class="username-usage"><?php echo $options->domain.$subAbsolutePath; ?>/<span id="profile-url"><?php echo Byends_Cookie::get('__byends_remember_signup_username', (@$_SESSION['__byends_oAuth_username'] ? @$_SESSION['__byends_oAuth_username'] : 'username') ); ?></span></span>
			    </p>
			    <p class="btn-area">
					<input class="btn-medium" name="signup" type="submit" tabindex="4" value="Create Account" />
					<input type="hidden" name="code" value="<?php echo @$_SESSION['__byends_oAuth_code']; ?>" />
				</p>
			    </form>
		    </div>
		</div>
		
	</div>
</div>

<?php require 'footer.php'; ?>