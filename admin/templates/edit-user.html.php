<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Edit User</h2>
<form action="" method="post">
	<?php if( !empty($status) ) { ?>
		<div class="warn">
			<?php if( $status == 'passwords-not-equal' ) { ?>The passwords do not match!<?php } ?>
			<?php if( $status == 'username-empty' ) { ?>The name was empty!<?php } ?>
			<?php if( $status == 'mail-empty' ) { ?>The mail was empty!<?php } ?>
			<?php if( $status == 'username-exists' ) { ?>The name was exists!<?php } ?>
			<?php if( $status == 'mail-incorrect' ) { ?>The mail was incorrect!<?php } ?>
			<?php if( $status == 'mail-exists' ) { ?>The mail was exists!<?php } ?>
		</div>
	<?php } ?>
	<input type="hidden" name="uid" value="<?php echo $user['uid']; ?>"/>
	<input type="hidden" name="avatar" value="<?php echo $user['avatar']; ?>"/>
	<dl>
		<dt>Avatar:</dt>
		<dd><img src="<?php echo $user['avatar']; ?>" width="32" height="32" /></dd>
		
		<dt>Fullname:</dt>
		<dd><input type="text" name="fullname" class="long" value="<?php echo $user['fullname']; ?>"/></dd>
		
		<dt>Username:</dt>
		<dd><input type="text" name="username" class="long" value="<?php echo $user['username']; ?>"/></dd>
		
		<dt>Mail:</dt>
		<dd><input type="text" name="mail" class="long" value="<?php echo $user['mail']; ?>"/></dd>
		
		<dt>Url:</dt>
		<dd><input type="text" name="url" class="long" value="<?php echo $user['url']; ?>"/></dd>
		
		<dt>Password:</dt>
		<dd>
			<input type="password" name="password" value=""/>
			(leave empty if you don't want to change it)
		</dd>
		
		<dt>(repeat):</dt>
		<dd><input type="password" name="password2" value=""/></dd>
		
		<dt>Group:</dt>
		<dd>
			<select name="group">
				<?php foreach ($widget->groups as $k => $v) {?>
				<option value="<?php echo $k; ?>"<?php if ($k == $user['group']) echo " selected"; ?>><?php echo $k; ?></option>
				<?php }?>
			</select>
		</dd>
		
		<dt>Status:</dt>
		<dd>
			<select name="status">
				<?php foreach ($widget->status as $k => $v) {?>
				<option value="<?php echo $k; ?>"<?php if ($k == $user['status']) echo " selected"; ?>><?php echo $k; ?></option>
				<?php }?>
			</select>
		</dd>
		
		<dt>Desc:</dt>
		<dd><textarea name="description"><?php echo $user['description']; ?></textarea></dd>
		
		<dt></dt>
		<dd>
			<input type="submit" name="update" value="Save" class="button"/>
			<input type="submit" name="delete" value="Delete" class="button" onclick="return confirm('Really delete this User and all Posts associated with it?');"/>
		</dd>
	</dl>
</form>

<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>