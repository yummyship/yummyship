<?php require 'header.php'; ?>

<div class="u-info clearfix">
	<div class="u-info-avatar"><img src="<?php echo $userInfo->avatar; ?>" alt="<?php echo $userInfo->name."'s Kitchen";?>" /></div>
	<!-- <div class="u-info-edit"><a class="btn" href="<?php echo BYENDS_USER_URL; ?>edit-profile">Edit Profile</a></div> -->
	<div class="u-info-name">
		<h2><?php echo $userInfo->name."'s Kitchen"; ?></h2>
		<div class="u-info-date">Add In <?php echo date('Y-m-d', $userInfo->created); ?></div>
	</div>
	<ul class="u-info-menu clearfix">
		<li<?php if($current == 'cook') echo ' class="current"'; ?>><a href="<?php echo BYENDS_COOK_URL.$userInfo->name; ?>">Publish</a></li>
		<li class="sprite like<?php if($current == 'likes') echo ' current'; ?>"><a href="<?php echo BYENDS_LIKES_URL.$userInfo->name; ?>">Likes</a></li>
	</ul>
</div>

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

<div id="end-post" class="hidden">
	<button id="more">More…</button>
	<div id="loading" ><img src="<?php echo BYENDS_THEMES_STATIC_URL; ?>images/loading-black.gif"> Loading…</div>
</div>

<div id="pages" class="wrap clearfix">
		page <?php echo $pages['current']; ?> of <?php echo $pages['total']; ?>
	
		<?php if( $pages['prev'] ) { ?>
			<a href="<?php echo BYENDS_SITE_URL.'page/'.$pages['prev']?>">&laquo; prev</a>
		<?php } else { ?>
			&laquo; prev
		<?php } ?>
		/
		<?php if( $pages['next'] ) { ?>
			<a href="<?php echo BYENDS_SITE_URL.'page/'.$pages['next']?>">next &raquo;</a>
		<?php } else { ?>
			next &raquo;
		<?php } ?>
</div>

<?php require 'footer.php'; ?>
