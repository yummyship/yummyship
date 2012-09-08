<?php 
if ( @file_exists('./install.php') ) {
	header('Location: install.php');
	exit;
}

require_once 'config.inc.php';
require_once 'Widget/Config.php';

//header( 'Content-type: text/html; charset=utf-8' );


$request = Byends_Request::getInstance($options);
$pathInfo = $request->getPathInfo();

$ver = '12.9.8.1555';

/** index */
if ( $request->match($pathInfo, 'index') || $request->match($pathInfo, 'index_page') ){
	$page = $request->match($pathInfo, 'index_page');
	$page = $page ? $page[1]-1 : 0;
	$widget = Widget_Content::getInstance();
	$contents = $widget->setCondtion(array('page' => $page))->select();
	
	if($page > $widget->getTotalPages()){
		$current = '404';
		require $widget->need('404.php');
	}
	else {
		$current = 'index';
		$pages = $widget->getPages();
		require $widget->need('index.php');
	}
}

/** popular */
elseif ( $request->match($pathInfo, 'popular') ||  $request->match($pathInfo, 'popular_page')) {
	$current = 'popular';
	
	$page = $request->match($pathInfo, 'popular_page');
	$page = $page ? $page[1]-1 : 0;
	
	$widget = Widget_Content::getInstance();
	$condition = array(
		'page' => $page,
		'order' => array('views', 'DESC')
	);
	$contents = $widget->setCondtion($condition)->select(); 
	if($page > $widget->getTotalPages()){
		$current = '404';
		require $widget->need('404.php');
	}
	else {
		$pages = $widget->getPages();
		require $widget->need('index.php');
	}
}
/** feed */
// else if( $request->match($pathInfo, 'feed') ) {
// 	$current = 'feed';
// 	$widget = Widget_Content::getInstance();
//	
// 	require $widget->need('feed.php');
// }

/** seed and zoom */
else if( $request->match($pathInfo, 'seed')	|| $request->match($pathInfo, 'zoom') ) {
	$current = $options->seed;
	$zoom = $request->match($pathInfo, 'zoom');
	$cid =  $request->match($pathInfo, 'seed');
	$cid = $cid ? $cid[1] : $zoom[1];
	
	$widget = Widget_Content::getInstance();
	
	if( !$cid || !intval($cid)) {
		$current = '404';
		require $widget->need('404.php');
	}
	else {
		$content = $widget->setCondtion(array('cid' => $cid))->select();
		
		if( count($content) ) {
			$widget->updateViews($cid);
			
			if( $zoom ) {
				$current = 'zoom';
				require $widget->need('zoom.php');
			}
			else {
				$widget->setPerPage(9);
				
				$order = array('views', 'DESC');
				$popularContent = $widget->setCondtion(array('cid' => 0, 'order' => $order))->select();
				
				if( $content['tagIdStr'] ) {
					$relatedContent = $widget->selectRelated($cid, $content['tagIdStr']);
					
					if( count($relatedContent) < 9 ) {
						$relatedContent = array_merge($relatedContent, $widget->selectRandom(9 - count($relatedContent)));
					}
				}
				else {
					$relatedContent = $widget->selectRandom(9);
				}
				
				$widget->setPerPage(10);
				$condition = array(
					'cid' => 0,
					'order' => array('cid', 'DESC')
				);
				$latestContent = $widget->setCondtion($condition)->select();
				require $widget->need('seed.php');
			}
		}
		else {
			$current = '404';
			require $widget->need('404.php');
		}
	}
}

/** api */
else if( $request->match($pathInfo, 'api') || $request->match($pathInfo, 'apiDo')) {
	$widget = Widget_Content::getInstance();
	$apiDo = $request->match($pathInfo, 'apiDo');
	$apiDo = $apiDo[1];
	
	if (!$request->isAjax() && $apiDo <> 'upload') {
		$current = '404';
		require $widget->need('404.php');
	}
	else {
		$current = $request->filter('trim')->action;
		$deniedMsg = json_encode(array(
				'result' => 0,
				'message' => 'Access denied!'
		));
		
		switch ($apiDo) {
			case 'rc':
				if (($current != 'index' && $current != 'popular')) {
					echo $deniedMsg;
					exit;
				}
				
				$page = $request->filter('trim', 'int')->start;
				$page = $page > 0 ? $page : 0;
				$size = $request->filter('trim', 'int')->size;
				$size = $size > 1 && $size < 31 ? $size : $options->ajaxPerPage;
				$order = $current == 'popular' ? array('views', 'DESC') : array('cid', 'DESC');
				$condition = array(
					'ajaxNum' => $size, 
					'page'    => $page, 
					'order'   => $order
				);
				$contents = $widget->setCondtion($condition)->select();
				
				if ($page > $widget->getTotalPages()) {
					echo json_encode(array(
							'seeds' => array(),
							'next' => ''
					));
					exit;
				}
				$seeds = array();
				foreach ($contents as $k => $v) {
					$seeds[] = array(
							'cid' => $v['cid'],
							'image' => $v['cover'],
							'permalink' => $v['permalink'],
							'title' => $v['title'],
							'thumb' => $v['thumb'],
							'description' => Byends_Paragraph::subStr($v['stripBrief'] ? $v['stripBrief'] : $v['title'], 0, 18),
							'dateWord' => $v['dateWord'],
							'viewsWord' => $v['viewsWord'],
							'favorite' => $v['favorite'],
							'favoritesNum' => $v['favoritesNum']
					);
				}
				echo json_encode(array(
						'seeds' => $seeds,
						'next' => $page + 1
				));
				exit;
				break;
			case 'save-recipe':
				$cid = $widget->request->filter('trim', 'int')->ri;
				$content = $widget->setCondtion(array('cid' => $cid))->select($cid);
				
				if (NULL === $widget->uid || !$content) {
					echo $deniedMsg;
					exit;
				}
				else {
					$cookInstance = Widget_Cook::getInstance();
					echo $cookInstance->doFavorite($cid);
				}
				break;
			case 'upload':
				$instanceUpload = Widget_Upload::getInstance();
				$uploadType = $request->filter('trim')->uploadType;
				if ($current == 'upload' || $current == 'fetch') {
					$reslut = $instanceUpload->setUploadType($uploadType)->upload();
					if ($current == 'upload') {
						echo '<script>parent.frameFileUploadComplete('.json_encode($reslut).');</script>';
					}
					elseif ($current == 'fetch') {
						echo json_encode($reslut);
					}
				}
				exit;
				break;
			default:
				echo $deniedMsg;
				break;
		}
		
		
	}
}

/** random */
else if( $request->match($pathInfo, 'random')) {
	$widget = Widget_Content::getInstance();
	$current = 'random';

	$content = $widget->selectRandom();
	if (count($content)) {
		$widget->response->redirect($content[0]['permalink']);
	}
	else {
		$widget->response->redirect(BYENDS_SITE_URL);
	}
}

/** auth */
else if ($request->match($pathInfo, 'auth')) {
	$auth = $request->match($pathInfo, 'auth');
	$widget = Widget_User::getInstance();
	
	switch ($auth[1]) {
		case 'signin':
			$current = 'signin';
			if ($widget->hasLogin()) {
				$widget->response->redirect(BYENDS_SITE_URL);
			}
			if ($request->isPost()) {
				$widget->login();
			}
			$notice = $widget->notice;
			require $widget->need('signin.php');
			break;
		case 'signup':
			$current = 'signup';
			require $widget->need('signup.php');
			break;
		case 'signout':
			$current = 'signout';
			$widget->logout();
			break;
		case 'forgot':
			$current = 'forgot';
			require $widget->need('forgot.php');
			break;
		default:
			$current = '404';
			$widget->hasLogin();
			require $widget->need('404.php');
			break;
	}
}
/** cook */
else if ($request->match($pathInfo, 'cook') 
		|| $request->match($pathInfo, 'cook_page')) {
	$current = 'cook';
	$cook = $request->match($pathInfo, 'cook');
	$page = $request->match($pathInfo, 'cook_page');
	$page = $page ? $page[2]-1 : 0;
	
	$widget = Widget_Cook::getInstance();
	$user = Widget_User::getInstance();
	
	$userInfo = $user->getUser(array('name' => $cook[1]));
	
	if ($userInfo) {
		$condition = array(
			'page' => $page, 
			'uid' => $userInfo->uid,
			'order' => array('created', 'DESC')
		);
		$contents = $widget->setCondtion(array('page' => $page, 'uid' => $userInfo->uid))->select();
		
		if($page > $widget->getTotalPages()){
			$current = '404';
			require $widget->need('404.php');
		}
		else {
			$pages = $widget->getPages();
			require $widget->need('cook.php');
		}
		
	}
	else {
		$current = '404';
		require $widget->need('404.php');
	}
}
/** likes */
else if ($request->match($pathInfo, 'likes')
		|| $request->match($pathInfo, 'likes_page')) {
	$current = 'likes';
	$likes = $request->match($pathInfo, 'likes');
	$page = $request->match($pathInfo, 'likes_page');
	$page = $page ? $page[2]-1 : 0;
	$widget = Widget_Cook::getInstance();
	$user = Widget_User::getInstance();

	$userInfo = $user->getUser(array('name' => $likes[1]));

	if ($userInfo) {
		$condition = array(
				'page' => $page,
				'uid' => $userInfo->uid,
				'order' => array('created', 'DESC')
		);
		$contents = $widget->setCondtion(array('page' => $page, 'uid' => $userInfo->uid))->favorites();

		if($page > $widget->getTotalPages()){
			$current = '404';
			require $widget->need('404.php');
		}
		else {
			$pages = $widget->getPages();
			require $widget->need('cook.php');
		}

	}
	else {
		$current = '404';
		require $widget->need('404.php');
	}
}
/** other */
else {
	$widget = Widget_Content::getInstance();
	$current = '404';
	require $widget->need('404.php');
}

?>