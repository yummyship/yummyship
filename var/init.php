<?php

/** 开始会话 */
@session_start();

require_once 'Widget/Options.php';

if (function_exists("ini_get") && !ini_get("date.timezone") && function_exists("date_default_timezone_set")) {
	@date_default_timezone_set('UTC');
}
$options = Widget_Options::get();
Byends_Date::setTimezoneOffset($options->timezone);

$subAbsolutePath = substr($options->absolutePath, -1) == '/' ? substr($options->absolutePath, 0, -1) : $options->absolutePath;

define( 'BYENDS_BASE_URL',				'http://'.$options->domain.$subAbsolutePath );
define( 'BYENDS_STATIC_URL',			'http://'.$options->staticDomain.$subAbsolutePath );

define( 'BYENDS_THEMES_DIR',			__BYENDS_ROOT_DIR__.__BYENDS_THEMES_DIR__ );

/** 定义当前主题目录(相对路径) */
define('__BYENDS_THEME_DIR__',			__BYENDS_THEMES_DIR__.$options->theme.'/');

define( 'BYENDS_ADMIN_DIR',				__BYENDS_ROOT_DIR__.__BYENDS_ADMIN_DIR__ );
define( 'BYENDS_ADMIN_THEMES_DIR',		__BYENDS_ROOT_DIR__.__BYENDS_ADMIN_THEMES_DIR__ );

define( 'BYENDS_ADMIN_URL',				BYENDS_BASE_URL.__BYENDS_ADMIN_DIR__ );
define( 'BYENDS_ADMIN_THEMES_URL',		BYENDS_BASE_URL.__BYENDS_ADMIN_THEMES_DIR__ );

define( 'BYENDS_THEMES_STATIC_URL',		BYENDS_STATIC_URL.__BYENDS_THEME_DIR__ );
define( 'BYENDS_AVATARS_STATIC_URL',	BYENDS_STATIC_URL.__BYENDS_AVATARS_DIR__ );
define( 'BYENDS_COVERS_STATIC_URL',		BYENDS_STATIC_URL.__BYENDS_COVERS_DIR__ );
define( 'BYENDS_THUMBS_STATIC_URL',		BYENDS_STATIC_URL.__BYENDS_THUMBS_DIR__ );
define( 'BYENDS_STEPS_STATIC_URL',		BYENDS_STATIC_URL.__BYENDS_STEPS_DIR__ );
define( 'BYENDS_TEMPS_STATIC_URL',		BYENDS_STATIC_URL.__BYENDS_TEMPS_DIR__ );

define( 'BYENDS_NO_IMAGE_STATIC_URL',	BYENDS_STATIC_URL.__BYENDS_DATA_DIR__.'default.png' );
define( 'BYENDS_NO_AVATAR_STATIC_URL',	BYENDS_STATIC_URL.__BYENDS_AVATARS_DIR__.'default.jpg' );

define( 'BYENDS_COPYRIGHT_YEAR',		date('Y') > '2012' ? '2012 - '.date('Y') : '2012' );
define( 'BYENDS_COPYRIGHT_NAME',		$options->title);

// Is mod_rewrite enabled? (see .htaccess)
if( $options->rewrite ) {
	define( 'BYENDS_SITE_URL',			BYENDS_BASE_URL.'/' );
} else {
	define( 'BYENDS_SITE_URL',			BYENDS_BASE_URL.'/index.php/' );
}

define( 'BYENDS_SEED_URL',				BYENDS_SITE_URL.$options->seed.'/' );
define( 'BYENDS_TAG_URL',				BYENDS_SITE_URL.$options->tag.'/' );

define( 'BYENDS_AUTH_SIGNIN_URL',		BYENDS_SITE_URL.'auth/signin' );
define( 'BYENDS_AUTH_SIGNUP_URL',		BYENDS_SITE_URL.'auth/signup' );
define( 'BYENDS_AUTH_SIGNOUT_URL',		BYENDS_SITE_URL.'auth/signout' );
define( 'BYENDS_AUTH_FORGOT_URL',		BYENDS_SITE_URL.'auth/forgot' );

define( 'BYENDS_USER_URL',				BYENDS_SITE_URL.'user/' );
define( 'BYENDS_COOK_URL',				BYENDS_SITE_URL.'cook/' );
define( 'BYENDS_LIKES_URL',				BYENDS_SITE_URL.'likes/' );

/** 设置自动载入函数 */
function __autoLoad($className)
{
	@include_once str_replace('_', '/', $className) . '.php';
}

if( function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() ) {
	$_GET = Byends_Paragraph::stripslashesDeep( $_GET );
	$_POST = Byends_Paragraph::stripslashesDeep( $_POST );
	$_COOKIE = Byends_Paragraph::stripslashesDeep($_COOKIE);
	
	reset($_GET);
	reset($_POST);
	reset($_COOKIE);
}

?>