<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Add User</h2>
<form action="" method="post">
	<?php if( !empty($status) ) { ?>
		<div class="warn">
			<?php if( $status == 'passwords-empty' ) { ?>The passwords was empty!<?php } ?>
			<?php if( $status == 'passwords-not-equal' ) { ?>The passwords do not match!<?php } ?>
			<?php if( $status == 'fullname-empty' ) { ?>The fullname was empty!<?php } ?>
			<?php if( $status == 'username-empty' ) { ?>The username was empty!<?php } ?>
			<?php if( $status == 'mail-empty' ) { ?>The mail was empty!<?php } ?>
			<?php if( $status == 'username-exists' ) { ?>The username was exists!<?php } ?>
			<?php if( $status == 'mail-incorrect' ) { ?>The mail was incorrect!<?php } ?>
			<?php if( $status == 'mail-exists' ) { ?>The mail was exists!<?php } ?>
		</div>
	<?php } ?>
	<dl>
		<dt>Fullname:</dt>
		<dd><input type="text" name="fullname" class="long" value="<?php echo $request->filter('trim')->fullname; ?>"/></dd>
		
		<dt>Username:</dt>
		<dd><input type="text" name="username" class="long" value="<?php echo $request->filter('trim')->username; ?>"/></dd>
		
		<dt>Mail:</dt>
		<dd><input type="text" name="mail" class="long" value="<?php echo $request->filter('trim')->mail; ?>"/></dd>
		
		<dt>Url:</dt>
		<dd><input type="text" name="url" class="long" value="<?php echo $request->filter('trim')->url; ?>"/></dd>
				
		<dt>Password:</dt>
		<dd>
			<input id="title" type="password" name="password" value=""/>
		</dd>
		
		<dt>(repeat):</dt>
		<dd><input id="title" type="password" name="password2" value=""/></dd>
		
		<dt>Group:</dt>
		<dd>
			<select name="group">
				<?php foreach ($widget->groups as $k => $v) {?>
				<option value="<?php echo $k; ?>"<?php if ($k == $request->filter('trim')->get('group')) echo " selected"; ?>><?php echo $k; ?></option>
				<?php }?>
			</select>
		</dd>
		
		<dt>Status:</dt>
		<dd>
			<select name="status">
				<?php foreach ($widget->status as $k => $v) {?>
				<option value="<?php echo $k; ?>"<?php if ($k == $request->filter('trim')->get('status')) echo " selected"; ?>><?php echo $k; ?></option>
				<?php }?>
			</select>
		</dd>
		
		<dt>Desc:</dt>
		<dd><textarea name="description"><?php echo $request->filter('trim')->get('description'); ?></textarea></dd>
		
		<dt></dt>
		<dd>
			<input type="submit" name="insert" value="Add User" class="button"/>
		</dd>
	</dl>
</form>

<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>