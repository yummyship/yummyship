<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Tags <span class="span-add">+ <a href="?tag&add">Add Tag</a></span></h2>

<table class="tags">
	<tr>
		<th>Mid</th>
		<th>Name</th>
		<th>Slug</th>
		<th>Count</th>
		<th></th>
	</tr>
	<?php foreach( $tags as $i => $t ) { ?>
		<tr class="<?php echo $i%2 ? 'odd' : 'even' ; ?>">
			<td class="mid" align="center"><?php echo $t['mid']; ?></td>
			<td class="name">
				<a href="?tag&edit&mid=<?php echo $t['mid']; ?>"><?php echo $t['name']; ?></a>
			</td>
			<td class="name">
				<a href="?tag&edit&mid=<?php echo $t['mid']; ?>"><?php echo $t['slug'];?></a>
			</td>
			<td class="views" align="center">
				<?php echo $t['count']; ?>
			</td>
			
			<td><a href="?tag&delete&mid=<?php echo $t['mid'];?>" onclick="return confirm('Really delete this Tag and all Rerationship associated with it?');">del?</a></td>
		</tr>
	<?php } ?>
</table>


<div id="pages">
	<div class="pageInfo">
		page <?php echo $pages['current']; ?> of <?php echo $pages['total']; ?>
	</div>
	
	<div class="pageLinks">
		<?php if( $pages['prev'] ) { ?>
			<a href="?tag&amp;page=<?php echo $pages['prev']?>">&laquo; prev</a>
		<?php } else { ?>
			&laquo; prev
		<?php } ?>
		/
		<?php if( $pages['next'] ) { ?>
			<a href="?tag&amp;page=<?php echo $pages['next']?>">next &raquo;</a>
		<?php } else { ?>
			next &raquo;
		<?php } ?>
	</div>
</div>



<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>