<?php require 'header.php'; ?>

<div id="page">
	<div id="page-box" class="clearfix">
		<div class="logo">
			<a href="<?php echo BYENDS_SITE_URL; ?>"><?php echo $options->title; ?></a>
		</div>
		<div class="separate"></div>
		<div class="error-page">
		    <h2 class="red">Whoops, Page Not Found.</h2>
		    <p><b>If you clicked on a link to get here</b> — please send us an email at <a href="mailto:support@<?php echo str_replace('www.', '', $options->domain);?>">support@<?php echo str_replace('www.', '', $options->domain);?></a> so we can correct the broken link.</p>
		    <p><b>If you typed the URL</b> — please double check the address to make sure it was entered exactly as intended.</p>
		    <p><b>If all else fails</b> — head back to the <a href="<?php echo BYENDS_SITE_URL; ?>">home page</a>.</p>
		</div>
	</div>
</div>

<?php require 'footer.php'; ?>