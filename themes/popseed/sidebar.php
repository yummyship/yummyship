<div id="sidebar">
	<div id="sidebar-inner">
		<div id="navigation" class="clearfix"><?php 
			$prev = $widget->thePrev();
			$next = $widget->theNext();
		?>
			<div class="prev"><?php echo $prev ? $prev : '<span class="disabled" title="Have No Prev Seed.">Have No Prev Seed.</span>'; ?></div>
			<div class="next"><?php echo $next ? $next : '<span class="disabled" title="Have No Next Seed.">Have No Next Seed.</span>'; ?></div>
		</div>
		<div class="widget share">
			<dl class="wd clearfix">
				<dt>Share</dt>
				<dd share-name="pi"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/pinterest_32.png" alt="Share On pinterest" /></dd>
				<dd share-name="fb"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/facebook_32.png" alt="Share On facebook" /></dd>
				<dd share-name="tw"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/twitter_32.png" alt="Share On twitter" /></dd>
				<dd share-name="tu"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/tumblr_32.png" alt="Share On tumblr" /></dd>
				<dd share-name="we"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/weheartit.png" alt="Share On weheartit" /></dd>
				<dd share-name="rd"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/reddit_32.png" alt="Share On reddit" /></dd>
				<dd share-name="dg"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/digg_32.png" alt="Share On digg" /></dd>
				<dd share-name="gg"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/googleplus_32.png" alt="Share On googleplus" /></dd>
				<dd share-name="su"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/stumbleupon_32.png" alt="Share On stumbleupon" /></dd>
				<dd share-name="lk"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/linkedin_32.png" alt="Share On linkedin" /></dd>
			</dl>
		</div>
		
		<div class="widget">
			<dl class="popular list-img wd clearfix">
				<dt>Popular</dt>
				<?php
				foreach ( $popularContent as $k => $v ) {
					echo '<dd><a href="'.$v['permalink'].'" title="'.$v['title'].'"><img src="'.$v['thumb'].'" alt="'.$v['title'].'"  /></a></dd>';
				}
				?>
			</dl>
		</div>
		
		<div class="widget">
			<dl class="related list-img wd clearfix">
				<dt>Related</dt>
				<?php
				foreach ( $relatedContent as $k => $v ) {
					echo '<dd><a href="'.$v['permalink'].'" title="'.$v['title'].'"><img src="'.$v['thumb'].'" alt="'.$v['title'].'"  /></a></dd>';
				}
				?>
			</dl>
		</div>
	</div>
</div>