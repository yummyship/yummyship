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
		'visitor'		=> 1
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
		$this->select = 'uid, name, password, mail, url, created, logged, `group`, authCode, 
					description, avatar, notify, status';
		$this->sCondition = array(
				'params'      => array(),
				'page'        => 0,
				'status'      => 'normal',
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
		$validator->addRule('mail', 'required', 'Please enter email.');
		$validator->addRule('mail', 'email', 'Email Invalid.');
		$error = $validator->run($this->request->filter('trim')->from('mail'));
		
		if ($error) {
			Byends_Cookie::set('__byends_remember_mail', $this->request->filter('trim')->mail, 0, BYENDS_BASE_URL);
		
			$this->notice->set($error);
			$this->response->goBack();
		}
		else {
			$validator->deleteRule('mail');
			$validator->addRule('password', 'required', 'Please enter password.');
			$error = $validator->run($this->request->filter('trim')->from('password'));
			
			if ($error) {
				Byends_Cookie::set('__byends_remember_mail', $this->request->filter('trim')->mail, 0, BYENDS_BASE_URL);
			
				$this->notice->set($error);
				$this->response->goBack();
			}
		}
		
		Byends_Cookie::delete('__byends_remember_mail', BYENDS_BASE_URL);
		$condition = array(
				'params'      => array('mail' => $this->request->filter('trim')->mail),
				'status'      => null,
				'processUser' => false,
				'object'      => true,
		);
		$user = $this->setCondtion($condition)->select();
		$valid = false;
		$errorInfo = 'Email or Password Invalid!';
		
		if ($user) {
			if ($user['status'] == 'normal') {
				$hashValidate = Byends_Paragraph::hashValidate($this->request->filter('trim')->password, $user['password']);
				if ($hashValidate) {
					$authCode = sha1(Byends_Paragraph::randString(20));
					$user['authCode'] = $authCode;
					$expire = 1 == $this->request->remember ? $this->timeStamp + 30*24*3600 : 0;
					
					Byends_Cookie::set('__byends_uid', $user['uid'], $expire, BYENDS_BASE_URL);
					Byends_Cookie::set('__byends_authCode', Byends_Paragraph::hash($authCode),	$expire, BYENDS_BASE_URL);
			
					$this->db->updateRow(
						BYENDS_TABLE_USERS,
						array( 'uid' => $user['uid'] ),
						array( 'logged' => $this->gmtTimeStamp, 'authCode' => $authCode )
					);
				
					$this->processUser($user, false, true);
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
	 * 用户登出
	 *
	 * @access public
	 * @return void
	 */
	public function logout()
	{
		Byends_Cookie::delete('__byends_uid', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_authCode', BYENDS_BASE_URL);
		
		Byends_Cookie::delete('__byends_gather_title', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_gather_image', BYENDS_BASE_URL);
		Byends_Cookie::delete('__byends_gather_referer', BYENDS_BASE_URL);
		
		$this->response->redirect(BYENDS_SITE_URL);
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
			$cookieUid = Byends_Cookie::get('__byends_uid');
			if (null !== $cookieUid) {
				/** 验证登陆 */
				$condition = array(
						'params'      => array('uid' => intval($cookieUid)),
						'status'      => 'normal',
						'processUser' => false,
						'object'      => true,
				);
				$user = $this->setCondtion($condition)->select();
				
				if ($user) {
					$cookieAuthCode = Byends_Cookie::get('__byends_authCode');
					
					if (Byends_Paragraph::hashValidate($user['authCode'], $cookieAuthCode)) {
						$this->processUser($user, true, true);
						
						return $this->_hasLogin;
					}
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
				->from('uid', 'name', 'mail', 'url', 
				'password', 'password2', 'group', 'status', 'description');
		
		if( empty($user['name']) ) {
			return 'username-empty';
		}
		
		if( empty($user['mail']) ) {
			return 'mail-empty';
		}
		
		$validate = new Byends_Validate();
		if( !$validate->email($user['mail']) ) {
			return 'mail-incorrect';
		}
		
		$userData = array(
				'name' => $user['name'],
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
				'SELECT uid, name, mail 
				FROM '.BYENDS_TABLE_USERS." 
				WHERE ( name = :1 or mail = :2 ) and uid <> :3", 
				$user['name'], $user['mail'], $user['uid']
		 );
		
		if ($userArr){
			if( $userArr['name'] == $user['name'] ) {
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
				->from( 'name', 'mail', 'url',
				'password', 'password2', 'group', 'status', 'description');
		
		if( empty($user['name']) ) {
			return 'username-empty';
		}
		
		if( empty($user['mail']) ) {
			return 'mail-empty';
		}
		
		$validate = new Byends_Validate();
		if( !$validate->email($user['mail']) ) {
			return 'mail-incorrect';
		}
		
		if( empty($user['password']) ) {
			return 'passwords-empty';
		}
		
		if( $user['password'] <> $user['password2'] ) {
			return 'passwords-not-equal';
		}
		
		$userArr = $this->db->getRow( 
				'SELECT uid, name, mail 
				FROM '.BYENDS_TABLE_USERS." 
				WHERE name = :1 or mail = :2", 
				$user['name'], $user['mail']
		 );
		
		if ($userArr){
			if( $userArr['name'] == $user['name'] ) {
				return 'username-exists';
			}
			elseif ( $userArr['mail'] == $user['mail'] ) {
				return 'mail-exists';
			}
		}
		
		$this->db->insertRow(
			BYENDS_TABLE_USERS,
			array(
				'name' => $user['name'],
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
	protected function processUser(&$user, $object = false, $hasLogin = false) 
	{
		$user['avatar'] = is_file(__BYENDS_ROOT_DIR__.__BYENDS_AVATARS_DIR__.$user['avatar']) ? 
						  BYENDS_AVATARS_STATIC_URL.$user['avatar'] : BYENDS_NO_AVATAR_STATIC_URL;
		$user['created'] = Byends_Date::timeStamp( $user['created'] );
		$user['dateWord'] = Byends_Date::dateWord($user['created'], $this->timeStamp, $this->options->lang);
		
		if ($hasLogin) {
			$this->user = (object)$user;
			$this->uid = $this->user->uid;
			$this->_hasLogin = true;
		}
		
		$user = $object ? (object)$user : $user;
	}
}

?>