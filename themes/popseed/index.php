<?php require 'header.php'; ?>

<div id="container" class="clearfix">
	<?php foreach( $contents as $k => $v ) { ?>
	<div class="recipe-card" id="<?php echo $v['cid']; ?>">
		<div class="recipe-card-image">
			<a href="<?php echo $v['permalink']; ?>" title="<?php echo $v['title']; ?>">
				<img src="<?php echo $v['thumb']; ?>" alt="<?php echo $v['title']; ?>"/>
			</a>
		</div>
		<div class="meta clearfix">
			<div class="meta-info">
				<span class="brief"><?php echo Byends_Paragraph::subStr($v['stripBrief'] ? $v['stripBrief'] : $v['title'], 0, 22); ?></span>
				<span class="sprite like<?php echo $v['favorite'] ? ' saved' : ''; ?>" data-cid="<?php echo $v['cid']; ?>"><?php echo $v['favoritesNum']; ?></span>
				<span class="sprite clock published"><?php echo $v['dateWord']; ?></span>
			</div>
			<div class="rating">
				<div class="num"><?php echo $v['viewsWord']; ?></div>
				Rating
			</div>
		</div>
	</div>
	<?php } ?>
</div>

<div id="end-marker">
	<button id="more-recipes">More…</button>
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

<?php require 'footer.php'; ?>
