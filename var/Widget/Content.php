<?php
/**
 * Post 处理类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Widget_Content
 * @copyright  Copyright (c) 2012 Byends (http://www.byends.com)
 */
class Widget_Content extends Widget_Abstract
{
	protected $cid = null;
	protected $postModified = 0;
	protected $isFavoritesId = array(
		'isSelected' => false,
		'data' => array()
	);
	
	protected $coverHash = null;
	protected $coverExt= null;
	protected $coverSize = null;
	protected $stepImageUrl = null;
	
	public $type = array(
		'post' => 'post',
		'page' => 'page',
		'attachment' => 'attachment'
	);
	
	public $status = array(
		'publish' => 'publish',
		'waiting' => 'waiting',
		'notpass' => 'notpass',
		'delete'  => 'delete'
	);
	
	public $instanceTag = null;
	public $instanceUser = null;
	public $instanceUpload = null;
	
	/**
	 * 单例句柄
	 * 
	 * @access private
	 * @var Widget_Content
	 */
	private static $_instance = null;
	
	public function __construct() 
	{
		parent::__construct();
		
		$this->perPage = $this->options->perPage;
		$this->instanceTag = Widget_Tag::getInstance();
		$this->instanceUser = Widget_User::getInstance();
		if ($this->instanceUser->hasLogin()) {
			$this->user = (object)$this->instanceUser->user;
			$this->uid = $this->user->uid;
		}
		$this->instanceUpload = Widget_Upload::getInstance();
		
		$this->select = 'c.cid, c.title, c.slug, c.uid, c.created, c.modified, 
					c.coverHash, c.coverExt, c.coverSize, c.brief, c.ingredients, c.steps, c.tips, 
					c.type, c.status, c.allowComment, c.commentsNum, c.favoritesNum, c.views';
		
		$this->sCondition = array(
				'cid' => 0,
				'uid' => 0,
				'page' => 0,
				'status' => 'publish',
				'type' => 'post',
				'order' => array('cid', 'DESC')
		);
	}
	
	/**
	 * 获取单例句柄
	 *
	 * @access public
	 * @return Widget_Content
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new Widget_Content();
		}
	
		return self::$_instance;
	}
	
	/**
	 * 获取内容
	 * @return array
	 */
	public function select() 
	{
		if (null !== $this->uid && !$this->isFavoritesId['isSelected']) {
			$instanceCook = Widget_Cook::getInstance();
			$this->isFavoritesId['isSelected'] = true;
			$this->isFavoritesId['data'] = $instanceCook->favoritesId();
		}
		
		if ($this->sCondition['cid'] > 0) {
			$condition = $this->sCondition['status'] ? ' AND c.status = :3 ' : '';
			$content = $this->db->getRow(
				'SELECT
					'.$this->select.', u.fullname, u.username, u.url, u.description, 
					u.avatar, u.status, u.likesNum, u.publishedNum 
				FROM
					'.BYENDS_TABLE_CONTENTS.' c
				LEFT JOIN '.BYENDS_TABLE_USERS.' u
					ON u.uid = c.uid
				WHERE
					c.cid = :1 AND c.type = :2'.$condition,
					$this->sCondition['cid'], $this->sCondition['type'], $this->sCondition['status']
			);
			
			if( empty($content) ) {
				return array();
			}
			
			$this->cid = $content['cid'];
			$this->postModified = $content['modified'];
			$this->processContent( $content );
			$this->instanceUser->processUser($content);
			$this->instanceTag->processTag($this->cid, $content);
			return $content;
		}
		else {
			$condition = $this->sCondition['status'] ? ' AND c.status = :2 ' : '';
			$condition .= $this->sCondition['uid'] ? ' AND c.uid = :3 ' : ''; 
			$this->currentPage = isset($this->sCondition['page']) ? $this->sCondition['page'] : 0;
			
			$selectNum = isset($this->sCondition['ajaxNum']) && $this->sCondition['ajaxNum'] > 0 ?
						$this->sCondition['ajaxNum'] : $this->perPage;
			$nextRecipe = isset($this->sCondition['nextRecipe']) ?
						$this->sCondition['nextRecipe'] : $this->currentPage * $this->perPage;
			
			$contents = $this->db->query(
				'SELECT SQL_CALC_FOUND_ROWS
					'.$this->select.', u.fullname, u.username, u.url, u.description, 
					u.avatar, u.status, u.likesNum, u.publishedNum 
				FROM
					'.BYENDS_TABLE_CONTENTS.' c
				LEFT JOIN '.BYENDS_TABLE_USERS.' u
					ON u.uid = c.uid
				WHERE
					c.type = :1 '.$condition.' 
				ORDER BY
					'.$this->sCondition['order'][0].' '.$this->sCondition['order'][1].'
				LIMIT
					:4, :5',
					$this->sCondition['type'], $this->sCondition['status'], $this->sCondition['uid'],
					$nextRecipe, $selectNum
			);
			$this->totals = $this->db->foundRows();
			$this->totalPages = ceil($this->totals / $this->perPage );
			
			if( empty($contents) ) {
				return array();
			}
			
			foreach( array_keys($contents) as $i ) {
				$this->processContent($contents[$i]);
				$this->instanceUser->processUser($contents[$i]);
				$this->instanceTag->processTag($contents[$i]['cid'], $contents[$i]);
			}
			
			return $contents;
		}
	}
	
	/**
	 * 获取随机内容
	 *
	 * @access public
	 * @return array
	 */
	public function selectRandom($num = 1, $noIds = array()) 
	{
		$contents = array();
		for ($i = 0;$i < $num;$i++) {
			$content = $this->db->getRow(
				'SELECT 
					'.$this->select.'
				FROM 
					'.BYENDS_TABLE_CONTENTS.' AS c
				JOIN (
						SELECT ROUND( RAND() * (SELECT MAX(cid)-MIN(cid) FROM '.BYENDS_TABLE_CONTENTS.')
								+ (SELECT MIN(cid) FROM '.BYENDS_TABLE_CONTENTS.') )
								AS cid ) AS m
				WHERE
					c.cid >= m.cid AND c.type = :1 AND c.status = :2 
				ORDER BY
					c.cid
				LIMIT
					1',
					'post', 'publish'
			);
			if (!in_array($content['cid'], $noIds)) {
				$noIds[] = $content['cid'];
				$contents[] = $content;
			}
		}
		
		if( empty($contents) ) {
			return array();
		}
		
		foreach(array_keys($contents) as $i) {
			$this->processContent($contents[$i]);
		}
	
		return $contents;
	}
	
	/**
	 * 获取相关内容
	 *
	 * @access public
	 * @return array
	 */
	public function selectRelated($cid, $tagIdStr, $num = 9) 
	{
		$contents = $this->db->query(
			'SELECT DISTINCT 
				'.$this->select.'
			FROM
				'.BYENDS_TABLE_CONTENTS.' c
			LEFT JOIN ".BYENDS_TABLE_RELATE." r
				ON r.cid = c.cid
			WHERE
				c.cid <> :1 AND r.mid IN('.$tagIdStr.') AND c.type = :2 AND c.status = :3
			ORDER BY
				c.cid DESC
			LIMIT
				:4',
				$cid, 'post', 'publish', $num
		);
		
		if (empty($contents)) {
			return array();
		}
	
		foreach( array_keys($contents) as $i ) {
			$this->processContent( $contents[$i] );
		}
	
		return $contents;
	}
	
	/**
	 * 前一条
	 *
	 * @access public
	 * @return string
	 */
	public function thePrev($default = null) 
	{
		$content = $this->db->getRow(
			'SELECT 
				'.$this->select.'
			FROM
				'.BYENDS_TABLE_CONTENTS.' c
			WHERE
				c.modified > :1 and 
				c.modified < :2
			ORDER BY 
				cid ASC
			LIMIT 1',
				$this->postModified,
				$this->gmtTimeStamp
		);
		
		if (empty($content)) {
			return $default;
		}
		$this->processContent($content);
		$link = '<a href="' . $content['permalink'] . '" title="' . $content['title'] . '">' . $content['title'] . '</a>';
		return $link;
	}
	
	/**
	 * 后一条
	 *
	 * @access public
	 * @return string
	 */
	public function theNext($default = null) 
	{
		$content = $this->db->getRow(
			'SELECT 
				'.$this->select.'
			FROM
				'.BYENDS_TABLE_CONTENTS.' c
			WHERE
				c.modified < :1
			ORDER BY 
				cid DESC
			LIMIT 1',
				$this->postModified
		);
		if (empty($content)) {
			return $default;
		}
		$this->processContent($content);
		$link = '<a href="' . $content['permalink'] . '" title="' . $content['title'] . '">' . $content['title'] . '</a>';
		return $link;
	}
	
	/**
	 * 删除一条内容
	 *
	 * @access public
	 * @return string
	 */
	public function delete() 
	{
		$cid =  $this->request->filter('trim', 'int')->get('cid', 0);
		if (!$cid) {
			return false;
		}
		
		$content = $this->db->getRow(
			'SELECT cid, modified, coverHash, coverExt
			FROM '.BYENDS_TABLE_CONTENTS.' 
			WHERE cid = :1',
			$cid
		);
		
		// Delete thumbnail and cover from disk
		if( $content['coverHash'] && $content['coverExt'] ) {
			$coverName = $content['coverHash']. '.' . $content['coverExt'];
			$thumb = 
				__BYENDS_ROOT_DIR__
				.__BYENDS_THUMBS_DIR__
				.date('Y/m/', Byends_Date::timeStamp($content['modified']))
				.$coverName;
			@unlink( $thumb );
			
			$cover =
			__BYENDS_ROOT_DIR__
			.__BYENDS_COVERS_DIR__
			.date('Y/m/', Byends_Date::timeStamp($content['modified']))
			.$coverName;
			@unlink( $cover );
		}
		
		$this->instanceTag->delRelationships($cid);
		$instanceCook = Widget_Cook::getInstance();
		$instanceCook->doFavorite($cid, 'unsaved');
		$this->db->query( 'DELETE FROM '.BYENDS_TABLE_CONTENTS.' WHERE cid = :1', $cid );
		$this->refreshPublishedNum($this->uid);
		
		return true;
	}
	
	/**
	 * 生成缩略名
	 * @param string $name
	 * @return boolean|string
	 */
	public function nameToSlug($name)
	{
		$tagSlug = Byends_Paragraph::slugName($name);
		if (empty($tagSlug)) {
			return false;
		}
	
		return $tagSlug;
	}
	
	/**
	 * 查询缩略名是否存在
	 * @param string $slug
	 * @param int $mid
	 * @return boolean
	 */
	public function nameSlugExists($slug, $cid = 0)
	{
		$condition = $cid == 0 ? 'slug = :1' : 'slug = :1 AND cid <> :2';
		$slug = $this->db->getRow( 'SELECT cid FROM '.BYENDS_TABLE_CONTENTS.' WHERE '.$condition.' LIMIT 1', $slug, $cid );
		return $slug ? true : false;
	}
	
	/**
	 * 插入 content
	 * @return boolean
	 */
	public function insert() 
	{
		if( !$this->uid ) {
			return 'not-logged-in';
		}
		
		$content = $this->request->filter('stripTags', 'trim')->from('created', 'title', 'cover',
				'brief', 'ingredients', 'dosage', 'steps', 'stepsImage', 'tips');

		if (!$content['title']) {
			return 'title-empty';
		}
		
		if (!$content['cover']) {
			return 'cover-empty';
		}
		
		if (!implode('', $content['ingredients'])) {
			return 'ingredients-empty';
		}
		
		if (!implode('', $content['steps'])) {
			return 'steps-empty';
		}
		
		$created = $content['created'] ? strtotime($content['created']) : $this->timeStamp;
		$content['status'] = 'publish';
		
		//处理 cover
		$result = $this->coverHandle($content['cover'], $created);
		if( $result !== true ) {
			return $result;
		}
		
		//处理 ingredients
		$tempIng = $tempDosage = array();
		foreach ($content['ingredients'] as $k => $v) {
			if ($v && !in_array($v, $tempIng)) { //去掉空值 和 重复值
				$tempIng[] = $v;
				$tempDosage[] = $content['dosage'][$k];
			}
		}
		
		//处理 steps
		$tempStep = $steps = array();
		foreach ($content['steps'] as $k => $v) {
			if ($v && !in_array($v, $tempStep)) { //去掉空值 和 重复值
				$tempStepImage = '';
				$tempStep[] = $v;
				if ($content['stepsImage'][$k]) {
					if ($this->stepImageHandle($content['stepsImage'][$k], $created)) {
						$tempStepImage = $this->stepImageUrl;
					}
				}
				$steps[] = $v. '@#|@' . $tempStepImage;
			}
		}
		
		$slug = $this->nameToSlug($content['title']);
		$tempSlug = $slug;
		for( $i = 1; $this->nameSlugExists($tempSlug); $i++ ) {
			$tempSlug = $slug.'-'.$i;
		}
		$slug = $tempSlug;
		
		$this->db->insertRow( BYENDS_TABLE_CONTENTS, array(
				'title' => (string)$content['title'],
				'slug' => $slug,
				'uid' => $this->uid,
				'created' => Byends_Date::gmtTime($created),
				'modified' => Byends_Date::gmtTime($created),
				'coverHash' => $this->coverHash,
				'coverExt' => $this->coverExt,
				'coverSize' => $this->coverSize,
				'brief' => (string)$content['brief'],
				'ingredients' => '',
				'steps' => serialize($steps),
				'tips' => (string)$content['tips'],
				'type' => 'post',
				'status' => $content['status'],
				'allowComment' => 0,
				'commentsNum' => 0,
				'favoritesNum' => 0,
				'views' => 0
		));
		
		$cid = $this->db->insertId();
		$insertTags = $this->instanceTag->setTags($cid, $tempIng, true);
		$ingredients = array();
		foreach ($insertTags as $k => $v) {
			$ingredients[$v] = $tempDosage[$k];
		}
		$this->db->updateRow(
				BYENDS_TABLE_CONTENTS,
				array('cid' => $cid),
				array('ingredients' => serialize($ingredients))
		);
		
		$this->refreshPublishedNum($this->uid);
		
		return true;
	}
	
	/**
	 * 修改图片  post
	 * @return boolean
	 */
	public function update() 
	{
		if( !$this->uid ) {
			return 'not-logged-in';
		}
		
		$content = $this->request->filter('stripTags', 'trim')->from('cid', 'modified', 'title', 'cover',
				'brief', 'ingredients', 'dosage', 'steps', 'stepsImage', 'tips');
		
		if (!$content['title']) {
			return 'title-empty';
		}
		
		if (!$content['cover']) {
			return 'cover-empty';
		}
		
		if (!implode('', $content['ingredients'])) {
			return 'ingredients-empty';
		}
		
		if (!implode('', $content['steps'])) {
			return 'steps-empty';
		}
		
		$data = array();
		$cid = $content['cid'];
		$modified = $content['modified'] ? strtotime($content['modified']) : $this->timeStamp;
		$post = $this->setCondition(array('cid' => $cid, 'status' => null))->select();
		$data['modified'] = Byends_Date::gmtTime($modified);
		
		
		//处理 cover
		if ($post['coverHash'] <> $content['cover']) {
			$result = $this->coverHandle($content['cover'], $modified);
			if( $result !== true ) {
				return $result;
			}
			$data['coverHash'] = $this->coverHash;
			$data['coverExt']  = $this->coverExt;
			$data['coverSize'] = $this->coverSize;
		}
		
		
		//处理 ingredients
		$tempIng = $tempDosage = array();
		foreach ($content['ingredients'] as $k => $v) {
			if ($v && !in_array($v, $tempIng)) { //去掉空值 和 重复值
				$tempIng[] = $v;
				$tempDosage[] = $content['dosage'][$k];
			}
		}
		$insertTags = $this->instanceTag->setTags($cid, $tempIng);
		$ingredients = array();
		foreach ($insertTags as $k => $v) {
			$ingredients[$v] = $tempDosage[$k];
		}
		$data['ingredients'] = serialize($ingredients);
		
		//处理 steps
		$tempStep = $steps = array();
		foreach ($content['steps'] as $k => $v) {
			if ($v && !in_array($v, $tempStep)) { //去掉空值 和 重复值
				$tempStepImage = '';
				$tempStep[] = $v;
				if ($content['stepsImage'][$k]) {
					$stepImage = '';
					if (isset($post['steps'][$k])) {
						list($stepText, $stepImage) = explode('@#|@', $post['steps'][$k]);
					}
					if ($stepImage == $content['stepsImage'][$k]) {
						$tempStepImage = $stepImage;
					}
					elseif ($this->stepImageHandle($content['stepsImage'][$k], $modified)) {
						$tempStepImage = $this->stepImageUrl;
					}
				}
				$steps[] = $v. '@#|@' . $tempStepImage;
			}
		}
		$data['steps']  = serialize($steps);
		
		
		$slug = $this->nameToSlug($content['title']);
		$tempSlug = $slug;
		for( $i = 1; $this->nameSlugExists($tempSlug, $cid); $i++ ) {
			$tempSlug = $slug.'-'.$i;
		}
		$data['title'] = (string)$content['title'];
		$data['slug']  = $tempSlug;
		$data['brief'] = (string)$content['brief'];
		$data['tips']  = (string)$content['tips'];
		
		$this->db->updateRow(
				BYENDS_TABLE_CONTENTS,
				array('cid' => $cid),
				$data
		);
		$this->refreshPublishedNum($this->uid);
		
		return true;
	}
	
	/**
	 * 更新浏览次数
	 * @param integer $cid
	 * @return boolean
	 */
	public function updateViews($cid) 
	{
		if (!$cid) {
			return false;
		}
		$this->db->query( 'UPDATE '.BYENDS_TABLE_CONTENTS.' SET views = views + 1 WHERE cid = :1', $cid );
		return true;
	}
	
	/**
	 * 刷新用户的发布数量
	 * @param integar $uid
	 * @return boolean
	 */
	public function refreshPublishedNum($uid)
	{
		if (!$uid) {
			return false;
		}
		
		$contents = $this->db->query(
			'SELECT SQL_CALC_FOUND_ROWS
				cid
			FROM
				'.BYENDS_TABLE_CONTENTS.' 
			WHERE
				uid = :1 AND type = :2 AND status = :3',
			$uid, 'post', 'publish'
		);
		
		$publishedNum = $this->db->foundRows();
		
		$this->db->updateRow(
			BYENDS_TABLE_USERS,
			array( 'uid' => $uid ),
			array( 'publishedNum' => $publishedNum )
		);
		
		return true;
	}
	
	/**
	 * 加工内容
	 * @param array $content
	 */
	protected function processContent( &$content ) 
	{
		$content['created'] = Byends_Date::timeStamp( $content['created'] );
		$content['modified'] = Byends_Date::timeStamp( $content['modified'] );
		$datePath = date( 'Y/m/', $content['modified'] );
		$content['title'] = htmlspecialchars($content['title']);
		$content['permalink'] = BYENDS_SEED_URL.$content['cid'];
		$content['zoomPermalink'] = BYENDS_SEED_URL.$content['cid'].'/zoom';
		$content['stripBrief'] = Byends_Paragraph::stripBrief( $content['brief'] );
		$content['brief'] = htmlspecialchars($content['brief']);
		$content['tips'] = htmlspecialchars($content['tips']);
		$content['ingredients'] = @unserialize($content['ingredients']);
		$content['steps'] = Byends_Paragraph::callbackDeep('htmlspecialchars', @unserialize($content['steps']));
		list($w, $h) = @explode('|', $content['coverSize']);
		$content['width'] = $w;
		$content['height'] = $h;
		$content['viewsWord'] = $this->viewsWord($content['views']);
		$content['dateWord'] = Byends_Date::dateWord($content['modified'], $this->timeStamp, $this->options->lang);
		$content['favorite'] = in_array($content['cid'], $this->isFavoritesId['data']);
		
		$cover = $content['coverHash'].'.'.$content['coverExt'];
		$content['thumb'] = is_file(__BYENDS_ROOT_DIR__.__BYENDS_THUMBS_DIR__.$datePath.$cover) ? BYENDS_STATIC_URL
							.__BYENDS_THUMBS_DIR__ . $datePath . $cover : BYENDS_NO_IMAGE_STATIC_URL;
	
		$content['cover'] = is_file(__BYENDS_ROOT_DIR__.__BYENDS_COVERS_DIR__.$datePath.$cover) ? BYENDS_STATIC_URL
							.__BYENDS_COVERS_DIR__ . $datePath . $cover : BYENDS_NO_IMAGE_STATIC_URL;
	}
	
	/**
	 * 浏览次数词语化
	 * @param integer $views
	 * @return string
	 */
	protected function viewsWord($views) 
	{
		if( $views >= 1000 ) {
			$views = round($views/1000 - 0.05, 1).'K';
		}
		return $views;
	}
	
	/**
	 * 处理封面图
	 * @param string $coverPath
	 */
	public function coverHandle($cover, $created) 
	{
		$ext = strtolower(substr($cover, strrpos($cover, '.') + 1));
		$coverPath = __BYENDS_ROOT_DIR__.__BYENDS_TEMPS_DIR__ . $cover;
		$coverDir  = __BYENDS_ROOT_DIR__.__BYENDS_COVERS_DIR__ . date('Y/m', $created);
		$thumbDir  = __BYENDS_ROOT_DIR__.__BYENDS_THUMBS_DIR__ . date('Y/m', $created);
		
		if (!is_file($coverPath) ||
			!$this->instanceUpload->mkdirr($coverDir) ||
			!$this->instanceUpload->mkdirr($thumbDir)
		) {
			return 'cover-empty';
		}
		
		$coverHash = md5_file($coverPath);
		$coverDir .= '/'. $coverHash. '.' . $ext;
		$thumbDir .= '/'. $coverHash. '.' . $ext;
		list( $coverWidth, $coverHeight, $type ) = getimagesize( $coverPath );
		$coverHeight = floor(($coverHeight/$coverWidth ) * $this->options->imageConfig['coverSize'][0]);
		$coverHeight = $this->options->imageConfig['coverSize'][1] > $coverHeight ? 
					$this->options->imageConfig['coverSize'][1] : $coverHeight;
		$this->instanceUpload->createThumb($coverPath, $coverDir, 
				$this->options->imageConfig['coverSize'][0], 
				$coverHeight, 
				$this->options->imageConfig['jpegQuality'],
				$this->options->imageConfig['cropType']);
		
		$this->instanceUpload->createThumb($coverPath, $thumbDir, 
				$this->options->imageConfig['thumbSize'][0], 
				$this->options->imageConfig['thumbSize'][1], 
				$this->options->imageConfig['jpegQuality'],
				$this->options->imageConfig['cropType']);
		
		$this->coverHash = $coverHash;
		$this->coverExt = $ext;
		list($coverWidth, $coverHeight) = getimagesize( $coverDir );
		$this->coverSize = $coverWidth.'|'.$coverHeight;
		
		return true;
	}
	
	/**
	 * 处理步骤图
	 * @param string $coverPath
	 */
	public function stepImageHandle($stepImage, $created) 
	{
		$ext = strtolower(substr($stepImage, strrpos($stepImage, '.') + 1));
		$stepImagePath = __BYENDS_ROOT_DIR__.__BYENDS_TEMPS_DIR__ . $stepImage;
		$stepImageDir   = __BYENDS_ROOT_DIR__.__BYENDS_STEPS_DIR__ . date('Y/m', $created);
		
		if (!is_file($stepImagePath) ||	!$this->instanceUpload->mkdirr($stepImageDir)) {
			return 'stepImage-empty';
		}
		
		$fileName = md5_file($stepImagePath). '.' . $ext;
		$fileName = $this->instanceUpload->getUniqueFileName($stepImageDir, $fileName);
		$stepImageDir .= '/' . $fileName;
		
		list( $stepWidth, $stepHeight, $type ) = getimagesize( $stepImagePath );
		$stepHeight = floor(($stepHeight/$stepWidth ) * $this->options->imageConfig['stepSize'][0]);
		$stepHeight = $this->options->imageConfig['stepSize'][1] > $stepHeight ?
					$this->options->imageConfig['stepSize'][1] : $stepHeight;
		
		$this->instanceUpload->createThumb($stepImagePath, $stepImageDir,
				$this->options->imageConfig['stepSize'][0],
				$stepHeight,
				$this->options->imageConfig['jpegQuality'],
				$this->options->imageConfig['cropType']);
		
		$this->stepImageUrl = BYENDS_STEPS_STATIC_URL . date('Y/m', $created) . '/' . $fileName;
		return true;
	}
	
}

?>