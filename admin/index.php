<?php

require_once 'common.php';

if (!defined('__BYENDS_ROOT_DIR__')) {
	exit;
}

//header( 'Content-type: text/html; charset=utf-8' );

$ver = '12.9.13.1505';

/** Setting */
if ($request->isSetRequest('setting')) {
	$widget = Widget_Options::getInstance();
	
	if ($request->isSetPost('update')) {
		if( $request->get('options') ) {
			foreach( $request->get('options') as $k => $v ) {
				$widget->update($k, $v);
			}
		}
		
		if( $request->get('imageConfig') ) {
			$widget->update('imageConfig', serialize( $request->get('imageConfig') ));
		}
		
		$status = 'save-succ';
		$options = (object)$widget->select();
		include( BYENDS_ADMIN_THEMES_DIR.'settings.html.php' );
	}
	else {
		include( BYENDS_ADMIN_THEMES_DIR.'settings.html.php' );
	}
}


/** user */
elseif ($request->isSetRequest('user')) {
	$widget = Widget_User::getInstance();
	
	/** add user */
	if ($request->isSetGet('add')) {
		/** insert user */
		if ($request->isSetPost('insert')) {
			$status = $widget->insert();
			
			if( $status === true ) {
				$response->redirect(BYENDS_ADMIN_URL.'?user');
			} 
		}
		
		include( BYENDS_ADMIN_THEMES_DIR.'add-user.html.php' );
	}
	/** delete user */
	elseif ($request->isSetRequest('delete')) {
		$widget->delete();
		$response->redirect(BYENDS_ADMIN_URL.'?user');
	}
	/** edit user */
	elseif ($request->isSetGet('edit')) {
		$uid = $request->filter('trim', 'int')->get('uid', 0);
		
		if ( $uid < 1) {
			$response->redirect(BYENDS_ADMIN_URL.'?user');
		}
		
		/** update user */
		if ($request->isSetPost('update')) {
			$status = $widget->update();
			
			if( $status === true ) {
				$response->redirect(BYENDS_ADMIN_URL.'?user');
			} else {
				$user = $request
						->filter('trim')
						->from('uid', 'name', 'mail', 'url', 
						 'avatar', 'group', 'status', 'description');
			}
		}
		else {
			$condition = array(
					'params' => array('uid' => $uid),
					'status'      => null,
					'processUser' => true,
					'object'      => false,
			);
			$user = $widget->setCondtion($condition)->select(); 
				
			if ( empty($user)) {
				$response->redirect(BYENDS_ADMIN_URL.'?user');
			}
		}
		
		include( BYENDS_ADMIN_THEMES_DIR.'edit-user.html.php' );
	}
	/** list users */
	else {
		$widget->setPerPage($options->adminPerPage);
		$page = $request->get('page', 0);
		$page = $page ? $page-1 : 0;
		$condition = array(
				'params'      => array(),
				'page'        => $page,
				'status'      => null,
		);
		$users = $widget->setCondtion($condition)->select();
		$pages = $widget->getPages();
		include( BYENDS_ADMIN_THEMES_DIR.'users.html.php' );
	}
}


/** tag */
elseif ($request->isSetRequest('tag')) {
	$widget = Widget_Tag::getInstance();
	
	/** add tag */
	if ($request->isSetGet('add')) {
		/** insert tag */
		if ($request->isSetPost('insert')) {
			$status = $widget->insert();
		
			if( $status === true ) {
				$response->redirect(BYENDS_ADMIN_URL.'?tag');
			}
		}
		
		include( BYENDS_ADMIN_THEMES_DIR.'add-tag.html.php' );
	}
	/** delete tag */
	elseif ($request->isSetRequest('delete')) {
		$widget->delete();
		$response->redirect(BYENDS_ADMIN_URL.'?tag');
	}
	/** edit tag */
	elseif ($request->isSetGet('edit')) {
		$mid = $request->filter('trim', 'int')->get('mid', 0);
		
		if ( !$mid) {
			$response->redirect(BYENDS_ADMIN_URL.'?tag');
		}
		
		/** update tag */
		if ($request->isSetPost('update')) {
			$status = $widget->update();
			
			if( $status === true ) {
				$response->redirect(BYENDS_ADMIN_URL.'?tag');
			} else {
				$tag = array(
						'mid' => $mid,
						'name' => $name,
						'slug' => $slug
				);
			}
		}
		else {
			$tag = $widget->setCondtion(array('mid' => $mid))->select();
			
			if ( empty($tag)) {
				$response->redirect(BYENDS_ADMIN_URL.'?tag');
			}
		}
		
		include( BYENDS_ADMIN_THEMES_DIR.'edit-tag.html.php' );
	}
	/** list tags */
	else {
		$widget->setPerPage($options->adminPerPage);
		$page = $request->get('page', 0);
		$page = $page ? $page-1 : 0;
		$tags = $widget->setCondtion(array('page' => $page))->select();
		$pages = $widget->getPages();
		include( BYENDS_ADMIN_THEMES_DIR.'tags.html.php' );
	}
}


/** post */
elseif ($request->isSetRequest('post')) {
	/** add post */
	if ($request->isSetGet('add')) {
		$widget = Widget_Content::getInstance();
		
		/** insert post */
		if ($request->isSetPost('insert')) {
			$status = $widget->insert();
		
			if( $status === true ) {
				$response->redirect(BYENDS_ADMIN_URL.'?post');
			}
		}
		/** clear data */
		elseif ($request->isSetGet('clear')) {
			Byends_Cookie::delete('__byends_gather_title', BYENDS_BASE_URL);
			Byends_Cookie::delete('__byends_gather_image', BYENDS_BASE_URL);
			Byends_Cookie::delete('__byends_gather_referer', BYENDS_BASE_URL);
			
			$response->redirect(BYENDS_ADMIN_URL.'?post&add');
		}
		
		include( BYENDS_ADMIN_THEMES_DIR.'add-post.html.php' );
	}
	/** delete post */
	elseif ($request->isSetRequest('delete')) {
		$widget = Widget_Content::getInstance();
		$widget->delete();
		$response->redirect(BYENDS_ADMIN_URL);
	}
	/** edit post */
	elseif ($request->isSetGet('edit')) {
		$cid = $request->filter('trim', 'int')->get('cid', 0);
		
		if ($cid < 1) {
			$response->redirect(BYENDS_ADMIN_URL);
		}
		
		$widget = Widget_Content::getInstance();
		
		/** update post */
		if ($request->isSetPost('update')) {
			$status = $widget->update();
		
			if( $status === true ) {
				$response->redirect(BYENDS_ADMIN_URL);
			} 
		}
		
		$post = $widget->setCondtion(array('cid' => $cid, 'status' => null))->select();
		
		if (empty($post)) {
			$response->redirect(BYENDS_ADMIN_URL);
		}
		
		include( BYENDS_ADMIN_THEMES_DIR.'edit-post.html.php' );
	}
	/** list posts */
	else {
		$page = $request->get('page', 0);
		$page = $page ? $page - 1 : 0;
		$widget = Widget_Content::getInstance();
		$widget->setPerPage($options->adminPerPage);
		$posts = $widget->setCondtion(array('page' => $page, 'status' => null))->select();
		$pages = $widget->getPages();
	
		include( BYENDS_ADMIN_THEMES_DIR.'posts.html.php' );
	}
}


/** index */
else {
	$page = $request->get('page', 0);
	$page = $page ? $page - 1 : 0;
	$widget = Widget_Content::getInstance();
	$widget->setPerPage($options->adminPerPage);
	$posts = $widget->setCondtion(array('page' => $page, 'status' => null))->select();
	$pages = $widget->getPages();
	
	include( BYENDS_ADMIN_THEMES_DIR.'posts.html.php' );
}


?>