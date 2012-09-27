<?php require 'header.php'; ?>

<div id="zoomr">
	<div id="zoomr_toolbar">
		<div class="bg"></div>
		<div class="fg">
			<div id="zoomr_logo" ><a href="<?php echo BYENDS_SITE_URL; ?>"><?php echo $options->title; ?></a></div>
			<a id="zoomr_hide" href="<?php echo $content['permalink']; ?>" title="back">back</a>
		</div>
	</div>
	<div id="zoomr_body">
		<img id="zoomr_img" src="<?php echo $content['cover']; ?>" width="<?php echo $content['width']; ?>" height="<?php echo $content['height']; ?>" title="<?php echo $content['title']; ?>" alt="<?php echo $content['title']; ?>">
	</div>
</div>

<?php require 'footer.php'; ?>