<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Add Post</h2>

<form action="" method="post" id="recipeFm" name="recipeFm">
	<?php if( !empty($status) ) { ?>
		<div class="warn">
			<?php if( $status == 'title-empty' ) { ?>The Title was empty!<?php } ?>
			<?php if( $status == 'cover-empty' ) { ?>The Cover was empty!<?php } ?>
			<?php if( $status == 'ingredients-empty' ) { ?>The Ingredients was empty!<?php } ?>
			<?php if( $status == 'steps-empty' ) { ?>The Steps was empty!<?php } ?>
			<?php if( $status == 'not-logged-in' ) { ?>The name or password was not correct!<?php } ?>
			<?php if( $status == 'download-failed' ) { ?>Couldn't load the image!<?php } ?>
			<?php if( $status == 'duplicate-image' ) { ?>This image was already posted!<?php } ?>
			<?php if( $status == 'thumbnail-failed' ) { ?>Couldn't create a thumbnail of the image!<?php } ?>
			<?php if( $status == 'rename-failed' ) { ?>Couldn't rename the image!<?php } ?>
		</div>
	<?php } ?>
	<dl>
		<dt>Created:</dt>
		<dd>
			<input type="text" name="created" id="created" value="<?php echo date('Y-m-d H:i:s', $widget->timeStamp);?>"/>
			<script type="text/javascript" src="<?php echo BYENDS_ADMIN_THEMES_URL; ?>calendar.js?12.9.2.1532"></script>
			<script type="text/javascript">
				created = new Calendar( 'created' );
			</script>
		</dd>
			
		<dt>Title:</dt>
		<dd><input type="text" id="title" name="title" class="long" value="<?php echo ($request->filter('trim')->title ? $request->filter('trim')->title : base64_decode(Byends_Cookie::get('__byends_gather_title'))); ?>"/></dd>
		
		<dt>Cover:</dt>
		<dd class="cover">
			<div class="cover-uploader">
				<a href="javascript:void(0);" class="i-cancel delete" style="display:none">✕</a>
				<a href="javascript:void(0);" class="button btn-regular cover-add">+ Add Cover </a>
				<input type="hidden" id="cover" name="cover" value="<?php echo ($request->filter('trim')->cover ? $request->filter('trim')->cover : Byends_Cookie::get('__byends_gather_cover')); ?>"/>
			</div>
		</dd>
		
		<dt>Brief:</dt>
		<dd><textarea id="brief" name="brief"><?php echo $request->filter('trim')->brief; ?></textarea></dd>
		
		<dt>Ingredients:</dt>
		<dd class="ingredients">
			<div class="ingredients-row-head">
				<span class="ingredients-row-name">Ingredients Name</span>
				<span class="ingredients-row-dosage">Dosage(option)</span>
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
			<div class="ingredients-row">
				<span class="ingredients-row-name"><input type="text" name="ingredients[]" /></span>
				<span class="ingredients-row-dosage"><input type="text" name="dosage[]" /></span>
			</div>
			
			<a href="javascript:void(0);" class="ingredients-row-add"> + Add Rows</a>
		</dd>
		
		<dt>Steps:</dt>
		<dd class="steps">
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
			
			<a href="javascript:void(0);" class="steps-row-add"> + Add Rows</a>
		</dd>
		
		<dt>Tips:</dt>
		<dd><textarea name="tips"><?php echo $request->filter('trim')->tips; ?></textarea></dd>
		
		<dt></dt>
		<dd>
			<input type="submit" name="insert" value="Add Post" class="button"/>
			<input type="button" name="clear" value="Clear Data" class="button"/>
		</dd>
	</dl>
</form>

<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>