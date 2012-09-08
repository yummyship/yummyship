<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Edit Post</h2>

<form action="" method="post" id="recipeFm" name="recipeFm">
	<input type="hidden" name="cid" value="<?php echo $post['cid']; ?>"/>
	<?php if( !empty($status) ) { ?>
		<div class="warn">
			<?php if( $status == 'title-empty' ) { ?>The Title was empty!<?php } ?>
			<?php if( $status == 'not-logged-in' ) { ?>The name or password was not correct!<?php } ?>
			<?php if( $status == 'download-failed' ) { ?>Couldn't load the image!<?php } ?>
			<?php if( $status == 'duplicate-image' ) { ?>This image was already posted!<?php } ?>
			<?php if( $status == 'thumbnail-failed' ) { ?>Couldn't create a thumbnail of the image!<?php } ?>
			<?php if( $status == 'rename-failed' ) { ?>Couldn't rename the image!<?php } ?>
		</div>
	<?php } ?>
	<dl>
		<dt>Modified:</dt>
		<dd>
			<input type="text" name="modified" id="modified" value="<?php echo date('Y-m-d H:i:s', $post['modified']);?>"/>
			<script type="text/javascript" src="<?php echo BYENDS_ADMIN_THEMES_URL; ?>calendar.js?12.9.2.1532"></script>
			<script type="text/javascript">
				created = new Calendar( 'modified' );
			</script>
		</dd>
			
		<dt>Title:</dt>
		<dd><input type="text" id="title" name="title" class="long" value="<?php echo $post['title']; ?>"/></dd>
		
		<dt>Cover:</dt>
		<dd class="cover">
			<div class="cover-uploader">
				<a href="javascript:void(0);" class="i-cancel delete">✕</a>
				<a href="javascript:void(0);" class="button btn-regular cover-add" style="display:none">+ Add Cover </a>
				<input type="hidden" id="cover" name="cover" value="<?php echo $post['coverHash']; ?>"/>
				<img src="<?php echo $post['cover']; ?>" />
			</div>
		</dd>
		
		<dt>Brief:</dt>
		<dd><textarea id="brief" name="brief"><?php echo $post['brief']; ?></textarea></dd>
		
		<dt>Ingredients:</dt>
		<dd class="ingredients">
			<div class="ingredients-row-head">
				<span class="ingredients-row-name">Ingredients Name</span>
				<span class="ingredients-row-dosage">Dosage(option)</span>
			</div>
			<?php if ($post['ingredients']) {
			foreach ($post['ingredients'] as $k => $v) {?>
			<div class="ingredients-row">
				<span class="ingredients-row-name"><input type="text" name="ingredients[]" value="<?php echo $post['tag'][$k]['name']; ?>" /></span>
				<span class="ingredients-row-dosage"><input type="text" name="dosage[]" value="<?php echo $v; ?>" /></span>
			</div>
			<?php }
			}
			else {?>
			<div class="ingredients-row">
				<span class="ingredients-row-name"><input type="text" name="ingredients[]" /></span>
				<span class="ingredients-row-dosage"><input type="text" name="dosage[]" /></span>
			</div>
			<div class="ingredients-row">
				<span class="ingredients-row-name"><input type="text" name="ingredients[]" /></span>
				<span class="ingredients-row-dosage"><input type="text" name="dosage[]" /></span>
			</div>
			<div class="ingredients-row">
				<span class="ingredients-row-name"><input type="text" name="ingredients[]" /></span>
				<span class="ingredients-row-dosage"><input type="text" name="dosage[]" /></span>
			</div>
			<div class="ingredients-row">
				<span class="ingredients-row-name"><input type="text" name="ingredients[]" /></span>
				<span class="ingredients-row-dosage"><input type="text" name="dosage[]" /></span>
			</div>
			<div class="ingredients-row">
				<span class="ingredients-row-name"><input type="text" name="ingredients[]" /></span>
				<span class="ingredients-row-dosage"><input type="text" name="dosage[]" /></span>
			</div>
			<div class="ingredients-row">
				<span class="ingredients-row-name"><input type="text" name="ingredients[]" /></span>
				<span class="ingredients-row-dosage"><input type="text" name="dosage[]" /></span>
			</div>
			<?php }?>
			<a href="javascript:void(0);" class="ingredients-row-add"> + Add Rows</a>
		</dd>
		
		<dt>Steps:</dt>
		<dd class="steps">
			<?php if ($post['steps']) {
			foreach ($post['steps'] as $k => $v) {
				list($stepText, $stepImage) = explode('@#|@', $v);
			?>
			<div class="steps-row clearfix">
				<div class="steps-num"><?php echo $k+1; ?></div>
				<div class="steps-text"><textarea name="steps[]"><?php echo $stepText; ?></textarea></div>
				<div class="steps-image">
					<div class="steps-image-uploader">
						<?php if ($stepImage) {?>
						<a href="javascript:void(0);" class="i-cancel delete">✕</a>
						<a href="javascript:void(0);" class="button btn-regular steps-image-add" style="display:none"> + Image </a>
						<input type="hidden" name="stepsImage[]" value="<?php echo $stepImage; ?>"/>
						<img src="<?php echo $stepImage; ?>" />
						<?php }
						else {?>
						<a href="javascript:void(0);" class="i-cancel delete" style="display:none">✕</a>
						<a href="javascript:void(0);" class="button btn-regular steps-image-add"> + Image </a>
						<input type="hidden" name="stepsImage[]" value=""/>
						<?php }?>
					</div>
				</div>
			</div>
			<?php }
			}
			else {?>
			<div class="steps-row clearfix">
				<div class="steps-num">1</div>
				<div class="steps-text"><textarea name="steps[]"></textarea></div>
				<div class="steps-image">
					<div class="steps-image-uploader">
						<a href="javascript:void(0);" class="i-cancel delete" style="display:none">✕</a>
						<a href="javascript:void(0);" class="button btn-regular steps-image-add"> + Image </a>
						<input type="hidden" name="stepsImage[]" value=""/>
					</div>
				</div>
			</div>
			<div class="steps-row clearfix">
				<div class="steps-num">2</div>
				<div class="steps-text"><textarea name="steps[]"></textarea></div>
				<div class="steps-image">
					<div class="steps-image-uploader">
						<a href="javascript:void(0);" class="i-cancel delete" style="display:none">✕</a>
						<a href="javascript:void(0);" class="button btn-regular steps-image-add"> + Image </a>
						<input type="hidden" name="stepsImage[]" value=""/>
					</div>
				</div>
			</div>
			<div class="steps-row clearfix">
				<div class="steps-num">3</div>
				<div class="steps-text"><textarea name="steps[]"></textarea></div>
				<div class="steps-image">
					<div class="steps-image-uploader">
						<a href="javascript:void(0);" class="i-cancel delete" style="display:none">✕</a>
						<a href="javascript:void(0);" class="button btn-regular steps-image-add"> + Image </a>
						<input type="hidden" name="stepsImage[]" value=""/>
					</div>
				</div>
			</div>
			<div class="steps-row clearfix">
				<div class="steps-num">4</div>
				<div class="steps-text"><textarea name="steps[]"></textarea></div>
				<div class="steps-image">
					<div class="steps-image-uploader">
						<a href="javascript:void(0);" class="i-cancel delete" style="display:none">✕</a>
						<a href="javascript:void(0);" class="button btn-regular steps-image-add"> + Image </a>
						<input type="hidden" name="stepsImage[]" value=""/>
					</div>
				</div>
			</div>
			<div class="steps-row clearfix">
				<div class="steps-num">5</div>
				<div class="steps-text"><textarea name="steps[]"></textarea></div>
				<div class="steps-image">
					<div class="steps-image-uploader">
						<a href="javascript:void(0);" class="i-cancel delete" style="display:none">✕</a>
						<a href="javascript:void(0);" class="button btn-regular steps-image-add"> + Image </a>
						<input type="hidden" name="stepsImage[]" value=""/>
					</div>
				</div>
			</div>
			<div class="steps-row clearfix">
				<div class="steps-num">6</div>
				<div class="steps-text"><textarea name="steps[]"></textarea></div>
				<div class="steps-image">
					<div class="steps-image-uploader">
						<a href="javascript:void(0);" class="i-cancel delete" style="display:none">✕</a>
						<a href="javascript:void(0);" class="button btn-regular steps-image-add"> + Image </a>
						<input type="hidden" name="stepsImage[]" value=""/>
					</div>
				</div>
			</div>
			<?php }?>
			<a href="javascript:void(0);" class="steps-row-add"> + Add Rows</a>
		</dd>
		
		<dt>Tips:</dt>
		<dd><textarea name="tips"><?php echo $post['tips']; ?></textarea></dd>
		
		<dt></dt>
		<dd>
			<input type="submit" name="update" value="Save" class="button"/>
			<input type="submit" name="delete" value="Delete" class="button" onclick="return confirm('Really delete this Post?');"/>
		</dd>
	</dl>
</form>
<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>