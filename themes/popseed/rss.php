<?php header('Content-Type: application/rss+xml; charset=utf-8'); echo '<?xml version="1.0" encoding="utf-8"?>';?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="<?php echo BYENDS_BASE_URL; ?>feed" rel="self" type="application/rss+xml" />
<title><?php echo htmlspecialchars( $options->title ); ?></title>
<link><?php echo BYENDS_BASE_URL; ?></link>
<description><?php echo htmlspecialchars( $options->title ); ?></description>
<language>en</language>

<?php foreach( $posts as $p ) { ?>
<item>
	<title><?php echo $p['title']; ?></title>
	<link><?php echo $p['url']; ?></link>
	<description>
		<?php if( $p['image'] ) { ?>
			&lt;p&gt;
			&lt;a href=&quot;<?php echo $p['url']; ?>&quot;&gt;
				&lt;img src=&quot;<?php echo $p['thumb']; ?>&quot; alt=&quot;&quot;/&gt;
			&lt;/a&gt;
			&lt;/p&gt;
			&lt;p&gt;
				<?php echo htmlspecialchars(nl2br($p['text'])); ?>
			&lt;/p&gt;
		<?php } else { ?>
			&lt;p&gt;
				<?php echo htmlspecialchars(nl2br($p['text'])); ?>
			&lt;/p&gt;
		<?php } ?>
	</description>
	<pubDate><?php echo date('r', $p['created']); ?></pubDate>
	<guid isPermaLink="false"><?php echo $p['url']; ?></guid>
</item>
<?php } ?>

</channel>
</rss>
