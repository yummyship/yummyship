<?php require 'header.php'; ?>

<?php require 'menu.php'; ?>

<div id="page" class="fullscreen">
	<div class="u-info clearfix">
		<div class="u-info-avatar"><img src="<?php echo $userInfo->avatar; ?>" alt="<?php echo $userInfo->fullname."'s Kitchen";?>" /></div>
		<!-- <div class="u-info-edit"><a class="btn" href="<?php echo BYENDS_SITE_URL; ?>settings">Edit Profile</a></div> -->
		<div class="u-info-name">
			<h2><?php echo $userInfo->fullname."'s Kitchen"; ?></h2>
			<div class="u-info-date">Add In <?php echo date('Y-m-d', $userInfo->created); ?></div>
		</div>
		<ul class="u-info-menu clearfix">
			<li<?php if($current == 'cook') echo ' class="current"'; ?>><a href="<?php echo BYENDS_SITE_URL.$userInfo->username; ?>">Publish (<?php echo $userInfo->publishedNum; ?>)</a></li>
			<li class="sprite like<?php if($current == 'likes') echo ' current'; ?>"><a href="<?php echo BYENDS_SITE_URL.$userInfo->username.'/likes'; ?>">Likes (<?php echo $userInfo->likesNum; ?>)</a></li>
		</ul>
	</div>

	<div id="recipe-cards">
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
		<?php } ?>
	</div>
	
	<div id="pages" class="wrap clearfix">
		<div class="pageInfo">
			page <?php echo $pages['current']; ?> of <?php echo $pages['total']; ?>
		</div>
		
		<div class="pageLinks">
			<?php if( $pages['prev'] ) { ?>
				<a href="<?php echo BYENDS_SITE_URL.$userInfo->username.'/'.$pagePrefix.$pages['prev']?>">&laquo; prev</a>
			<?php } else { ?>
				&laquo; prev
			<?php } ?>
			/
			<?php if( $pages['next'] ) { ?>
				<a href="<?php echo BYENDS_SITE_URL.$userInfo->username.'/'.$pagePrefix.$pages['next']?>">next &raquo;</a>
			<?php } else { ?>
				next &raquo;
			<?php } ?>
		</div>
	</div>
</div>

<?php require 'footer.php'; ?>
