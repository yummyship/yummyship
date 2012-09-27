<?php
if (!defined('__DIR__')) {
    define('__DIR__', dirname(__FILE__));
}

if ( @file_exists(__DIR__.'/../install.php') ) {
	header('Location: ../install.php');
	exit;
}

require_once __DIR__.'/../config.inc.php';
require_once 'init.php';

$userInstance = Widget_User::getInstance();
$userInstance->pass('administrator');

$response = Byends_Response::getInstance();
$request = Byends_Request::getInstance();


