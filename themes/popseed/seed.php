<?php require 'header.php'; ?>

<?php require 'menu.php'; ?>

<div id="page" class="center">
	<div id="recipe-details" class="clearfix">
		<div id="recipe-left">
			<div id="recipe-left-inner" class="board-shadow">
				<div id="navigation" class="board-padding clearfix"><?php 
					$prev = $widget->thePrev();
					$next = $widget->theNext();
					?>
					<div class="prev"><?php echo $prev ? $prev : '<span class="disabled" title="Have No Prev Seed.">Have No Prev Seed.</span>'; ?></div>
					<div class="next"><?php echo $next ? $next : '<span class="disabled" title="Have No Next Seed.">Have No Next Seed.</span>'; ?></div>
				</div>
				<div class="widget share board-padding">
					<dl class="clearfix">
						<dt>Share</dt>
						<dd share-name="pi"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/pinterest_48.png" alt="Share On Pinterest" /></dd>
						<dd share-name="gg" class="margin-right-none"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/googleplus_48.png" alt="Share On Google Plus" /></dd>
						<dd share-name="fb"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/facebook_32.png" alt="Share On Facebook" /></dd>
						<dd share-name="tw" class="margin-right-none"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/twitter_48.png" alt="Share On Twitter" /></dd>
						<?php if (false) {?>
						<dd share-name="we"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/weheartit.png" alt="Share On weheartit" /></dd>
						<dd share-name="tu"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/tumblr_32.png" alt="Share On tumblr" /></dd>
						<dd share-name="rd" class="margin-right-none"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/reddit_32.png" alt="Share On reddit" /></dd>
						<dd share-name="lk"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/linkedin_32.png" alt="Share On linkedin" /></dd>
						<dd share-name="dg"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/digg_32.png" alt="Share On digg" /></dd>
						<dd share-name="su" class="margin-right-none"><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/stumbleupon_32.png" alt="Share On stumbleupon" /></dd>
						<?php }?>
					</dl>
				</div>
			</div>
		</div>
		<div id="recipe-content">
			<h1><?php echo $content['title']; ?></h1><?php 
				$meta = ' srcWidth="'.$content['width'].'" srcHeight="'.$content['height'].'"';
				$style = '';
				if($content['width'] > 526){
					$meta = ' width="526" '.$meta;
				}
				$style = 'background-image:url('.$content['cover'].');height:'.($content['height'] > 396 ? '396' : $content['height']).'px';
			?>
			<div class="recipe-middle board-shadow">
				<div class="cover<?php if($content['height'] > 396){echo ' recipe-big-cover';} ?>" style="<?php echo $style; ?>">
					<img src="<?php echo $content['cover']; ?>" title="<?php echo $content['title']; ?>" alt="<?php echo $content['title']; ?>"<?php echo $meta;?> />
					<?php if(false) {?>
					<a class="zoom" href="<?php echo $content['zoomPermalink']; ?>" title="Full Image"></a>
					<?php }?>
				</div>
			
				<div class="recipe-meta board-padding clearfix">
					<span class="author"><a href="<?php echo $content['userUrl']; ?>"><?php echo $content['fullname']; ?></a></span>
					<span class="published">On <?php echo date('M d, Y', $content['modified']).'('.$content['dateWord'].')'; ?></span> | 
					<span class="likeNum">Likes <span class="num"><?php echo $content['favoritesNum']; ?></span></span> | 
					<span class="views">Rating <?php echo $content['views']; ?></span>
					<span class="btn-regular action-like right<?php echo $content['favorite'] ? ' saved' : ''; ?>" data-cid="<?php echo $content['cid']; ?>">
						<span class="sprite like<?php echo $content['favorite'] ? ' saved' : ''; ?>"></span>
						Like
					</span>
				</div>
				<div class="recipe-info board-padding">
					<?php if ($content['brief']) {?>
					<p class="recipe-info-title"><b>Brief</b></p>
					<p><?php echo nl2br($content['brief']); ?></p>
					<?php }?>
					<p class="recipe-info-title"><b>Ingredients</b></p>
					<table class="recipe-info-ingredients" cellpadding="0" cellspacing="1">
					<tbody>
					<?php 
					$countIng = count($content['ingredients']);
					$table = "<tr>";
					$i = 1;
					foreach ($content['ingredients'] as $k => $v) {
						$table .= '<td>'.$content['tag'][$k]['name'].'<span>'.$v."</span></td>";
						if ($i%2 == 0 ) $table .= "</tr><tr>";
						$i ++;
					}
					if ($countIng > 1 && $countIng%2 == 1) {
						$table .= '<td>&nbsp;</td>';					
					}
					echo $table;
					?></tr>
					</tbody>
					</table>
					<p class="recipe-info-title"><b>Steps</b></p>
					<div class="recipe-info-steps">
						<ol>
						<?php 
						$li = '';
						$stepsNum = count($content['steps']);
						foreach ($content['steps'] as $k => $v) {
							list($stepText, $stepImage) = explode('@#|@', $v);
							$li .= '<li class="clearfix">';
							$li .= $stepsNum > 1 ? '<em>'.($k+1).'.</em>' : '';
							if ($stepImage) {
								$li .= '<span>'.nl2br($stepText).'</span><img src="'.$stepImage.'" alt="'.$content['title'].' Step '.($k+1).'" width="'.$options->imageConfig['stepSize'][0].'" height="'.$options->imageConfig['stepSize'][1].'" />';
							}
							else {
								$li .= nl2br($stepText);
							}
							$li .= '</li>';
						} 
						echo $li;
						?>
						</ol>
					</div>
					<?php if ($content['tips']) {?>
					<p class="recipe-info-title"><b>Tips:</b></p>
					<p><?php echo nl2br($content['tips']); ?></p>
					<?php }?>
				</div>
				<?php if (isset($options->contentAds) && $options->contentAds) { ?>
				<div class="ads board-padding"><?php echo $options->contentAds; ?></div>
				<?php }?>
				<div class="share-link board-padding">
					<a class="sprite link" href="<?php echo $content['permalink']; ?>" title="<?php echo $content['title']; ?> Link">Link</a>
					<input id="link-url" type="text" value="<?php echo $content['permalink']; ?>" readonly="readonly">
				</div>
				<dl class="related board-padding clearfix">
					<dt>Related Recipes:</dt>
					<?php
					$i = 1;
					foreach ( $relatedContent as $k => $v ) {
						echo '<dd'.($i%5==0 ? ' class="margin-right-none"' : '').'><a href="'.$v['permalink'].'" title="'.$v['title'].'"><img src="'.$v['thumb'].'" alt="'.$v['title'].'" /></a></dd>';
						$i++;
					}
					?>
				</dl>
				
				<?php require 'comments.php'; ?>
			</div>
		</div>
		
		<?php require 'sidebar.php'; ?>
	</div>
	<div id="scroll-to-top">Scroll to Top</div>
</div>

<?php require 'footer.php'; ?>