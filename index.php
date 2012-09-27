<?php 
if ( @file_exists('./install.php') ) {
	header('Location: install.php');
	exit;
}

require_once 'config.inc.php';
require_once 'init.php';


//header( 'Content-type: text/html; charset=utf-8' );

$request = Byends_Request::getInstance();
$request->setRouting($options->routingTable);
$pathInfo = $request->getPathInfo();

$ver = '2012.9.27.1254';
$metaInfo = (object)array(
	'title' => 'Yummyship — Page Not Found',
	'keywords' => '404,Yummyship,Page Not Found',
	'description' => 'The Page In Yummyship Not Found.'
);
$current = $request->match($pathInfo);

switch ($current[0]) {
	/** index | popular */
	case 'index':
	case 'index_page':
	case 'popular':
	case 'popular_page':
		$page = isset($current[1]) && $current[1] > 0 ? $current[1]-1 : 0;
		$widget = Widget_Content::getInstance();
		$current = false === strpos($current[0], 'popular') ? 'index' : 'popular';
		$condition = $current == 'index' ? array('page' => $page) : 
					array('page' => $page, 'order' => array('views', 'DESC'));
		$contents = $widget->setCondition($condition)->select();
		
		if($page > $widget->getTotalPages()){
			$current = '404';
			require $widget->need('404.php');
		}
		else {
			$metaInfo->title = $options->title.' - '.$options->description;
			$metaInfo->keywords = $options->keywords;
			$metaInfo->description = $options->description;
			$pages = $widget->getPages();
			require $widget->need('index.php');
		}
		exit;
	
	/** seed | zoom */
	case 'seed':
	case 'zoom':
		$cid = $current[1];
		$current = $current[0] == 'seed' ? 'seed' : 'zoom';
		$widget = Widget_Content::getInstance();
		
		if ($cid > 0) {
			$content = $widget->setCondition(array('cid' => $cid))->select();
			
			if( count($content) ) {
				$widget->updateViews($cid);
				$metaInfo->title = $content['title'].' - '.$options->title;
				$metaInfo->keywords = $content['tagNameStr'] ? $content['tagNameStr'] : $options->keywords;
				$metaInfo->description = $content['stripBrief'] ? Byends_Paragraph::subStr($content['stripBrief'], 0, 100) : $options->description;
				
				if( $current == 'zoom' ) {
					require $widget->need('zoom.php');
				}
				else {
					$widget->setPerPage(9);
					
					$order = array('views', 'DESC');
					$popularContent = $widget->setCondition(array('cid' => 0, 'order' => $order))->select();
					
					if( $content['tagIdStr'] ) {
						$relatedContent = $widget->selectRelated($cid, $content['tagIdStr']);
						
						if( count($relatedContent) < 10 ) {
							$noIds = $relatedContent ? Byends_Paragraph::arrayFlatten($relatedContent, cid) : array();
							$relatedContent = array_merge($relatedContent, $widget->selectRandom(10 - count($relatedContent), $noIds));
						}
					}
					else {
						$relatedContent = $widget->selectRandom(10);
					}
					/*
					$widget->setPerPage(10);
					$condition = array(
							'cid' => 0,
							'order' => array('cid', 'DESC')
					);
					$latestContent = $widget->setCondition($condition)->select();*/
					require $widget->need('seed.php');
				}
			}
		}
		else {
			$current = '404';
			require $widget->need('404.php');
		}
		exit;
	
	/** api */
	case 'api':
		$apiDo = $current[1];
		$current = $request->filter('trim')->action;
		$deniedMsg = json_encode(array(
				'result' => 0,
				'message' => 'Access denied!'
		));
		$widget = Widget_Content::getInstance();
		
		switch ($apiDo) {
			case 'rc':
				if (!$request->isAjax() || ($current != 'index' && $current != 'popular')) {
					echo $deniedMsg;
					exit;
				}
				
				$ajaxPerPage = $request->filter('trim', 'int')->ajaxNum;
				$nextRecipe = $request->filter('trim', 'int')->nextRecipe;
				$order = $current == 'popular' ? array('views', 'DESC') : array('cid', 'DESC');
				$condition = array(
						'ajaxNum' => $ajaxPerPage ? $ajaxPerPage : $widget->options->ajaxPerPage,
						'nextRecipe' => $nextRecipe,
						'order'   => $order
				);
				usleep(100000); //沉睡10万微秒，即十分之一秒，减轻服务器压力
				$contents = $widget->setCondition($condition)->select();
				$seeds = array();
				foreach ($contents as $k => $v) {
					$seeds[] = array(
							'cid' => $v['cid'],
							'fullname' => $v['fullname'],
							'username' => $v['username'],
							'url' => $v['url'],
							'userUrl' => $v['userUrl'],
							'userLikesUrl' => $v['userLikesUrl'],
							'description' => $v['description'],
							'avatar' => $v['avatar'],
							'status' => $v['status'],
							'likesNum' => $v['likesNum'],
							'publishedNum' => $v['publishedNum'],
							'permalink' => $v['permalink'],
							'coverHash' => $v['coverHash'],
							'permalink' => $v['permalink'],
							'title' => $v['title'],
							'thumb' => $v['thumb'],
							'dateWord' => $v['dateWord'],
							'viewsWord' => $v['viewsWord'],
							'favorite' => $v['favorite'],
							'favoritesNum' => $v['favoritesNum']
					);
				}
				echo json_encode(array(
						'seeds' => $seeds,
						'next' => $nextRecipe + count($contents)
				));
				exit;
			case 'save-recipe':
				if (!$request->isAjax()) {
					echo $deniedMsg;
					exit;
				}
				
				$cid = $widget->request->filter('trim', 'int')->ri;
				$content = $widget->setCondition(array('cid' => $cid))->select($cid);
			
				if (null === $widget->uid || !$content) {
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
				$current = '404';
				require $widget->need('404.php');
		}
		exit;
	
	/** random */
	case 'random':
		$widget = Widget_Content::getInstance();
		$current = 'random';
		
		$content = $widget->selectRandom();
		if (count($content)) {
			$widget->response->redirect($content[0]['permalink']);
		}
		else {
			$widget->response->redirect(BYENDS_SITE_URL);
		}
		exit;
		
	/** auth */
	case 'auth_signin':
		$current = $current[0];
		$widget = Widget_User::getInstance();
		
		if ($widget->hasLogin()) {
			$widget->response->redirect(BYENDS_SITE_URL);
		}
		
		if ($request->isPost()) {
			$widget->login();
		}
		
		$notice = $widget->notice;
		$metaInfo = (object)array(
			'title' => 'Yummyship — Sign In',
			'keywords' => '',
			'description' => ''
		);
		require $widget->need('signin.php');
		exit;
	case 'auth_signup':
		$current = $current[0];
		$widget = Widget_User::getInstance();
		$notice = $widget->notice;
		$metaInfo = (object)array(
				'title' => 'Yummyship — Sign Up',
				'keywords' => '',
				'description' => ''
		);
		require $widget->need('signup.php');
		exit;
	case 'auth_signout':
		$current = $current[0];
		$widget = Widget_User::getInstance();
		$widget->logout();
		exit;
	case 'auth_forgot':
		$current = $current[0];
		$widget = Widget_User::getInstance();
		$metaInfo = (object)array(
				'title' => 'Yummyship — Forgot Password',
				'keywords' => '',
				'description' => ''
		);
		require $widget->need('forgot.php');
		exit;
	
	/** oauth */
	case 'auth_oauth':
		$widget = Widget_User::getInstance();
		switch ($current[1]) {
			case 'facebook':
				$widget->OAuthSign($current[1]);
				break;
			case 'fbcallback':
				$widget->OAuthSign($current[1]);
				$current = 'auth_sign_fbcallback';
				$notice = $widget->notice;
				$metaInfo = (object)array(
						'title' => 'Yummyship — Sign Up',
						'keywords' => '',
						'description' => ''
				);
				require $widget->need('signup.php');
				break;
			case 'twitter':
				$widget->OAuthSign($current[1]);
				break;
			case 'twcallback':
				$widget->OAuthSign($current[1]);
				$current = 'auth_sign_twcallback';
				$notice = $widget->notice;
				$metaInfo = (object)array(
						'title' => 'Yummyship — Sign Up',
						'keywords' => '',
						'description' => ''
				);
				require $widget->need('signup.php');
				break;
		}
		exit;
	
	/** 注意，其它路由必须放在 user 路由的前面 */
	/** user */
	default:
		$username = isset($current[1]) && $current[1] ? $current[1] : null;
		$page = isset($current[2]) && $current[2] > 0 ?  $current[2] - 1 : 0;
		$widget = Widget_Cook::getInstance();
		
		if ($username && !in_array($username, $options->systemKey)) {//判断是否是系统关键字
			switch ($current[0]) {
				case 'user':
				case 'user_page':
					$current = 'cook';
					$userInstance = Widget_User::getInstance();
					$condition = array(
							'params'      => array('username' => $username),
							'status'      => 'normal',
							'processUser' => true,
							'object'      => true,
					);
					$userInfo = $userInstance->setCondition($condition)->select();
					
					if ($userInfo) {
						$condition = array(
								'page' => $page,
								'uid' => $userInfo->uid,
								'order' => array('created', 'DESC')
						);
						$contents = $widget->setCondition(array('page' => $page, 'uid' => $userInfo->uid))->select();
							
						if($page <= $widget->getTotalPages()){
							$pagePrefix = '';
							$pages = $widget->getPages();
							$metaInfo->title = $userInfo->fullname.'\'s Recipes — '.$options->title;
							$metaInfo->keywords = '';
							$metaInfo->description = '';
							require $widget->need('cook.php');
							exit;
						}
					}
					break;
				case 'user_likes':
				case 'user_likes_page':
					$current = 'likes';
					$userInstance = Widget_User::getInstance();
					$condition = array(
							'params'      => array('username' => $username),
							'status'      => 'normal',
							'processUser' => true,
							'object'      => true,
					);
					$userInfo = $userInstance->setCondition($condition)->select();
						
					if ($userInfo) {
						$condition = array(
								'page' => $page,
								'uid' => $userInfo->uid,
								'order' => array('created', 'DESC')
						);
						$contents = $widget->setCondition(array('page' => $page, 'uid' => $userInfo->uid))->favorites();
							
						if($page <= $widget->getTotalPages()){
							$pagePrefix = 'likes/';
							$pages = $widget->getPages();
							$metaInfo->title = $userInfo->fullname.'\'s Liked Recipes — '.$options->title;
							$metaInfo->keywords = '';
							$metaInfo->description = '';
							require $widget->need('cook.php');
							exit;
						}
					}
					break;
			}
		}
		
		$current = '404';
		require $widget->need('404.php');
		exit;
}
?>