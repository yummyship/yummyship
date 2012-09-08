<?php
if (!defined('__DIR__')) {
	define('__DIR__', dirname(__FILE__));
}
require_once __DIR__.'/../config.inc.php';
require_once 'Widget/Config.php';

header( 'Content-type: text/html; charset=utf-8' );

$response = Byends_Response::getInstance();
$request = Byends_Request::getInstance($options);

Byends_Cookie::delete('__byends_gather_title', BYENDS_BASE_URL);
Byends_Cookie::delete('__byends_gather_image', BYENDS_BASE_URL);
Byends_Cookie::delete('__byends_gather_referer', BYENDS_BASE_URL);

//deBug(iconv('gb2312','utf-8//TRANSLIT//IGNORE', $request->filter('trim')->title), 1);

if ($request->filter('trim')->image) {
	Byends_Cookie::set('__byends_gather_title', $request->filter('trim')->title, 0, BYENDS_BASE_URL);
	Byends_Cookie::set('__byends_gather_image', $request->filter('trim')->image, 0, BYENDS_BASE_URL);
	Byends_Cookie::set('__byends_gather_referer', $request->filter('trim')->referer, 0, BYENDS_BASE_URL);
	
	$response->redirect(BYENDS_ADMIN_URL.'?post&add');
}


$response->redirect(BYENDS_SITE_URL);

?>