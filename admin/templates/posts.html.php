<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Posts <span class="span-add">+ <a href="?post&add">Add Post</a></span></h2>

<table class="posts">
	<tr>
		<th>Image</th>
		<th>Cid</th>
		<th>Title</th>
		<th>Date / User</th>
		<th>Brief</th>
		<th>Status</th>
		<!-- <th>AllowComment/CommentsNum</th> -->
		<th>FavNum / Views</th>
		<th></th>
	</tr>
	<?php foreach( $posts as $i => $p ) { ?>
		<tr class="<?php echo $i%2 ? 'odd' : 'even' ; ?>">
			<td class="image">
				<?php if( $p['thumb'] ) { ?>
					<a href="?post&edit&cid=<?php echo $p['cid'];?>" title="Edit"><img src="<?php echo $p['thumb']; ?>" /></a>
				<?php } ?>
			</td>
			<td class="cid"><?php echo $p['cid'];?></td>
			<td class="title">
				<a href="?post&edit&cid=<?php echo $p['cid'];?>" title="Edit"><?php echo $p['title']; ?></a>
			</td>
			<td class="date">
				<?php echo date( 'Y-m-d H:i', $p['created'] ); ?>
				<div class="user"><?php echo $p['userName']; ?></div>
			</td>
			<td class="text">
				<?php echo Byends_Paragraph::subStr($p['stripBrief'], 0, 35); ?>
			</td>
			<td class="views">
				<?php echo $p['status']; ?>
			</td>
			<!-- 
			<td class="views">
				<?php echo $p['allowComment'] ? 'Y' : 'N'; ?>
				<div><?php echo $p['commentsNum']; ?></div>
			</td>
			 -->
			<td class="views">
				<?php echo $p['favoritesNum']; ?>
				<div><?php echo $p['views']; ?></div>
			</td>
			<td>
				<a href="?post&delete&cid=<?php echo $p['cid'];?>" onclick="return confirm('Really delete this Post?');">del?</a><br />
				<a href="<?php echo $p['permalink'];?>" title="View" target="_blank">view</a>
			</td>
		</tr>
	<?php } ?>
</table>


<div id="pages">
	<div class="pageInfo">
		page <?php echo $pages['current']; ?> of <?php echo $pages['total']; ?>
	</div>
	
	<div class="pageLinks">
		<?php if( $pages['prev'] ) { ?>
			<a href="?post&amp;page=<?php echo $pages['prev']?>">&laquo; prev</a>
		<?php } else { ?>
			&laquo; prev
		<?php } ?>
		/
		<?php if( $pages['next'] ) { ?>
			<a href="?post&amp;page=<?php echo $pages['next']?>">next &raquo;</a>
		<?php } else { ?>
			next &raquo;
		<?php } ?>
	</div>
</div>

<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>