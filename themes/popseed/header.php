<!DOCTYPE html>
<html>
<head>
<title><?php 
if( $current == 'index' || $current == 'popular' ) {
	echo htmlspecialchars( $options->title ).' - '.$options->description;
}
else if( $current == $options->seed || $current == 'zoom' ) { 
	echo htmlspecialchars( $content['title'].' - '.$options->title );
}
else if( $current == '404' ) {
	echo '404';
}
?></title>
<meta charset="UTF-8">
<?php if( $current <> '404' ) {?>
<meta name="keywords" content="<?php 
if( $current == 'index' || $current == 'popular' ) {
	echo $options->keywords;
}
else if( $current == $options->seed || $current == 'zoom') {
	echo $content['tagNameStr'] ? $content['tagNameStr'] : $options->keywords;
}
?>">
<meta name="description" content="<?php 
if( $current == 'index' || $current == 'popular' ) {
	echo $options->description;
}
else if( $current == $options->seed || $current == 'zoom') {
	echo $content['stripBrief'] ? Byends_Paragraph::subStr($content['stripBrief'], 0, 100) : $options->description;
}
?>">
<?php }?>

<?php if (false) {?>
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo BYENDS_SITE_URL; ?>feed" />
<?php }?>
<link rel="shortcut icon" type="image/x-icon" href="<?php echo BYENDS_THEMES_STATIC_URL; ?>favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?php echo BYENDS_THEMES_STATIC_URL; ?>style.css?<?php echo $ver; ?>" />
</head>
<body>
<?php if( $current <> 'zoom') {?>
<div id="header">
	<div class="wrap clearfix">
		<div id="logo" class="clearfix">
			<a href="<?php echo BYENDS_SITE_URL; ?>"><?php echo htmlspecialchars( $options->title ); ?></a>
		</div>
		<ul id="nav" class="clearfix">
			<li <?php if($current == 'popular') echo 'class="current"';?>>
				<a href="<?php echo BYENDS_SITE_URL; ?>popular">Popular</a>
			</li>
			<li class="last<?php if($current == 'random') echo ' current';?>">
				<a href="<?php echo BYENDS_SITE_URL; ?>random">Random</a>
			</li>
		</ul>
		<ul id="auth" class="clearfix">
			<?php if (null !== $widget->uid) {?>
			<li><img src="<?php echo $widget->user->avatar; ?>" /><a href="<?php echo BYENDS_COOK_URL.$widget->user->name; ?>" ><?php echo $widget->user->name; ?></a></li>
			<?php if ($widget->instanceUser->pass('administrator', true)) {?>
			<li><a href="<?php echo BYENDS_ADMIN_URL; ?>" >AdminControl</a></li>
			<?php }?>
			<li class="last"><a href="<?php echo BYENDS_AUTH_SIGNOUT_URL; ?>" >Sign out</a></li>
			<?php 
			}
			else {
			?>
			<li class="last"><a href="<?php echo BYENDS_AUTH_SIGNIN_URL; ?>">Sign in</a></li>
			<?php if (false) { ?>
			<li class="last"><a href="<?php echo BYENDS_AUTH_SIGNUP_URL; ?>">Sign up</a></li>
			<?php } }?>
		</ul>
	</div>
</div>

<div class="clearfix">
<?php }?>