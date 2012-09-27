<div id="sidebar">
	<div id="sidebar-inner" class="board-shadow">
		<div class="widget board-padding">
			<div id="publisher-card" class="clearfix">
				<div class="profile-photo"><a href="<?php echo $content['userUrl'];?>"><img src="<?php echo $content['avatar'];?>"></a></div>
				<a class="profile-name" itemprop="author" rel="author" href="<?php echo $content['userUrl'];?>"><?php echo $content['fullname'];?></a>
				<div class="profile-details"><a href="<?php echo $content['userLikesUrl'];?>">Likes <?php echo $content['likesNum']; ?> recipes</a></div>
				<div class="profile-details"><a href="<?php echo $content['userUrl'];?>">Published <?php echo $content['publishedNum']; ?> recipes</a></div>
			</div>
		</div>
		<?php if (isset($options->sidebarAds) && $options->sidebarAds) { ?>
		<div class="widget board-padding">
			<div class="ads"><?php echo $options->sidebarAds; ?></div>
		</div>
		<?php }?>
		<div class="widget board-padding">
			<dl class="popular list-img clearfix">
				<dt>Popular</dt>
				<?php
				$i = 1;
				foreach ( $popularContent as $k => $v ) {
					echo '<dd'.($i%3==0 ? ' class="margin-right-none"' : '').'><a href="'.$v['permalink'].'" title="'.$v['title'].'"><img src="'.$v['thumb'].'" alt="'.$v['title'].'"  /></a></dd>';
					$i++;
				}
				?>
			</dl>
		</div>
	</div>
</div>