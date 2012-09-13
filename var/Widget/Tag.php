<?php
/**
 * Tag 处理类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Widget_Tag
 * @copyright  Copyright (c) 2012 Byends (http://www.byends.com)
 */
class Widget_Tag extends Widget_Abstract
{
	protected $type = 'tag';
	
	/**
	 * 单例句柄
	 * 
	 * @access private
	 * @var Widget_Tag
	 */
	private static $_instance = null;
	
	public function __construct() 
	{
		parent::__construct();
		
		$this->perPage = $this->options->perPage;
		$this->select = 'mid, name, slug, type, description, count';
		$this->sCondition = array(
				'mid' => 0,
				'page' => 0,
				'order' => array('mid', 'DESC')
		);
		
	}
	
	/**
	 * 获取单例句柄
	 *
	 * @access public
	 * @return Widget_Tag
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new Widget_Tag();
		}
	
		return self::$_instance;
	}
	
	/**
	 * 获取Tag
	 * @return array
	 */
	public function select()
	{
		if ($this->sCondition['mid'] > 0) {
			$tag = $this->db->getRow(
					'SELECT
						'.$this->select.'
					FROM
						'.BYENDS_TABLE_METAS.'
					WHERE
						type = :1 AND mid = :2',
						$this->type, $this->sCondition['mid']
			);
			if( empty($tag) ) {
				return array();
			}
			
			return $tag;
		}
		else {
			$this->currentPage = $this->sCondition['page'];
			$tags = $this->db->query(
					'SELECT SQL_CALC_FOUND_ROWS
						'.$this->select.'
					FROM
						'.BYENDS_TABLE_METAS.'
					WHERE
						type = :1
					ORDER BY
						'.$this->sCondition['order'][0].' '.$this->sCondition['order'][1].'
					LIMIT
						:2, :3',
							$this->type,
							$this->currentPage * $this->perPage,
							$this->perPage
			);
			$this->totals = $this->db->foundRows();
			$this->totalPages = ceil($this->totals / $this->perPage );
			
			return $tags;
		}
	}
	
	/**
	 * 删除标签
	 * @param integer $mid
	 * @return string|boolean
	 */
	public function delete()
	{
		$mid = $this->request->filter('trim', 'int')->get('mid', 0);
		if( !$mid ) {
			return false;
		}
	
		$mid = is_array($mid) ? $mid : array($mid);
		foreach ($mid as $v) {
			$this->delRelationships(0, $v, false);
			$this->db->query( 'DELETE FROM '.BYENDS_TABLE_METAS.' WHERE type = :1 AND mid = :2', $this->type, $v );
		}
	
		return true;
	}
	
	/**
	 * 更新标签
	 * 
	 * @param integer $mid
	 * @param string $name
	 * @param string $slug
	 * @param string $description
	 * @return string|boolean
	 */
	public function update()
	{
		$mid = $this->request->filter('trim', 'int')->get('mid', 0);
		$name = $this->request->filter('trim')->get('name', '');
		$slug = $this->request->filter('trim')->get('slug', '');
		if(!$mid) {
			return 'mid-error';
		}
		
		if( empty($name) ) {
			return 'name-empty';
		}
		
		if( $this->nameExists($name, $mid) ) {
			return 'name-exists';
		}
		
		$slug = $this->nameToSlug($slug ? $slug : $name);
		if( !$slug ) {
			return 'nameToSlug-error';
		}
		
		if( $this->slugExists($slug, $mid) ) {
			return 'slug-exists';
		}
		
		$this->db->updateRow(
				BYENDS_TABLE_METAS,
				array( 'mid' => $mid ),
				array(
					'name' => $name,
					'slug' => $slug,
					'description' => ($description ? $description : $name)
				)
		);
		
		return true;
	}
	
	/**
	 * 检测需要插入的标签
	 * @return string|boolean
	 */
	public function insert() 
	{
		$name = $this->request->filter('trim')->name;
		$slug = $this->request->filter('trim')->slug;
		
		if( empty($name) ) {
			return 'name-empty';
		}
		
		if( $this->nameExists($name) ) {
			return 'name-exists';
		}
		
		$slug = $this->nameToSlug($slug ? $slug : $name);
		if( !$slug ) {
			return 'nameToSlug-error';
		}
		
		if( $this->slugExists($slug) ) {
			return 'slug-exists';
		}
		
		$mid = $this->insertTag($name, $slug);
		
		return true;
	}
	
	/**
	 * 插入标签
	 * 
	 * @param string $name
	 * @param string $slug
	 * @return integer
	 */
	public function insertTag($name, $slug) 
	{
		$this->db->insertRow(
				BYENDS_TABLE_METAS,
				array(
						'name' => $name,
						'slug' => $slug,
						'type' => $this->type,
						'description' => $name,
						'count' => 0
				)
		);
		return $this->db->insertId();
	}
	
	/**
	 * 判断标签是否存在
	 * 
	 * @param integer $mid
	 * @return boolean
	 */
	public function tagExists($mid)
	{
		$tag = $this->db->getRow( 'SELECT mid FROM '.BYENDS_TABLE_METAS.' WHERE type = :1 AND mid = :2 LIMIT 1', $this->type, $mid );
		return $tag ? true : false;
	}
	
	/**
	 * 判断标签名称是否存在
	 * 
	 * @param string $name
	 * @param integer $mid
	 * @return Ambigous <boolean, integer>
	 */
	public function nameExists($name, $mid = 0)
	{
		$condition = $mid == 0 ? 'type= :1 AND name = :2' : 'type= :1 AND name = :2 AND mid <> :3';
		$tag = $this->db->getRow( 'SELECT mid FROM '.BYENDS_TABLE_METAS.' WHERE '.$condition.' LIMIT 1', $this->type, $name, $mid );
		return $tag ? $tag['mid'] : false;
	}
	
	/**
	 * 标签名转换为缩略名
	 * @param unknown_type $tag
	 * @return boolean|string
	 */
	public function nameToSlug($name)
	{
		$slug = Byends_Paragraph::slugName($name);
		if (empty($slug)) {
			return false;
		}
	
		return $slug;
	}
	
	/**
	 * 判断标签缩略名是否存在
	 * 
	 * @param string $slug 缩略名
	 * @param integer $mid
	 * @return boolean
	 */
	public function slugExists($slug, $mid = 0)
	{
		$condition = $mid == 0 ? 'type = :1 AND slug = :2' : 'type = :1 AND slug = :2 AND mid <> :3';
		$slug = $this->db->getRow( 'SELECT mid FROM '.BYENDS_TABLE_METAS.' WHERE '.$condition.' LIMIT 1', $this->type, $slug, $mid );
		return $slug ? true : false;
	}
	
	/**
	 * 添加关联
	 * @param int $cid
	 * @param int $mid
	 */
	public function addRelationships( $cid, $mid ) {
		$this->db->insertRow( 
			BYENDS_TABLE_RELATE, 
			array(
				'cid' => $cid,
				'mid' => $mid
			)
		);
		$this->refreshTag($mid);
		
		return true;
	}
	

	/**
	 * 删除关联
	 * @param int $cid
	 * @param int $mid
	 */
	public function delRelationships($cid = 0, $mid = 0, $refreshTag = true) 
	{
		$condition = array();
	
		if ($cid) {
			$condition[] = 'cid = '.$cid;
		}
	
		if ($mid) {
			$condition[] = 'mid = '.$mid;
		}
		
		if ( ($cid || $mid) && $refreshTag) {
			if (!$mid) {
				$tmpMid = $this->db->query(
					'SELECT mid FROM '.BYENDS_TABLE_RELATE.' WHERE cid = :1', $cid
				);
				$mid = Byends_Paragraph::arrayFlatten($tmpMid, 'mid');
			}
		}
		
		if ($condition) {
			$condition = implode(' AND ', $condition);
			$this->db->query( 'DELETE FROM '.BYENDS_TABLE_RELATE.' WHERE '.$condition);
			
			if ($refreshTag) {
				if (is_array($mid)) {
					foreach ($mid as $v) {
						$this->refreshTag($v);
					}
				}
				else {
					$this->refreshTag($mid);
				}
			}
		}
		
		return true;
	}
	
	/**
	 * 根据tag获取MID
	 * 
	 * @param mixed $inputTags
	 * @return array
	 */
	public function scanTags($inputTags)
	{
		$tags = is_array($inputTags) ? $inputTags : array($inputTags);
		$result = array();
		if( $inputTags ) {
			foreach ($tags as $tag) {
				if (empty($tag)) {
					continue;
				}
	
				$tagId = $this->nameExists($tag);
	
				if ($tagId) {
					$result[] = $tagId;
				} else {
					$tagSlug = $this->nameToSlug($tag);
	
					if ($tagSlug) {
						$result[] = $this->insertTag($tag, $tagSlug);
					}
				}
			}
		}
		return is_array($inputTags) ? $result : current($result);
	}
	
	/**
	 * 设置内容标签
	 * @param integer $cid
	 * @param string $tags
	 */
	public function setTags($cid, $tags, $isNew = false)
	{
		if (!$isNew) {
			/** 取出已有tag */
			$existTags = $this->db->query(
					'SELECT
					m.mid
				FROM
					'.BYENDS_TABLE_METAS.' m
				LEFT JOIN '.BYENDS_TABLE_RELATE.' r
					ON r.mid = m.mid
				WHERE
					r.cid = :1',
					$cid
			);
	
			/** 删除已有tag */
			if ($existTags) {
				foreach ($existTags as $tag) {
					$this->delRelationships($cid, $tag['mid']);
				}
			}
		}
	
		/** 检测并取出 tag */
		$insertTags = $this->scanTags($tags);
		
		/** 插入tag */
		if ($insertTags) {
			foreach ($insertTags as $tagId) {
				$this->addRelationships($cid, $tagId);
			}
		}
		
		return $insertTags;
	}
	
	/**
	 * 刷新标签
	 * @param int $mid
	 * @return boolean
	 */
	public function refreshTag($mid) 
	{
		$relate = $this->db->query(
				'SELECT SQL_CALC_FOUND_ROWS
					cid, mid
				FROM
					'.BYENDS_TABLE_RELATE.'
				WHERE
					mid = :1'
				, $mid
		);
		$count = $this->db->foundRows();
		$this->db->updateRow(
				BYENDS_TABLE_METAS,
				array( 'type' => $this->type, 'mid' => $mid ),
				array( 'count' => $count)
		);
		
		return true;
	}
	
	/**
	 * 加工标签信息
	 *
	 * @access public
	 * @return string
	 */
	public function processTag($cid, &$content)
	{
		$tags = $this->db->query(
				"SELECT
				m.mid, m.name, m.slug, m.count
			FROM
				".BYENDS_TABLE_METAS." m
			LEFT JOIN ".BYENDS_TABLE_RELATE." r
				ON r.mid = m.mid
			WHERE
				m.type = :1 AND r.cid = :2
			ORDER BY
				m.count DESC",
				$this->type, $cid
		);
		
		$tagIdStr = '';
		$tagNameStr = '';
		$tempTags = array();
		if ($tags) {
			foreach($tags as $k => $v) {
				$tempTags[$v['mid']] = $v;
				$tempTags[$v['mid']]['permalink'] = BYENDS_TAG_URL.$v['slug'];
			}
			$tagIdStr = Byends_Paragraph::arrayFlatten($tags, 'mid');
			$tagIdStr = implode(',', $tagIdStr);
				
			$tagNameStr = Byends_Paragraph::arrayFlatten($tags, 'name');
			$tagNameStr = implode(',', $tagNameStr);
		}
		$content['tag'] = $tempTags;
		$content['tagIdStr'] = $tagIdStr ? $tagIdStr : '';
		$content['tagNameStr'] = $tagIdStr ? $tagNameStr : '';
	}
}

?>