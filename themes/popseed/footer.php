<?php if( $current <> 'zoom') {?>
	<div id="scroll-to-top">Scroll to Top</div>
</div>
<div id="footer">
	&copy; <?php echo BYENDS_COPYRIGHT_YEAR.' '.BYENDS_COPYRIGHT_NAME; ?>.
</div>
<?php }?>
<div class="hidden">
<script type="text/javascript" src="<?php echo BYENDS_THEMES_STATIC_URL; ?>js/jquery.js?<?php echo $ver; ?>"></script>
<script type="text/javascript" src="<?php echo BYENDS_THEMES_STATIC_URL; ?>js/jquery.plugins.js?<?php echo $ver; ?>"></script>
<script type="text/javascript" src="<?php echo BYENDS_THEMES_STATIC_URL; ?>js/common.js?<?php echo $ver; ?>"></script>
<script>
var 
domain = '<?php echo str_replace('www.', '', $options->domain); ?>',
siteUrl = '<?php echo BYENDS_SITE_URL; ?>',
signInUrl = '<?php echo BYENDS_AUTH_SIGNIN_URL; ?>',
signedIn = <?php echo (NULL !== $widget->uid ? 'true' : 'false'); ?>,
fetchingMore = false,
seedAction = '<?php echo $current; ?>',
seedName = '<?php echo $options->seed; ?>',
nextRecipe = <?php echo $options->perPage; ?>,
recipeNum = <?php echo $options->ajaxPerPage; ?>,
scrolledToEnd = false;
$(document).ready(function(){
	Yummyship.init();
	$("#more-recipes").click(function() {
		Yummyship.fetchMoreSeeds();
	});
	Yummyship.initSeeds('.recipe-card');
	if (seedAction == 'index' || seedAction == 'popular') {
		$(document).bind('scroll', Yummyship.scrollSeed);
	}
});
</script>

<?php 
if ( $_SERVER['HTTP_HOST'] != '192.168.1.80' ) {
	echo $options->hiddens;
} ?>
</div>
</body>
</html>