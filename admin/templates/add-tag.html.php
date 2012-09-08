<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Add Tag</h2>
<form action="" method="post">
	<?php if( !empty($status) ) { ?>
		<div class="warn">
			<?php if( $status == 'name-empty' ) { ?>The Name was empty!<?php } ?>
			<?php if( $status == 'name-exists' ) { ?>The Name was exists!<?php } ?>
			<?php if( $status == 'nameToSlug-error' ) { ?>The slug was error!<?php } ?>
			<?php if( $status == 'slug-exists' ) { ?>The slug was exists!<?php } ?>
		</div>
	<?php } ?>
	<dl>
		<dt>Name:</dt>
		<dd><input type="text" name="name" class="long" value="<?php echo $request->filter('trim')->name; ?>"/></dd>
		
		<dt>Slug:</dt>
		<dd><input type="text" name="slug" class="long" value="<?php echo $request->filter('trim')->slug; ?>"/></dd>
		
		<dt></dt>
		<dd>
			<input type="submit" name="insert" value="Add Tag" class="button"/>
		</dd>
	</dl>
</form>

<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>