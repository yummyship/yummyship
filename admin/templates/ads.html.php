<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Advertising</h2>

<form action="<?php echo BYENDS_ADMIN_URL.'?ads'; ?>" method="post">
	<?php if( !empty($status) ) { ?>
		<div class="warn">
			<?php if( $status == 'save-succ' ) { ?>The Ads is saving!<?php } ?>
		</div>
	<?php } ?>
	<dl>
		<dt>Content Ads:</dt>
		<dd><textarea id="text" name="options[contentAds]"><?php echo $options->contentAds; ?></textarea></dd>
		
		<dt>Sidebar Ads:<br /><br />250x250</dt>
		<dd><textarea id="text" name="options[sidebarAds]"><?php echo $options->sidebarAds; ?></textarea></dd>
		
		<dt></dt>
		<dd>
			<input type="submit" name="update" value="Save Advertising" class="button"/>
		</dd>
	</dl>
</form>

<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>