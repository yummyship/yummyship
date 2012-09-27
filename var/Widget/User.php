<?php
/**
 * User 处理类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Widget_User
 * @copyright  Copyright (c) 2012 Byends (http://www.byends.com)
 */
class Widget_User extends Widget_Abstract 
{
	private $_authType = array(
		'normal'   => 'normal',
		'facebook' => 'facebook',
		'twitter'  => 'twitter'
	);
	
	/**
	 * 是否已经登录
	 *
	 * @access private
	 * @var boolean
	 */
	private $_hasLogin = null;
	
	/**
	 * 单例句柄
	 *
	 * @access private
	 * @var Widget_User
	 */
	private static $_instance = null;	
	/** 
	 * 用户组
	 *
	 * @access public
	 * @var array
	 */
	public $groups = array(
		'administrator' => 0,
		'editor'		=> 1,
		'contributor'	=> 2,
		'visitor'		=> 3
	);
	
	public $status = array(
		'normal'  => 'normal',
		'delete'  => 'delete'
	);
	
	public $user = null;
	public $notice = null;
	
	public function __construct() 
	{
		parent::__construct();
		
		$this->perPage = $this->options->perPage;
		$this->notice = new Byends_Notice();
		$this->select = 'uid, fullname, username, password, mail, url, created, logged, `group`, authCode, 
					description, avatar, notify, status, likesNum, publishedNum';
		$this->sCondition = array(
				'params'      => array(),
				'page'        => 0,
				'status'      => $this->status['normal'],
				'processUser' => true,
				'object'      => true,
				'order'       => array('uid', 'DESC')
		);
	}
	
	/**
	 * 获取单例句柄
	 *
	 * @access public
	 * @return Widget_User
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new Widget_User();
		}
	
		return self::$_instance;
	}
	
	/**
	 * 获取用户
	 * @return array
	 */
	public function select()
	{
		if ($this->sCondition['params']) {
			$params = implode(' AND ', $this->db->quoteArray($this->sCondition['params']));
			$params .= $this->sCondition['status'] ? ' AND status = :1 ' : '';
			$user = $this->db->getRow(
				'SELECT
					'.$this->select.'
				 FROM
				 	'.BYENDS_TABLE_USERS.' WHERE '.$params, $this->sCondition['status']
			);
			
			if ( empty($user) ) {
				return array();
			}
			
			if ($this->sCondition['processUser']) {
				$this->processUser($user, $this->sCondition['object']);
			}
			
			return $user;
		}
		else {
			$condition = $this->sCondition['status'] ? ' AND status = :1 ' : '';
			$this->currentPage = $this->sCondition['page'];
			$users = $this->db->query(
					'SELECT SQL_CALC_FOUND_ROWS
						'.$this->select.'
					FROM
						'.BYENDS_TABLE_USERS.'
					WHERE
						1 = 1 '.$condition.'
					ORDER BY
						'.$this->sCondition['order'][0].' '.$this->sCondition['order'][1].'
					LIMIT
						:2, :3',
						$this->sCondition['status'],
						$this->currentPage * $this->perPage,
						$this->perPage
			);
			$this->totals = $this->db->foundRows();
			$this->totalPages = ceil($this->totals / $this->perPage );
			foreach( array_keys($users) as $i ) {
				$this->processUser($users[$i]);
			}
			return $users;
		}
	}
	
	/**
	 * 获取  oAuth 用户
	 * @param array $param
	 * @return array
	 */
	public function selectOAuth($param)
	{
		$params = implode(' AND ', $this->db->quoteArray($param));
		$oAuthUser = $this->db->getRow(
			'SELECT
				oid, uid, oAuthUid, oAuthCode, accessToken, oAuthType
			 FROM
			 	'.BYENDS_TABLE_OAUTH_USERS.' WHERE '.$params
		);
			
		if ( empty($oAuthUser) ) {
			return array();
		}
		
		return $oAuthUser;
	}
	
	/**
	 * 以用户名和密码登录
	 *
	 * @access public
	 * @param string $name 用户名
	 * @param string $password 密码
	 * @param integer $expire 过期时间
	 * @return boolean
	 */
	public function login()
	{
		if ($this->hasLogin()) {
			 $this->response->redirect(BYENDS_BASE_URL);
		}
		
		$validator = new Byends_Validate();
		$validator->addRule('mail', 'required', 'Please enter Email.');
		$validator->addRule('mail', 'email', 'Email Invalid.');
		$error = $validator->run($this->request->filter('trim')->from('mail'));
		
		if ($error) {
			Byends_Cookie::set('__byends_remember_mail', $this->request->filter('trim')->mail, 0, BYENDS_BASE_URL);
		
			$this->notice->set($error, null, 'error');
			$this->response->goBack();
		}
		else {
			$validator->deleteRule('mail');
			$validator->addRule('password', 'required', 'Please enter Password.');
			$error = $validator->run($this->request->filter('trim')->from('password'));
			
			if ($error) {
				Byends_Cookie::set('__byends_remember_mail', $this->request->filter('trim')->mail, 0, BYENDS_BASE_URL);
			
				$this->notice->set($error, null, 'error');
				$this->response->goBack();
			}
		}
		
		Byends_Cookie::delete('__byends_remember_mail', BYENDS_BASE_URL);
		$condition = array(
				'params'      => array('mail' => $this->request->filter('trim')->mail),
				'status'      => null,
				'processUser' => false
		);
		$user = $this->setCondition($condition)->select();
		$valid = false;
		$errorInfo = 'Email or Password Invalid!';
		
		if ($user) {
			if ($user['status'] == $this->status['normal']) {
				$hashValidate = Byends_Paragraph::hashValidate($this->request->filter('trim')->password, $user['password']);
				if ($hashValidate) {
					$authCode = sha1(Byends_Paragraph::randString(20));
					$user['authCode'] = $authCode;
					//$expire = 1 == $this->request->remember ? $this->timeStamp + 30*24*3600 : 0;
					$expire = $this->timeStamp + 30*24*3600;
					
					Byends_Cookie::set('__byends_authUid', $user['uid'], $expire, BYENDS_BASE_URL);
					Byends_Cookie::set('__byends_authType', $this->_authType['normal'], $expire, BYENDS_BASE_URL);
					Byends_Cookie::set('__byends_authCode', Byends_Paragraph::hash($authCode),	$expire, BYENDS_BASE_URL);
			
					$this->db->updateRow(
						BYENDS_TABLE_USERS,
						array( 'uid' => $user['uid'] ),
						array( 'logged' => $this->gmtTimeStamp, 'authCode' => $authCode )
					);
				
					$valid = true;
				}
			}
			else {
				$errorInfo = 'The User has been suspended!';
			}
		}
	
		if (!$valid) {
			Byends_Cookie::set('__byends_remember_mail', $this->request->filter('trim')->mail, 0, BYENDS_BASE_URL);
			$notice = new Byends_Notice();
			$notice->set($errorInfo, null, 'error');
			$this->response->goBack();
			$this->response->goBack('?referer=' . urlencode($this->request->referer));
		}
		
		if (null != $this->request->referer) {
			$this->response->redirect($this->request->referer);
// 		} else if ($this->pass('administrator', TURE)) {
// 			$this->response->redirect(BYENDS_ADMIN_URL);
		} else {
			$this->response->redirect(BYENDS_BASE_URL);
		}
	}
	
	/**
	 * OAuth 自动登录
	 * @param string $mail
	 * @param array $oAuthInfo
	 */
	public function autoLogin($oAuthInfo, $isAutoAdd = false, $isCheck = true)
	{
		$user = null;
		
		if ($isCheck) {
			$oAuthUser = $this->selectOAuth(array( 'oAuthUid' => $oAuthInfo['oAuthUid'], 'oAuthType' => $oAuthInfo['oAuthType'] ));
			
			if (isset($oAuthInfo['mail']) && $oAuthInfo['mail']) {
				$condition = array(
					'params'      => array('mail' => $oAuthInfo['mail']),
					'status'      => null,
					'processUser' => false
				);
				$user = $this->setCondition($condition)->select();
			}
			elseif ($oAuthUser) {
				$condition = array(
					'params'      => array('uid' => $oAuthUser['uid']),
					'status'      => null,
					'processUser' => false
				);
				$user = $this->setCondition($condition)->select();
			}
		}
		
		if ($user) {
			if ($user['status'] <> $this->status['normal'] ) {
				$error = 'The User has been suspended!';
				$this->notice->set($error, null, 'error');
				$this->response->redirect(BYENDS_AUTH_SIGNIN_URL);
			}
			
			$expire = $this->timeStamp + 30*24*3600;
			$oAuthCode = $oAuthInfo['oAuthCode'];
			Byends_Cookie::set('__byends_authUid', $oAuthInfo['oAuthUid'], $expire, BYENDS_BASE_URL);
			Byends_Cookie::set('__byends_authType', $oAuthInfo['oAuthType'], $expire, BYENDS_BASE_URL);
			Byends_Cookie::set('__byends_authCode', $oAuthCode,	$expire, BYENDS_BASE_URL);
				
			$this->db->updateRow(
				BYENDS_TABLE_USERS,
				array( 'uid' => $user['uid'] ),
				array( 'logged' => $this->gmtTimeStamp )
			);
			
			if ($oAuthUser) {
				$this->db->updateRow(
					BYENDS_TABLE_OAUTH_USERS,
					array( 'uid' => $user['uid'], 'oAuthType' => $oAuthInfo['oAuthType'] ),
					array( 'oAuthCode' => $oAuthCode, 'accessToken' => $oAuthInfo['accessToken'] )
				);
			}
			else {
				$this->db->insertRow(
					BYENDS_TABLE_OAUTH_USERS,
					array(
						'uid' 			=> $user['uid'],
						'oAuthUid' 		=> $oAuthInfo['oAuthUid'],
						'oAuthCode'		=> $oAuthInfo['oAuthCode'],
						'accessToken' 	=> $oAuthInfo['accessToken'],
						'oAuthType' 	=> $oAuthInfo['oAuthType']
					)
				);
			}
			
			if (null != $this->request->referer) {
				$this->response->redirect($this->request->referer);
				// 		} else if ($this->pass('administrator', TURE)) {
				// 			$this->response->redirect(BYENDS_ADMIN_URL);
			} else {
				$this->response->redirect(BYENDS_BASE_URL);
			}
		}
		
		if ($isAutoAdd) {
			//插入 用户
			$this->db->insertRow(
					BYENDS_TABLE_USERS,
					array(
							'fullname' => $oAuthInfo['fullname'],
							'username' => $oAuthInfo['username'],
							'password' => '',
							'mail' => $oAuthInfo['mail'],
							'url' => '',
							'created' => $this->gmtTimeStamp,
							'logged' => $this->gmtTimeStamp,
							'group' => 'contributor',
							'description' => '',
							'status' => $this->status['normal']
					)
			);
			$uid = $this->db->insertId();
			
			//插入  OAuth 用户
			$this->db->insertRow(
					BYENDS_TABLE_OAUTH_USERS,
					array(
							'uid' 			=> $uid,
							'oAuthUid' 		=> $oAuthInfo['oAuthUid'],
							'oAuthCode'		=> $oAuthInfo['oAuthCode'],
							'accessToken' 	=> $oAuthInfo['accessToken'],
							'oAuthType' 	=> $oAuthInfo['oAuthType']
					)
			);
			
			//登录
			$expire = $this->timeStamp + 30*24*3600;
			Byends_Cookie::set('__byends_authUid', $oAuthInfo['oAuthUid'], $expire, BYENDS_BASE_URL);
			Byends_Cookie::set('__byends_authType', $oAuthInfo['oAuthType'], $expire, BYENDS_BASE_URL);
			Byends_Cookie::set('__byends_authCode', $oAuthInfo['oAuthCode'], $expire, BYENDS_BASE_URL);
			
			if (null != $this->request->referer) {
				$this->response->redirect($this->request->referer);
				// 		} else if ($this->pass('administrator', TURE)) {
				// 			$this->response->redirect(BYENDS_ADMIN_URL);
			} else {
				$this->response->redirect(BYENDS_BASE_URL);
			}
		}
	}
	
	/**
	 * 用户登出
	 *
	 * @access public
	 * @return void
	 */
	public function logout($redirect = true)
	{
		Byends_Cookie::delete('__byends_authUid', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_authType', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_authCode', BYENDS_BASE_URL);
		
		Byends_Cookie::delete('__byends_remember_mail', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_remember_fullname', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_remember_username', BYENDS_BASE_URL);
		
		Byends_Cookie::delete('__byends_remember_signup_mail', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_remember_signup_fullname', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_remember_signup_username', BYENDS_BASE_URL);
		
		Byends_Cookie::delete('__byends_gather_title', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_gather_image', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_gather_referer', BYENDS_BASE_URL);
		
		session_unset();
		
		if ($redirect) {
			$this->response->redirect(BYENDS_SITE_URL);
		}
		
		return true;
	}
	
	/**
	 * 判断用户是否已经登录
	 *
	 * @access public
	 * @return void
	 */
	public function hasLogin()
	{
		if (null !== $this->_hasLogin) {
			return $this->_hasLogin;
		} else {
			$cookieAuthUid = Byends_Cookie::get('__byends_authUid');
			$cookieAuthType = Byends_Cookie::get('__byends_authType');
			$cookieAuthCode = Byends_Cookie::get('__byends_authCode');
			
			if (null !== $cookieAuthUid && null !== $cookieAuthType && null !== $cookieAuthCode) {
				switch ($cookieAuthType) {
					case $this->_authType['normal']:
						$condition = array(
								'params'      => array('uid' => intval($cookieAuthUid)),
								'status'      => $this->status['normal'],
								'processUser' => false
						);
						$user = $this->setCondition($condition)->select();
						
						/** 验证登陆 */
						if ($user) {
							if (Byends_Paragraph::hashValidate($user['authCode'], $cookieAuthCode)) {
								$this->processUser($user, true, true);
								return $this->_hasLogin;
							}
						}
						break;
					case $this->_authType['facebook']:
					case $this->_authType['twitter']:
						$param = array(
							'oAuthUid'  => $cookieAuthUid,
							'oAuthType' => $cookieAuthType
						);
						$oAuthUser =  $this->selectOAuth($param);
						
						if ($oAuthUser) {
							if ($cookieAuthCode == $oAuthUser['oAuthCode']) {
								$condition = array(
										'params'      => array('uid' => $oAuthUser['uid']),
										'status'      => $this->status['normal'],
										'processUser' => false
								);
								$user = $this->setCondition($condition)->select();
								
								if ($user) {
									$this->processUser($user, true, true);
									return $this->_hasLogin;
								}
							}
						}
						break;
				}
				
				$this->logout();
			}
	
			return ($this->_hasLogin = false);
		}
	}
	
	/**
	 * 修改用户信息
	 * @return string|boolean
	 */
	public function update() 
	{
		$user = $this->request
				->filter('trim')
				->from('uid', 'fullname', 'username', 'mail', 'url', 
				'password', 'password2', 'group', 'status', 'description');
		
		if( empty($user['fullname']) ) {
			return 'fullname-empty';
		}
		
		if( empty($user['username']) ) {
			return 'username-empty';
		}
		
		if (in_array($user['username'], $this->options->systemKey)) {
			return 'username-exists';
		}
		
		if( empty($user['mail']) ) {
			return 'mail-empty';
		}
		
		$validate = new Byends_Validate();
		if( !$validate->email($user['mail']) ) {
			return 'mail-incorrect';
		}
		
		$userData = array(
				'fullname' => $user['fullname'],
				'username' => $user['username'],
				'mail' => $user['mail'],
				'url' => (string)$user['url'],
				'group' => $user['group'],
				'description' => (string)$user['description'],
				'status' => $user['status']
		);
		
		if (($user['password'] || $user['password2']) ) {
			if ($user['password'] <> $user['password2']) {
				return 'passwords-not-equal';
			}
			else {
				$userData['password'] = Byends_Paragraph::hash($user['password']);
			}
		}
		
		$userArr = $this->db->getRow( 
				'SELECT uid, username, mail 
				FROM '.BYENDS_TABLE_USERS." 
				WHERE ( username = :1 or mail = :2 ) and uid <> :3", 
				$user['username'], $user['mail'], $user['uid']
		 );
		
		if ($userArr){
			if( $userArr['username'] == $user['username'] ) {
				return 'username-exists';
			}
			elseif ( $userArr['mail'] == $user['mail'] ) {
				return 'mail-exists';
			}
		}
		
		$this->db->updateRow(
			BYENDS_TABLE_USERS,
			array( 'uid' => $user['uid'] ),
			$userData
		);
		return true;
	}
	
	/**
	 * 添加用户
	 * @return string|boolean
	 */
	public function insert()
	{
		$user = $this->request
				->filter('trim')
				->from( 'fullname', 'username', 'mail', 'url',
				'password', 'password2', 'group', 'status', 'description');
		
		if (empty($user['fullname'])) {
			return 'fullname-empty';
		}
		
		if (empty($user['username'])) {
			return 'username-empty';
		}
		
		if (in_array($user['username'], $this->options->systemKey)) {
			return 'username-exists';
		}
		
		if ( empty($user['mail'])) {
			return 'mail-empty';
		}
		
		$validate = new Byends_Validate();
		if ( !$validate->email($user['mail'])) {
			return 'mail-incorrect';
		}
		
		if ( empty($user['password'])) {
			return 'passwords-empty';
		}
		
		if ( $user['password'] <> $user['password2']) {
			return 'passwords-not-equal';
		}
		
		$userArr = $this->db->getRow( 
				'SELECT uid, username, mail 
				FROM '.BYENDS_TABLE_USERS." 
				WHERE username = :1 or mail = :2", 
				$user['username'], $user['mail']
		 );
		
		if ($userArr){
			if( $userArr['username'] == $user['username'] ) {
				return 'username-exists';
			}
			elseif ( $userArr['mail'] == $user['mail'] ) {
				return 'mail-exists';
			}
		}
		
		$this->db->insertRow(
			BYENDS_TABLE_USERS,
			array(
				'fullname' => $user['fullname'],
				'username' => $user['username'],
				'password' => Byends_Paragraph::hash($user['password']),
				'mail' => $user['mail'],
				'url' => (string)$user['url'],
				'created' => $this->gmtTimeStamp,
				'logged' => $this->gmtTimeStamp,
				'group' => $user['group'],
				'description' => (string)$user['description'],
				'status' => $user['status']
			)
		);
		
		return true;
	}
	
	/**
	 * 删除用户，只是更改状态，并非真正意义上的删除
	 */
	public function delete()
	{
		$uid = $this->request->filter('trim', 'int')->get('uid', 0);
		if (!$uid) {
			return false;
		}
		$this->db->updateRow(
				BYENDS_TABLE_USERS,
				array( 'uid' => $uid ),
				array( 'status' => 'delete' )
		);
	
		return true;
	}
	
	/**
	 * 删除用户
	 */
	public function realDelete( $uid )
	{
		$posts = $this->db->query( 'SELECT cid FROM '.BYENDS_TABLE_POSTS.' WHERE uid = :1', $uid );
		foreach( $posts as $p ) {
			$this->deletePost( $p['cid'] );
		}
	
		$this->db->query( 'DELETE FROM '.BYENDS_TABLE_USERS.' WHERE uid = :1', $uid );
		return true;
	}
	
	/**
	 * 判断用户权限
	 *
	 * @access public
	 * @param string $group 用户组
	 * @param boolean $return 是否为返回模式
	 * @return boolean
	 */
	public function pass($group, $return = false)
	{
		if ($this->hasLogin()) {
			if (array_key_exists($group, $this->groups) && $this->groups[$this->user->group] <= $this->groups[$group]) {
				return true;
			}
		} else {
            if ($return) {
                return false;
            } else {
                //防止循环重定向
                $this->response->redirect(BYENDS_AUTH_SIGNIN_URL .
                (0 === strpos($this->request->getReferer(), BYENDS_AUTH_SIGNIN_URL) ? '' :
                '?referer=' . urlencode($this->request->makeUriByRequest())), false);
            }
        }
		
		if ($return) {
            return false;
        } else {
            $this->response->redirect(BYENDS_SITE_URL.'404');
        }
	}
	
	/**
	 * 加工用户数据
	 * 
	 * @param array $user
	 */
	public function processUser(&$user, $object = false, $hasLogin = false) 
	{
		$user['avatar'] = is_file(__BYENDS_ROOT_DIR__.__BYENDS_AVATARS_DIR__.$user['avatar']) ? 
						  BYENDS_AVATARS_STATIC_URL.$user['avatar'] : BYENDS_NO_AVATAR_STATIC_URL;
		$user['created'] = Byends_Date::timeStamp( $user['created'] );
		$user['dateWord'] = Byends_Date::dateWord($user['created'], $this->timeStamp, $this->options->lang);
		$user['userUrl'] = BYENDS_SITE_URL.$user['username'];
		$user['userLikesUrl'] = BYENDS_SITE_URL.$user['username'].'/likes';
		
		if ($hasLogin) {
			$this->user = (object)$user;
			$this->uid = $this->user->uid;
			$this->_hasLogin = true;
		}
		
		$user = $object ? (object)$user : $user;
	}
	
	/**
	 * OAuth Sign
	 * @param string $provider
	 * @return multitype
	 */
	public function OAuthSign($provider)
	{
		switch ($provider) {
			case 'facebook':
			case 'fbcallback':
				require 'OAuth/Facebook/facebook.php';
				$fbconfig = array(
					'appid'       => '476584082376194',
					'secret' 	  => 'deecb2e48b891aecb259d4de40ee9e2b',
					'callbackUrl' => BYENDS_AUTH_OAUTH_URL.'fbcallback'
				);
				$facebook = new Facebook(array(
					'appId'  => $fbconfig['appid' ],
					'secret' => $fbconfig['secret']
				));
				$user = $facebook->getUser();
				$codeKey = 'fb_'.$facebook->getAppId().'_code';
				$accessTokenKey = 'fb_'.$facebook->getAppId().'_access_token';
				
				switch ($provider) {
					case 'facebook':
						if ($user) {
							$this->response->redirect($fbconfig['callbackUrl'].'?code='.$_SESSION[$codeKey]);
						}
						else {
							$loginUrl = $facebook->getLoginUrl(array(
								'scope'         => 'user_about_me,email,publish_actions',
								'redirect_uri'  => $fbconfig['callbackUrl'],
								//'display'		=> 'popup'
							));
							$this->response->redirect($loginUrl);
						}
						break;
					case 'fbcallback':
						$code = $this->request->filter('trim')->code;
						if (!$user || !isset($_SESSION[$codeKey]) || !isset($_SESSION[$accessTokenKey])
							|| $_SESSION[$codeKey] <> $code || $_SESSION[$accessTokenKey] <> $facebook->getAccessToken()) {
							session_unset();
							$error = 'Something went wrong with Facebook.';
							$this->notice->set($error, null, 'error');
							$this->response->redirect(BYENDS_AUTH_SIGNIN_URL);
						}
						
						try {
							$userProfile = $facebook->api('/me?fields=id,email,name,username,gender,verified,
											picture.height('.$this->options->imageConfig->avatarSize[1].')
											.width('.$this->options->imageConfig->avatarSize[0].')');
							
							
							$oAuthInfo = array(
								'oAuthUid'    => $userProfile['id'],
								'mail'        => $userProfile['email'],
								'fullname'    => $userProfile['name'],
								'username'    => $userProfile['username'],
								'oAuthCode'   => @$_SESSION[$codeKey],
								'accessToken' => @$_SESSION[$accessTokenKey],
								'oAuthType'   => $this->_authType['facebook']
							);
							
							//已经存在该用户则自动登录
							$this->autoLogin($oAuthInfo);
							
							if ($this->request->isSetPost('signup')) {
								$checkResult = $this->checkOAuthSign();
								$oAuthInfo['fullname'] = $checkResult['fullname'];
								$oAuthInfo['username'] = $checkResult['username'];
								
								$this->autoLogin($oAuthInfo, true, false);
							}
							
							$_SESSION['__byends_oAuth_mail']     = $oAuthInfo['mail'];
							$_SESSION['__byends_oAuth_fullname'] = $oAuthInfo['fullname'];
							$_SESSION['__byends_oAuth_username'] = $oAuthInfo['username'];
							$_SESSION['__byends_oAuth_code']     = $oAuthInfo['oAuthCode'];
						} catch (FacebookApiException $e) {
							//deBug($e);
							session_unset();
							$error = 'Something went wrong with Facebook.';
							$this->notice->set($error, null, 'error');
							$this->response->redirect(BYENDS_AUTH_SIGNIN_URL);
						}
						break;
				}
				break;
				
			case 'twitter':
			case 'twcallback':
				require 'OAuth/Twitter/twitteroauth.php';
				$twconfig = array(
					'CONSUMER_KEY'     => 'unwBloGtG8leo1X74Dhxw',
					'CONSUMER_SECRET'  => 'Ess1N7issrchrewLyHDuSyEAyEBqaztiLFDyDHTjk',
					'OAUTH_CALLBACK'   => BYENDS_AUTH_OAUTH_URL.'twcallback'
				);
				
				switch ($provider) {
					case 'twitter':
						try {
							$twitter = new TwitterOAuth($twconfig['CONSUMER_KEY'], $twconfig['CONSUMER_SECRET']);
							$requestToken = $twitter->getRequestToken($twconfig['OAUTH_CALLBACK']);
							
							if (200 == $twitter->http_code) {
								$_SESSION['__byends_oAuth_request_token'] = $requestToken;
								$loginUrl = $twitter->getAuthorizeURL($requestToken);
								$this->response->redirect($loginUrl);
							}
						} catch (OAuthException $e) {
							//deBug($e);
						}
						
						session_unset();
						$error = 'Something went wrong with Twitter.1';
						$this->notice->set($error, null, 'error');
						$this->response->redirect(BYENDS_AUTH_SIGNIN_URL);
						break;
					case 'twcallback':
						$requestToken = $this->request->filter('trim')->from('oauth_token', 'oauth_verifier');
						
						if ($this->request->isSetPost('signup')) {
							$code = $this->request->filter('trim')->code;
							if (isset($_SESSION['__byends_oAuth_code']) && $code <> $_SESSION['__byends_oAuth_code']) {
								session_unset();
								$error = 'Something went wrong with Twitter';
								$this->notice->set($error, null, 'error');
								$this->response->redirect(BYENDS_AUTH_SIGNIN_URL);
							}
							
							$checkResult = $this->checkOAuthSign(true);
							$oAuthInfo = array(
									'mail'		  => $checkResult['mail'],
									'oAuthUid'    => $_SESSION['__byends_oAuth_uid'],
									'fullname'    => $checkResult['fullname'],
									'username'    => $checkResult['username'],
									'oAuthCode'   => $_SESSION['__byends_oAuth_code'],
									'accessToken' => $_SESSION['__byends_oAuth_access_token'],
									'oAuthType'   => $this->_authType['twitter']
							);
							$this->autoLogin($oAuthInfo, true);
						}
						
						if (isset($requestToken['oauth_token']) && isset($requestToken['oauth_verifier']) 
								&& isset($_SESSION['__byends_oAuth_request_token']['oauth_token'])
								&& $requestToken['oauth_token'] == $_SESSION['__byends_oAuth_request_token']['oauth_token']) {
							try {
								$twitter = new TwitterOAuth($twconfig['CONSUMER_KEY'], $twconfig['CONSUMER_SECRET'], 
											$_SESSION['__byends_oAuth_request_token']['oauth_token'], 
											$_SESSION['__byends_oAuth_request_token']['oauth_token_secret']);
								
								unset($_SESSION['__byends_oAuth_request_token']);
								$oAuthToken = $twitter->getAccessToken($requestToken['oauth_verifier']);
								
								if (200 == $twitter->http_code) {
									$twitter = new TwitterOAuth($twconfig['CONSUMER_KEY'], $twconfig['CONSUMER_SECRET'], 
											$oAuthToken['oauth_token'], $oAuthToken['oauth_token_secret']);
									$userProfile = $twitter->get('account/verify_credentials');
									$oAuthInfo = array(
										'oAuthUid'    => $userProfile->id,
										'fullname'    => $userProfile->name,
										'username'    => $userProfile->screen_name,
										'oAuthCode'   => $oAuthToken['oauth_token'],
										'accessToken' => $oAuthToken['oauth_token_secret'],
										'oAuthType'   => $this->_authType['twitter']
									);
									$_SESSION['__byends_oAuth_uid']          = $oAuthInfo['oAuthUid'];
									$_SESSION['__byends_oAuth_fullname']     = $oAuthInfo['fullname'];
									$_SESSION['__byends_oAuth_username']     = $oAuthInfo['username'];
									$_SESSION['__byends_oAuth_code']         = $oAuthInfo['oAuthCode'];
									$_SESSION['__byends_oAuth_access_token'] = $oAuthInfo['accessToken'];
									
									$this->autoLogin($oAuthInfo, false, true);
									$this->response->redirect($twconfig['OAUTH_CALLBACK']);
								}
								else {
									session_unset();
									$error = 'Something went wrong with Twitter';
									$this->notice->set($error, null, 'error');
									$this->response->redirect(BYENDS_AUTH_SIGNIN_URL);
								}
							} catch (OAuthException $e) {
								//deBug($e);
								session_unset();
								$error = 'Something went wrong with Twitter';
								$this->notice->set($error, null, 'error');
								$this->response->redirect(BYENDS_AUTH_SIGNIN_URL);
							}
						}
						else {
							if (!isset($_SESSION['__byends_oAuth_code']) || !$_SESSION['__byends_oAuth_code']) {
								session_unset();
								$error = 'Something went wrong with Twitter';
								$this->notice->set($error, null, 'error');
								$this->response->redirect(BYENDS_AUTH_SIGNIN_URL);
							}
						}
						break;
				}
				break;
		}
	}
	
	/**
	 * 检测 OAuth Sign
	 * @param boolean $isCheckMail
	 * @return array
	 */
	public function checkOAuthSign($isCheckMail = false)
	{
		$mail	  = $this->request->filter('trim')->mail;
		$fullname = $this->request->filter('trim')->fullname;
		$username = $this->request->filter('trim')->username;
		preg_match_all('|[A-Za-z0-9_]*|', $username, $username);
		$username = @implode('', $username[0]);
		$valid = true;
		
		if ($isCheckMail) {
			if ($valid && strlen($mail) < 1) {
				$error = 'Email is required.';
				$valid = false;
			}
			
			if ($valid && !preg_match("/^[^@\s<&>]+@([-a-z0-9]+\.)+[a-z]{2,}$/i", $mail)) {
				$error = 'Email Invalid.';
				$valid = false;
			}
			
			if ($valid) {
				$condition = array(
						'params'      => array('mail' => $mail),
						'status'      => null,
						'processUser' => false
				);
				if ($this->setCondition($condition)->select()) {
					$error = 'The Email is already taken.';
					$valid = false;
				}
			}
		}
		
		if ($valid && strlen($fullname) < 1) {
			$error = 'Full Name is required.';
			$valid = false;
		}
		
		if ($valid && strlen($fullname) < 2) {
			$error = 'Full Name Must contain 2 Characters.';
			$valid = false;
		}
		
		if ($valid && strlen($username) < 1) {
			$error = 'Username is required.';
			$valid = false;
		}
		
		if ($valid && strlen($username) < 3) {
			$error = 'Username Must contain 3 Characters.';
			$valid = false;
		}
		
		if ($valid && in_array($username, $this->options->systemKey)) {
			$error = 'The Username is already taken.';
			$valid = false;
		}
		
		if ($valid) {
			$userArr = $this->db->getRow(
				'SELECT uid, username, mail
				FROM '.BYENDS_TABLE_USERS."
				WHERE username = :1",
				$username
			);
				
			if ($userArr) {
				$error = 'The Username  is already taken.';
				$valid = false;
			}
		}
		
		if (!$valid) {
			Byends_Cookie::set('__byends_remember_signup_mail', $mail, 0, BYENDS_BASE_URL);
			Byends_Cookie::set('__byends_remember_signup_fullname', $fullname, 0, BYENDS_BASE_URL);
			Byends_Cookie::set('__byends_remember_signup_username', $username, 0, BYENDS_BASE_URL);
			$this->notice->set($error, null, 'error');
			$this->response->goBack();
		}
		
		$result = array(
			'mail'     => $mail,
			'fullname' => $fullname,
			'username' => $username
		);
		
		return $result;
	}
}

?>