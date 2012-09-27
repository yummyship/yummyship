<?php require 'header.php'; ?>

<?php require 'menu.php'; ?>

<div id="page" class="fullscreen">
	<div id="recipe-cards" class="clearfix">
	<?php foreach( $contents as $k => $v ) { ?>
		<div class="recipe-card" id="<?php echo $v['coverHash']; ?>">
			<a href="<?php echo $v['permalink']; ?>" title="<?php echo $v['title']; ?>">
				<div class="recipe-card-title">
					<div class="recipe-card-image">
						<img src="<?php echo $v['thumb']; ?>" alt="<?php echo $v['title']; ?>"/>
						<div class="overlay"></div>
					</div>
					<div class="title"><?php echo $v['title']; ?></div>
				</div>
			</a>
			<div class="meta clearfix">
				<div class="meta-info">
					<div class="author"><a href="<?php echo $v['userUrl']; ?>"><?php echo $v['fullname']; ?></a></div>
					<span class="sprite like<?php echo $v['favorite'] ? ' saved' : ''; ?>" data-cid="<?php echo $v['cid']; ?>"><?php echo $v['favoritesNum']; ?></span>
					<span class="sprite clock published"><?php echo $v['dateWord']; ?></span>
				</div>
				<div class="rating">
					<div class="num"><?php echo $v['viewsWord']; ?></div>
					Rating
				</div>
			</div>
		</div>
	<?php }	?>
	</div>
	
	<div id="end-marker">
		<button id="more-recipes" class="btn-big">More…</button>
		<div id="loading-more" ><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/loading-black.gif"> Loading…</div>
	</div>
	
	<div id="pages" class="wrap clearfix hidden">
		<div class="pageInfo">
			page <?php echo $pages['current']; ?> of <?php echo $pages['total']; ?>
		</div>
		
		<div class="pageLinks">
			<?php if( $pages['prev'] ) { ?>
				<a href="<?php echo BYENDS_SITE_URL.$current.'/'.$pages['prev']?>">&laquo; prev</a>
			<?php } else { ?>
				&laquo; prev
			<?php } ?>
			/
			<?php if( $pages['next'] ) { ?>
				<a href="<?php echo BYENDS_SITE_URL.$current.'/'.$pages['next']?>">next &raquo;</a>
			<?php } else { ?>
				next &raquo;
			<?php } ?>
		</div>
	</div>
	<div id="scroll-to-top">Scroll to Top</div>
</div>

<?php require 'footer.php'; ?>
