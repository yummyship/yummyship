<!DOCTYPE html>
<html>
<head>
	<title>AdminControl: <?php echo $options->title; ?></title>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="<?php echo BYENDS_ADMIN_THEMES_URL; ?>admin.css" />
	<link rel="Shortcut Icon" href="<?php echo BYENDS_ADMIN_THEMES_URL; ?>admin.ico" />
</head>
<body>


<div id="menu">
	<img src="<?php echo $userInstance->user->avatar; ?>" class="avatar" />
	<br />
	Hi, <?php echo $userInstance->user->name; ?>
	<a href="<?php echo BYENDS_AUTH_SIGNOUT_URL; ?>">Logout</a>
	<br />
	<br />
	<a href="<?php echo BYENDS_SITE_URL; ?>" target="_blank">Visite Site</a>
	<h1><?php echo $options->title; ?></h1>
	<a href="?post">Posts</a>
	<br />
	<a href="?tag">Tags</a>
	<br />
	<a href="?user">Users</a>
	<br />
	<a href="?setting">Settings</a>
	<br />
	Bookmarklet:
	<a class="bookmarklet" title="Post Bookmarklet" href="javascript:void(function(a,b,c,d){d=b.createElement('script');d.setAttribute('charset','utf-8');d.src='<?php echo BYENDS_SITE_URL.'gather/gather.js.php'; ?>?'+new Date().getTime();b.body.appendChild(d)}(window,document))"><?php echo $options->title; ?></a>
	<!-- 
	<br />
	<a class="bookmarklet" title="Post Bookmarklet" href="javascript:void(function(a,b,c,d){d=b.createElement('script');d.setAttribute('charset','utf-8');d.src='<?php echo BYENDS_POST_JS; ?>?'+new Date().getTime();b.body.appendChild(d)}(window,document))"><?php echo $options->title; ?></a>
	 -->
</div>

<div id="content" class="relative">
