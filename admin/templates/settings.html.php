<?php include(BYENDS_ADMIN_THEMES_DIR.'head.html.php'); ?>

<h2>Settings</h2>

<form action="<?php echo BYENDS_ADMIN_URL.'?setting'; ?>" method="post">
	<?php if( !empty($status) ) { ?>
		<div class="warn">
			<?php if( $status == 'save-succ' ) { ?>The Settings is saving!<?php } ?>
		</div>
	<?php } 
	
	$rewrite = '<option value="0" '.($options->rewrite == 0 ? 'selected="true"' : '').'>关闭</option>';
	$rewrite .= '<option value="1" '.($options->rewrite == 1 ? 'selected="true"' : '').'>启用</option>';
	
	$timeZone = '';
	foreach( Byends_Date::$timezoneList as $k => $v) {
		$selected = $k == $options->timezone ? 'selected="true"' : '';
		$timeZone .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
	}
	
	$cropType = '<option value="0" '.($options->imageConfig['cropType'] == 0 ? 'selected="true"' : '').'>缩略图片</option>';
	$cropType .= '<option value="1" '.($options->imageConfig['cropType'] == 1 ? 'selected="true"' : '').'>中间裁剪</option>';
	?>
	<dl>
		<dt>Title:</dt>
		<dd><input type="text" name="options[title]" class="long" value="<?php echo $options->title; ?>"/></dd>
		
		<dt>Description:</dt>
		<dd><input type="text" name="options[description]" class="long" value="<?php echo $options->description; ?>"/></dd>
		
		<dt>Keywords:</dt>
		<dd><input type="text" name="options[keywords]" class="long" value="<?php echo $options->keywords; ?>"/></dd>
		
		<dt>Domain:</dt>
		<dd><input type="text" name="domain" class="long" disabled style="background-color: #eee" value="<?php echo $options->domain; ?>"/></dd>
		
		<dt>StaticDomain:</dt>
		<dd><input type="text" name="options[staticDomain]" class="long" value="<?php echo $options->staticDomain; ?>"/></dd>
		
		<dt>AbsolutePath:</dt>
		<dd><input type="text" name="options[absolutePath]" class="long" value="<?php echo $options->absolutePath; ?>"/></dd>
		
		<dt>Seed:</dt>
		<dd><input type="text" name="options[seed]" value="<?php echo $options->seed; ?>"/></dd>
		
		<dt>DateWordLang:</dt>
		<dd><input type="text" name="options[lang]" value="<?php echo $options->lang; ?>"/></dd>
		
		<dt>Rewrite:</dt>
		<dd><select name="options[rewrite]"><?php echo $rewrite; ?></select></dd>
		
		<dt>Theme:</dt>
		<dd><input type="text" name="options[theme]" class="long" value="<?php echo $options->theme; ?>"/></dd>
		
		<dt>Timezone:</dt>
		<dd><select name="options[timezone]"><?php echo $timeZone; ?></select></dd>
		
		<dt>PostsNum:</dt>
		<dd><input type="text" name="options[perPage]" value="<?php echo $options->perPage; ?>"/></dd>
		
		<dt>AdminNum:</dt>
		<dd><input type="text" name="options[adminperPage]" value="<?php echo $options->adminPerPage; ?>"/></dd>
		
		<dt>AjaxNum:</dt>
		<dd><input type="text" name="options[ajaxPerPage]" value="<?php echo $options->ajaxPerPage; ?>"/></dd>
		
		<dt>CropType:</dt>
		<dd><select name="imageConfig[cropType]"><?php echo $cropType; ?></select></dd>
		
		<dt>CoverSize:</dt>
		<dd><input type="text" name="imageConfig[coverSize]" value="<?php echo implode('|', $options->imageConfig['coverSize']); ?>"/></dd>
		
		<dt>ThumbSize:</dt>
		<dd><input type="text" name="imageConfig[thumbSize]" value="<?php echo implode('|', $options->imageConfig['thumbSize']); ?>"/></dd>
		
		<dt>StepSize:</dt>
		<dd><input type="text" name="imageConfig[stepSize]" value="<?php echo implode('|', $options->imageConfig['stepSize']); ?>"/></dd>
		
		<dt>JpegQuality:</dt>
		<dd><input type="text" name="imageConfig[jpegQuality]" value="<?php echo $options->imageConfig['jpegQuality']; ?>"/></dd>
		
		<dt>Ads:</dt>
		<dd><textarea id="text" name="options[ads]"><?php echo $options->ads; ?></textarea></dd>
		
		<dt>Hiddens:</dt>
		<dd><textarea id="text" name="options[hiddens]"><?php echo $options->hiddens; ?></textarea></dd>
		<dt></dt>
		<dd>
			<input type="submit" name="update" value="Save Settings" class="button"/>
		</dd>
	</dl>
</form>

<?php include(BYENDS_ADMIN_THEMES_DIR.'foot.html.php'); ?>