<?php
if( version_compare(PHP_VERSION, '5.0.0') == -1 ) {
	// probably useless, because PHP 4 wont parse this document.
	die( "You need at least PHP 5.0.0 to run BYENDS. Your current PHP version is ".PHP_VERSION );
}
//error_reporting(0);

require_once 'config.inc.php';

header( 'Content-type: text/html; charset=utf-8' );

$createTablesSQL = array(
	"CREATE TABLE IF NOT EXISTS `".BYENDS_TABLE_CONTENTS."` (
	  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `title` varchar(200) DEFAULT NULL,
	  `slug` varchar(200) DEFAULT NULL,
	  `uid` int(10) unsigned DEFAULT '0',
	  `created` int(10) unsigned DEFAULT '0',
	  `modified` int(10) unsigned DEFAULT '0',
	  `coverHash` varchar(32) DEFAULT NULL,
	  `coverExt` varchar(5) DEFAULT NULL,
	  `coverSize` varchar(16) DEFAULT NULL,
	  `brief` text DEFAULT NULL,
	  `ingredients` text DEFAULT NULL,
	  `steps` text DEFAULT NULL,
	  `tips` text DEFAULT NULL,
	  `type` varchar(16) DEFAULT 'post',
	  `status` varchar(16) DEFAULT 'waiting',
	  `allowComment` char(1) DEFAULT '0',
	  `commentsNum` int(10) unsigned DEFAULT '0',
	  `favoritesNum` int(10) unsigned DEFAULT '0',
	  `views` int(10) unsigned DEFAULT '0',
	  PRIMARY KEY (`cid`),
	  KEY `slug` (`slug`),
	  KEY `created` (`created`),
	  KEY `modified` (`modified`),
	  KEY `coverHash` (`coverHash`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
	
	"CREATE TABLE IF NOT EXISTS `".BYENDS_TABLE_FAVORITES."` (
	  `uid` int(10) unsigned NOT NULL,
	  `cid` int(10) unsigned NOT NULL,
	  `created` int(10) unsigned DEFAULT '0',
	  PRIMARY KEY (`uid`,`cid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;",
		
	"CREATE TABLE IF NOT EXISTS `".BYENDS_TABLE_METAS."` (
	  `mid` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `name` varchar(200) DEFAULT NULL,
	  `slug` varchar(200) DEFAULT NULL,
	  `type` varchar(16) NOT NULL,
	  `description` varchar(200) DEFAULT NULL,
	  `count` int(10) unsigned DEFAULT '0',
	  PRIMARY KEY (`mid`),
	  KEY `name` (`name`),
	  KEY `slug` (`slug`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;",
	
	"CREATE TABLE IF NOT EXISTS `".BYENDS_TABLE_OPTIONS."` (
	  `name` varchar(32) NOT NULL,
	  `value` text,
	  PRIMARY KEY (`name`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;",
	
	"CREATE TABLE IF NOT EXISTS `".BYENDS_TABLE_RELATE."` (
	  `cid` int(10) unsigned NOT NULL,
	  `mid` int(10) unsigned NOT NULL,
	  PRIMARY KEY (`cid`,`mid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;",
	
	
	"CREATE TABLE IF NOT EXISTS `".BYENDS_TABLE_USERS."` (
	  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `name` varchar(32) DEFAULT NULL,
	  `password` varchar(64) DEFAULT NULL,
	  `mail` varchar(200) DEFAULT NULL,
	  `url` varchar(200) DEFAULT NULL,
	  `created` int(10) unsigned DEFAULT '0',
	  `logged` int(10) unsigned DEFAULT '0',
	  `group` varchar(16) NOT NULL DEFAULT 'visitor',
	  `authCode` varchar(64) DEFAULT NULL,
	  `description` text,
	  `avatar` varchar(32) DEFAULT NULL,
	  `notify` varchar(255) DEFAULT NULL,
	  `status` varchar(16) DEFAULT 'normal',
	  PRIMARY KEY (`uid`),
	  UNIQUE KEY `name` (`name`),
	  UNIQUE KEY `mail` (`mail`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
);

$requirements = array(
	'File Access' => array(
		'Data directory is writeable' => array(
			'message' => 'Make sure PHP has the necessary priviliges (chmod) to write to the <code>data/</code> directory.',
			'value' => @is_writeable( __BYENDS_ROOT_DIR__.__BYENDS_DATA_DIR__ )
		),
	),
	
	'Database' => array(
		'Connection established' => array(
			'message' => 'Check your database settings in <code>lib/BYENDS_config.class.php</code>',
			'value' => @mysql_connect( $dbCfg['host'], $dbCfg['user'], $dbCfg['password'] )
		),
		'MySQL Version >= 4.0' => array(
			'value' => ( version_compare(@mysql_get_server_info(),'4.0') != -1 )
		),
		'Database exists' => array(
			'message' => 'The database BYENDS will be installed in must already exist. The installer will not attempt to create it.',
			'value' => @mysql_select_db( $dbCfg['database'] )
		)
	),
	
	'PHP' => array(
		'cURL or URL fopen wrappers enabled' => array(
			'message' => 'BYENDS needs <code>cURL</code> or <code>allow_url_fopen</code> to be enabled to copy images from other sites.',
			'value' => (
				is_callable( 'curl_init' ) ||
				iniEnabled( 'allow_url_fopen' )
			)
		),
		'GD library installed' => array(
			'message' => 'The GD library (PHP extension) is needed to manipulate images.',
			'value' => is_callable( 'gd_info' )
		)
	)
);

$gmtTimeStamp = Byends_Date::gmtTime();
$timeStamp = Byends_Date::timeStamp($gmtTimeStamp);

$suggestedPath = preg_replace( '#install.php$#i', '', $_SERVER['SCRIPT_NAME'] );
$recommendations = array(
	'PHP Settings' => array(
		'Safe Mode deactivated' => array(
			'message' => 
				'It is strongly recommended to disable <code>safe_mode</code>. If safe_mode is on, you\'re very likely to '
				.'encounter problems when posting images. You might want to ask Google about '
				.'&quot;<a href="http://www.google.com/search?q=safe_mode+mkdir">safe_mode mkdir</a>&quot; and '
				.'&quot;<a href="http://www.google.com/search?q=chmod+setuid">chmod setuid</a>&quot;.',
			'value' => !iniEnabled( 'safe_mode' )
		),
		'Magic Quotes deactivated' => array(
			'message' => 
				'If <code>magic_quotes_gpc</code> is enabled, BYENDS will have to do some extra work to revert this '
				.'stupid behaviour. Turn it off!',
			'value' => !iniEnabled( 'magic_quotes_gpc' )
		),
		'Register Globals deactivated' => array(
			'message' => 
				'Though there is no known problem with BYENDS and the <code>register_globals</code> option, it is '
				.'generally a good idea to turn it off.',
			'value' => !iniEnabled( 'register_globals' )
		 ),
		'Memory limit >= 4M' => array(
			'message' => 
				'If your <code>memory_limit</code> setting is too low, you might experience problems when posting '
				.'large images. For instance, PHP needs about 6mb of RAM to create a thumbnail from a 1600x1280 image.',
			'value' => ( humanToBytes(ini_get( 'memory_limit' )) > humanToBytes('4M') )
		)
	)
);


$requirementsMet = true;
foreach( array_keys($requirements) as $i ) {
	foreach( array_keys($requirements[$i]) as $j ) {
		if( empty($requirements[$i][$j]['value']) ) {
			$requirementsMet = false;
		}
	}
}



function humanToBytes( $s ) {
	$s = trim( $s );
	$last = strtolower( $s{strlen($s)-1} );
	switch( $last ) {
		case 'g': $s *= 1024;
		case 'm': $s *= 1024;
		case 'k': $s *= 1024;
	}
	
    return $s;
}

function iniEnabled( $s ) {
	return in_array( 
		strtolower( ini_get( $s ) ), 
		array( 'on', '1', 'true', 'yes' ) 
	);
}

function installBYENDS( &$sql, &$errors ) {
	global $gmtTimeStamp, $dbCfg;
	$db = new Byends_Db(
		$dbCfg['host'],
		$dbCfg['database'],
		$dbCfg['user'],
		$dbCfg['password']
	);
	
	$tables = $db->query( 'SHOW TABLES LIKE "'.BYENDS_TABLE_CONTENTS.'"' );
	if( !empty($tables) ) {
		$errors['table-exists'] = true;
		return false;
	}
	
	foreach( $sql as $q ) {
		if( !$db->query($q) ) {
			$errors['sql-error'] = $db->getError();
			return false;
		}
	}
	
	$db->insertRow( BYENDS_TABLE_USERS, array(
		'name' => $_POST['name'],
		'password' => Byends_Paragraph::hash($_POST['pass']),
		'mail' => $_POST['mail'],
		'url' => $_SERVER["HTTP_HOST"],
		'created' => $gmtTimeStamp,
		'group' => 'administrator'
	));
	
	$imageConfig = array(
		'coverSize'   => '526|349',
		'thumbSize'   => '250|192',
		'stepSize'    => '200|132',
		'jpegQuality' => 80,
		'cropType'    => 0
	);
	
	$options = array(
		'theme' => 'popseed',
		'timezone' => $_POST['timezone'],
		'title' => 'BYENDS',
		'description' => 'So Cool, isn\'t it?',
		'keywords' => 'BYENDS',
		'rewrite' => 0,
		'domain' => $_SERVER["HTTP_HOST"],
		'staticDomain' => $_SERVER["HTTP_HOST"],
		'absolutePath' => str_replace(basename($_SERVER["REQUEST_URI"]), '', $_SERVER["REQUEST_URI"]),
		'seed' => 'recipe',
		'tag' => 'tag',
		'perPage' => 12,
		'adminPerPage' => 9,
		'ajaxPerPage' => 9,
		'lang' => 'en',
		'imageConfig' => serialize($imageConfig),
		'ads' => '',
		'hiddens' => ''
	);
	foreach ( $options as $k => $v) {
		$db->insertRow(	BYENDS_TABLE_OPTIONS, array(
				'name'  => $k,
				'value' => $v
		));
	}
	
	
	return true;
}

function _u()
{
	$url = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	if (isset($_SERVER["QUERY_STRING"])) {
		$url = str_replace("?" . $_SERVER["QUERY_STRING"], "", $url);
	}

	return $url;
}

$mode = 'check';
$errors = array();
if( 
	isset($_POST['install']) && 
	!empty($_POST['name']) && 
	!empty($_POST['pass']) && 
	!empty($_POST['pass2']) &&
	!empty($_POST['mail'])
) {
	if( $_POST['pass'] == $_POST['pass2'] ) {
		if( installBYENDS( $createTablesSQL, $errors ) ) {
			$mode = 'installed';
		} else {
			$mode = 'install-failed';
		}
	}
	else {
		$errors['passwords-not-equal'] = true;
	}
} else if( isset($_POST['install']) ) {
	$errors['missing-data'] = true;
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
	<title>Install: BYENDS</title>
	<link rel="stylesheet" type="text/css" href="admin/templates/admin.css" />
	<link rel="Shortcut Icon" href="admin/templates/BYENDS.ico" />
</head>
<style type="text/css">
div.warn {
	position: static;
}
</style>
<body>

<div class="install">
	<?php if( $mode == 'check' ) { ?>
		<h1>Welcome to BYENDS!</h1>
		<p>
			Install BYENDS in 3 simple steps:
		</p>
		
		<ol>
			<li>Edit your settings in <code>config.inc.php</code></li>
			<li>Set the chmod of the <code>data/</code> directory so PHP can write to it</li>
			<li>Enter the name and password for your first user below and click install</li>
		</ol>
		
		<p>
			The following is the result of a short system check. <strong>All requirements must be met in order to 
			install BYENDS.</strong>
		</p>
		
		<h1>Requirements</h1>
		<?php foreach( $requirements as $collectionTitle => $collection ) {?>
			<div class="collection">
				<h2><?php echo $collectionTitle; ?></h2>
				<?php foreach( $collection as $title => $r ) {?>
					<?php if( $r['value'] ) { ?>
						<div class="check ok">
							<h3><?php echo $title; ?>: OK</h3>
						</div>
					<?php } else { ?>
						<div class="check failed">
							<h3><?php echo $title; ?>: FAILED</h3>
							<?php if( isset($r['message']) ) { ?>
								<p><?php echo $r['message']; ?></p>
							<?php } ?>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		<?php } ?>

		<h1>Recommendations</h1>
		<?php foreach( $recommendations as $collectionTitle => $collection ) {?>
			<div class="collection">
				<h2><?php echo $collectionTitle; ?></h2>
				<?php foreach( $collection as $title => $r ) {?>
					<?php if( $r['value'] ) { ?>
						<div class="check ok">
							<h3><?php echo $title; ?>: OK</h3>
						</div>
					<?php } else { ?>
						<div class="check failed">
							<h3><?php echo $title; ?>: FAILED</h3>
							<?php if( isset($r['message']) ) { ?>
								<p><?php echo $r['message']; ?></p>
							<?php } ?>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		<?php } ?>
		
		<a name="form"></a>
		<?php if( $requirementsMet ) { ?>
			<form action="install.php#form" method="post">
				<h1>User Settings - <?php echo date('Y-m-d H:i:s', $timeStamp)?></h1>
				<p>
					Please enter the name and password for the admin user and click <em>Install BYENDS</em> 
					to create the database tables.
				</p>
				<?php if( isset($errors['passwords-not-equal']) ) { ?>
					<div class="warn">The passwords you entered did not match!</div>
				<?php } ?>
				<?php if( isset($errors['missing-data']) ) { ?>
					<div class="warn">Please enter a name and a password!</div>
				<?php } 
				$timeZone = '';
				foreach( Byends_Date::$timezoneList as $k => $v) {
					$selected = $k == '28800' ? 'selected="true"' : '';
					$timeZone .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
				}
				?>
				<dl>
					<dt>BaseUrl:</dt>
						<dd><input style="background-color: #eee" type="text" name="baseUrl" disabled value="<?php echo dirname(_u()).'/'; ?>" class="long"/></dd>
					<dt>Name:</dt>
						<dd><input type="text" name="name"/></dd>
					<dt>Mail:</dt>
						<dd><input type="text" name="mail"/></dd>
					<dt>Password:</dt>
						<dd><input type="password" name="pass"/></dd>
					<dt>(repeat):</dt>
						<dd><input type="password" name="pass2"/></dd>
					<dt>TimeZone:</dt>
						<dd><select name="timezone"><?php echo $timeZone; ?></select></dd>
					<dt>&nbsp;</dt>
						<dd><input type="submit" class="button" name="install" value="Install BYENDS"/></dd>
				</dl>
			</form>
		<?php } ?>
	<?php } else if( $mode == 'install-failed' ) {?>
		<h1>Installation failed</h1>
		<?php if( isset($errors['table-exists']) ) { ?>
			<div class="warn">The BYENDS Posts-Table exists. Is BYENDS already installed?</div>
		<?php } ?>
		<?php if( isset($errors['sql-error']) ) { ?>
			<div class="warn"><?php echo $errors['sql-error']; ?></div>
		<?php } ?>
	<?php } else if( $mode == 'installed' ) {?>
		<h1>Installation successful</h1>
		<p>
			Please remove this file (admin/install.php) from your server now.
		</p>
		<p>
			You may head over to the <a href="./admin/">admin menu</a> or vist 
			<a href="./">your newly created BYENDS</a>.
		</p>
	<?php } ?>
</div>

</body>
</html>