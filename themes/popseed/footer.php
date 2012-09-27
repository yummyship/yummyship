<?php if( $current <> 'zoom' && $current <> '404' && false === strpos($current, 'auth')) {?>
<div id="footer">
	&copy; <?php echo BYENDS_COPYRIGHT_YEAR.' '.BYENDS_COPYRIGHT_NAME; ?>.
</div>
<?php }?>
<div class="hidden">
<script type="text/javascript" src="<?php echo BYENDS_THEMES_STATIC_URL; ?>js/jquery.js?<?php echo $ver; ?>"></script>
<script type="text/javascript" src="<?php echo BYENDS_THEMES_STATIC_URL; ?>js/jquery.plugins.js?<?php echo $ver; ?>"></script>
<script type="text/javascript" src="<?php echo BYENDS_THEMES_STATIC_URL; ?>js/yummyship.js?<?php echo $ver; ?>"></script>
<script type="text/javascript">
var 
domain = '<?php echo str_replace('www.', '', $options->domain); ?>',
siteUrl = '<?php echo BYENDS_SITE_URL; ?>',
signInUrl = '<?php echo BYENDS_AUTH_SIGNIN_URL; ?>',
signOAuthUrl = '<?php echo BYENDS_AUTH_OAUTH_URL; ?>',
signedIn = <?php echo (null !== $widget->uid ? 'true' : 'false'); ?>,
seedAction = '<?php echo $current; ?>',
seedName = '<?php echo $options->seed; ?>',
nextRecipe = <?php echo $options->perPage; ?>,
recipeNum = <?php echo $options->ajaxPerPage; ?>,
fetchingMore = true,
scrolledToEnd = false;
Yummyship.initSeeds('.recipe-card');
if (typeof seedAction !== 'undefined' && (seedAction == 'index' || seedAction == 'popular')) {
	$(document).bind('scroll', Yummyship.scrollSeed);
}
</script>
<?php 
if ( $_SERVER['HTTP_HOST'] != '192.168.1.80' ) {
	echo $options->hiddens;
} ?>
</div>
</body>
</html>