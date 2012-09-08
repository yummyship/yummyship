<?php require 'header.php'; ?>

<div id="container">
	<div id="content" class="clearfix">
		<div class="seed">
			<h1><?php echo $content['title']; ?></h1><?php 
				$meta = ' srcWidth="'.$content['width'].'" srcHeight="'.$content['height'].'"';
				if($content['width'] > 642){
					$meta = ' width="642" '.$meta;
				}
			?>
			<div class="meta clearfix">
				<div class="sprite clock published"><?php echo date('M d, Y', $content['modified']).'('.$content['dateWord'].')'; ?></div>
			</div>
			<div class="cover">
				<img src="<?php echo $content['cover']; ?>" title="<?php echo $content['title']; ?>" alt="<?php echo $content['title']; ?>"<?php echo $meta;?> />
				<?php if(FALSE) {?>
				<a class="zoom" href="<?php echo $content['zoomPermalink']; ?>" title="Full Image"></a>
				<?php }?>
			</div>
			<div class="recipe-info">
				<?php if ($content['brief']) {?>
				<p class="recipe-info-title"><b>Brief:</b></p>
				<?php echo Byends_Paragraph::cutParagraph($content['brief']); ?>
				<?php }?>
				<p class="recipe-info-title"><b>Ingredients:</b>
				<table class="recipe-info-ingredients" cellpadding="0" cellspacing="1">
				<tbody>
				<?php 
				$countIng = count($content['ingredients']);
				$table = "<tr>";
				$i = 1;
				foreach ($content['ingredients'] as $k => $v) {
					$table .= '<td>'.$content['tag'][$k]['name'].'<span>'.$v."</span></td>";
					if ($i%2 == 0 ) $table .= "</tr><tr>";
					$i ++;
				}
				if ($countIng > 1 && $countIng%2 == 1) {
					$table .= '<td>&nbsp;</td>';					
				}
				echo $table;
				?></tr>
				</tbody>
				</table>
				<p class="recipe-info-title"><b>Steps:</b>
				<div class="recipe-info-steps">
					<ol>
					<?php 
					$li = '';
					foreach ($content['steps'] as $k => $v) {
						list($stepText, $stepImage) = explode('@#|@', $v);
						$li .= '<li class="clearfix"><em>'.($k+1).'.</em>';
						$li .= '<span>'.$stepText.'</span>';
						if ($stepImage) {
							$li .='<img src="'.$stepImage.'" alt="'.$content['title'].' Step '.($k+1).'" width="'.$options->imageConfig['stepSize'][0].'" height="'.$options->imageConfig['stepSize'][1].'" />';
						}
						$li .= '</li>';
					} 
					echo $li;
					?>
					</ol>
				</div>
				<?php if ($content['tips']) {?>
				<p class="recipe-info-title"><b>Tips:</b></p>
				<?php echo Byends_Paragraph::cutParagraph($content['tips']); ?>
				<?php }?>
			</div>
			<div class="ads"><?php echo $options->ads; ?></div>
			<div class="share-link">
				<a class="sprite link" href="<?php echo $content['permalink']; ?>" title="<?php echo $content['title']; ?> Link">Link</a>
				<input id="link-url" type="text" value="<?php echo $content['permalink']; ?>" readonly="readonly">
			</div>
			<dl class="latest clearfix">
				<dt>The Latest Seeds:</dt>
				<?php
				foreach ( $latestContent as $k => $v ) {
					echo '<dd><a href="'.$v['permalink'].'" title="'.$v['title'].'"><img src="'.$v['thumb'].'" alt="'.$v['title'].'" /></a></dd>';
				}
				?>
			</dl>
			<?php require 'comments.php'; ?>
		</div>
	
		<?php require 'sidebar.php'; ?>
	</div>

</div>

<?php require 'footer.php'; ?>