<div id="header">
	<div id="inner-header" class="<?php if($current == 'seed'){echo 'center';}else{echo 'fullscreen';} ?>">
		<div id="logo">
			<a href="<?php echo BYENDS_SITE_URL; ?>"><?php echo $options->title; ?></a>
		</div>
		<ul id="nav" class="clearfix">
			<li <?php if($current == 'popular') echo 'class="current"';?>>
				<a href="<?php echo BYENDS_SITE_URL; ?>popular">Popular</a>
			</li>
			<li class="<?php if($current == 'random') echo 'current';?>">
				<a href="<?php echo BYENDS_SITE_URL; ?>random">Random</a>
			</li>
		</ul>
		<ul id="nav-user" class="right dropdown-menu white">
			<?php if (null !== $widget->uid) {?>
			<li id="user-dropdown" class="dropdown-item">
				<img src="<?php echo $widget->user->avatar; ?>" />
				<a href="<?php echo $widget->user->userUrl; ?>" ><?php echo $widget->user->fullname; ?></a>
				<span class="dwn">▼</span>
				<ul class="">
					<?php if ($widget->instanceUser->pass('administrator', true)) {?>
					<li><a href="<?php echo BYENDS_ADMIN_URL; ?>" ><span class="tools-sprite tools-manage"></span>Manage</a></li>
					<?php }?>
					<li><a href="<?php echo $widget->user->userUrl; ?>"><span class="tools-sprite tools-user"></span>Profile</a></li>
					<li class="predivider"><a href="<?php echo $widget->user->userLikesUrl; ?>"><span class="tools-sprite tools-likes"></span>Likes</a></li>
					<!-- <li class="divider"><a href="<?php echo BYENDS_AUTH_SETTINGS_URL; ?>"><span class="tools-sprite tools-settings"></span>Settings</a></li> -->
					<li class="divider"><a href="<?php echo BYENDS_AUTH_SIGNOUT_URL; ?>"><span class="tools-sprite tools-signout"></span>Sign out</a></li></ul>
			</li>
			<?php 
			}
			else {
			?>
			<li>
				<a href="<?php echo BYENDS_AUTH_SIGNIN_URL; ?>"><span class="tools-sprite tools-user"></span>Sign in</a>
			</li>
			<?php if (false) { ?>
			<li><a href="<?php echo BYENDS_AUTH_SIGNUP_URL; ?>">Sign up</a></li>
			<?php } }?>
		</ul>
	</div>
</div>