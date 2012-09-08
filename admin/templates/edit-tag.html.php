<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Edit Tag</h2>
<form action="" method="post">
	<?php if( !empty($status) ) { ?>
		<div class="warn">
			<?php if( $status == 'mid-error' ) { ?>The Tag Id was error!<?php } ?>
			<?php if( $status == 'name-empty' ) { ?>The Name was empty!<?php } ?>
			<?php if( $status == 'name-exists' ) { ?>The Name was exists!<?php } ?>
			<?php if( $status == 'nameToSlug-error' ) { ?>The slug was error!<?php } ?>
			<?php if( $status == 'slug-exists' ) { ?>The slug was exists!<?php } ?>
		</div>
	<?php } ?>
	<input type="hidden" name="mid" value="<?php echo $tag['mid']; ?>"/>
	<dl>
		<dt>Name:</dt>
		<dd><input type="text" name="name" class="long" value="<?php echo $tag['name']; ?>"/></dd>
		
		<dt>Slug:</dt>
		<dd><input type="text" name="slug" class="long" value="<?php echo $tag['slug']; ?>"/></dd>
		
		<dt></dt>
		<dd>
			<input type="submit" name="update" value="Save" class="button"/>
			<input type="submit" name="delete" value="Delete" class="button" onclick="return confirm('Really delete this Tag and all Rerationship associated with it?');"/>
		</dd>
	</dl>
</form>

<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>