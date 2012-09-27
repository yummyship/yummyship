<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Users <span class="span-add">+ <a href="?user&add">Add User</a></span></h2>

<table class="users">
	<tr>
		<th>Avatar</th>
		<th>Uid</th>
		<th>Name</th>
		<th>Mail</th>
		<th>Url</th>
		<th>Desc</th>
		<th>Created</th>
		<th>Last</th>
		<th>Group/Status</th>
		<th></th>
	</tr>
	<?php foreach( $users as $i => $u ) { ?>
		<tr class="<?php echo $i%2 ? 'odd' : 'even' ; ?>">
			<td class="avatar">
				<a href="?user&edit&uid=<?php echo $u['uid']; ?>" title="Edit"><img src="<?php echo $u['avatar'];?>" /></a>
			</td>
			<td class="uid"><?php echo $u['uid']; ?></td>
			<td class="name">
				<a href="?user&edit&uid=<?php echo $u['uid']; ?>"><?php echo $u['fullname']; ?></a>
			</td>
			<td class="mail">
				<?php echo $u['mail'];?>
			</td>
			<td class="url">
				<?php echo $u['url']; ?>
			</td>
			<td class="desc">
				<?php echo Byends_Paragraph::subStr($u['description'], 0, 20); ?>
			</td>
			<td class="date">
				<?php echo date( 'Y-m-d H:i', $u['created']); ?>
			</td>
			<td class="date">
				<?php echo date( 'Y-m-d H:i', $u['logged']); ?>
			</td>
			<td class="group">
				<?php echo $u['group']; ?>
				<div><?php echo $u['status']; ?></div>
			</td>
			<td><a href="?user&delete&uid=<?php echo $u['uid'];?>" onclick="return confirm('Really delete this User and all Posts associated with it?');">del?</a></td>
		</tr>
	<?php } ?>
</table>


<div id="pages">
	<div class="pageInfo">
		page <?php echo $pages['current']; ?> of <?php echo $pages['total']; ?>
	</div>
	
	<div class="pageLinks">
		<?php if( $pages['prev'] ) { ?>
			<a href="?user&amp;page=<?php echo $pages['prev']?>">&laquo; prev</a>
		<?php } else { ?>
			&laquo; prev
		<?php } ?>
		/
		<?php if( $pages['next'] ) { ?>
			<a href="?user&amp;page=<?php echo $pages['next']?>">next &raquo;</a>
		<?php } else { ?>
			next &raquo;
		<?php } ?>
	</div>
</div>

<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>