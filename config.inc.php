<?php 

/** 定义根目录 */
define('__BYENDS_ROOT_DIR__', dirname(__FILE__));

/** 定义主题目录(相对路径) */
define('__BYENDS_THEMES_DIR__', '/themes/');

/** 定义data目录(相对路径) */
define('__BYENDS_DATA_DIR__', '/data/');

/** 定义avatar目录(相对路径) */
define('__BYENDS_AVATARS_DIR__', __BYENDS_DATA_DIR__ . 'avatars/');

/** 定义covers目录(相对路径) */
define('__BYENDS_COVERS_DIR__', __BYENDS_DATA_DIR__ . 'covers/');

/** 定义thumbs目录(相对路径) */
define('__BYENDS_THUMBS_DIR__', __BYENDS_DATA_DIR__ . 'thumbs/');

/** 定义steps目录(相对路径) */
define('__BYENDS_STEPS_DIR__', __BYENDS_DATA_DIR__ . 'steps/');

/** 定义temps目录(相对路径) */
define('__BYENDS_TEMPS_DIR__', __BYENDS_DATA_DIR__ . 'temps/');

/** 后台路径(相对路径) */
define('__BYENDS_ADMIN_DIR__', '/admin/');

/** 后台主题路径(相对路径) */
define('__BYENDS_ADMIN_THEMES_DIR__', __BYENDS_ADMIN_DIR__.'templates/');

/** 设置包含路径 */
@set_include_path(get_include_path() . PATH_SEPARATOR .
		__BYENDS_ROOT_DIR__ . '/var' . PATH_SEPARATOR .
		__BYENDS_ROOT_DIR__ . substr(__BYENDS_THEMES_DIR__, 0, -1));


/** 载入 段落 支持 */
require_once 'Byends/Paragraph.php';

/** 载入 响应 支持 */
require_once 'Byends/Response.php';

/** 载入 日期 支持 */
require_once 'Byends/Date.php';

/** 载入 请求  支持 */
require_once 'Byends/Request.php';

/** 载入 数据库 支持 */
require_once 'Byends/Db.php';

$dbCfg = array(
	'host' => 'localhost',
	'database' => 'lovewithyummy',
	'user' => 'root',
	'password' => '',
	'prefix' => 'lwy_'
);
$db = new Byends_Db($dbCfg['host'], $dbCfg['database'], $dbCfg['user'], $dbCfg['password']);
$db->set($db);

define( 'BYENDS_TABLE_CONTENTS',		$dbCfg['prefix'].'contents' );
define( 'BYENDS_TABLE_USERS',			$dbCfg['prefix'].'users' );
define( 'BYENDS_TABLE_OAUTH_USERS',		$dbCfg['prefix'].'oauth_users' );
define( 'BYENDS_TABLE_METAS',			$dbCfg['prefix'].'metas' );
define( 'BYENDS_TABLE_RELATE',			$dbCfg['prefix'].'relationships' );
define( 'BYENDS_TABLE_OPTIONS',			$dbCfg['prefix'].'options' );
define( 'BYENDS_TABLE_FAVORITES',		$dbCfg['prefix'].'favorites' );

/**
 * 用于调试
 */
function deBug($str, $isContinue = false, $varDump = true, $style = 'color:red') {
	if (is_array ( $str ) || is_object ( $str ) || is_resource ( $str )) {
		echo '<pre>';
		$varDump ? print_r ( $str ) : var_export ( $str );
		echo '</pre>';
	} else {
		echo "<div style='{$style}'>" . $str . "</div>";
	}
	if (! $isContinue) {
		exit ();
	}
}

?>