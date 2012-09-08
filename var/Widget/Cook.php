<?php
/**
 * Cook 处理类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Widget_Cook
 * @copyright  Copyright (c) 2011 Byends (http://www.byends.com)
 */
class Widget_Cook extends Widget_Content
{

	/**
	 * 单例句柄
	 *
	 * @access private
	 * @var Widget_Cook
	 */
	private static $_instance = NULL;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取单例句柄
	 *
	 * @access public
	 * @return Widget_Cook
	 */
	public static function getInstance()
	{
		if (NULL === self::$_instance) {
			self::$_instance = new Widget_Cook();
		}

		return self::$_instance;
	}
	
	/**
	 * 获取指定用户收藏的所有内容
	 *
	 * @access public
	 * @return array
	 */
	public function favorites()
	{
		$uid = $this->sCondition['uid'] < 1 ? $this->uid : $this->sCondition['uid'];
		$this->currentPage = $this->sCondition['page'];
		$favorites = $this->db->query(
				'SELECT SQL_CALC_FOUND_ROWS
					cid
				FROM
					'.BYENDS_TABLE_FAVORITES.'
				WHERE
					uid = :1
				ORDER BY
					'.$this->sCondition['order'][0].' '.$this->sCondition['order'][1].'
				LIMIT
					:2, :3',
				$uid, $this->currentPage * $this->perPage, $this->perPage
		);
		
		if( empty($favorites) ) {
			return array();
		}
		
		$favorites = Byends_Paragraph::arrayFlatten($favorites, 'cid');
		$favorites = implode(',', $favorites);
		
		$this->totals = $this->db->foundRows();
		$this->totalPages = ceil($this->totals / $this->perPage );
		
		$contents = $this->db->query(
			'SELECT SQL_CALC_FOUND_ROWS
				'.$this->select.'
			FROM
				'.BYENDS_TABLE_CONTENTS.' c
			LEFT JOIN '.BYENDS_TABLE_USERS.' u
				ON u.uid = c.uid
			WHERE
				c.cid in ('.$favorites.')
				AND c.type = \'post\' AND c.status = \'publish\'
			'
		);
	
		if( empty($contents) ) {
			return array();
		}
	
		foreach( array_keys($contents) as $i ) {
			$this->processContent($contents[$i]);
			$this->instanceTag->processTag($contents[$i]['cid'], $contents[$i]);
		}
	
		return $contents;
	}
	
	/**
	 * 操作收藏 （存在则删除，不存在则添加）
	 * @return array
	 */
	public function doFavorite($cid){
		$state = 'saved';
		$favoriteExists = $this->favoriteExists($cid);
		
		if ($favoriteExists) {
			$this->db->query( 
					'DELETE FROM 
						'.BYENDS_TABLE_FAVORITES.' 
					WHERE uid = :1 AND cid = :2', 
					$this->uid, $cid
			);
			$state = 'unsaved';
		}
		else {
			$this->db->insertRow(BYENDS_TABLE_FAVORITES, array(
					'uid' => $this->uid,
					'cid' => $cid,
					'created' => $this->gmtTimeStamp
			));
		}
		
		$count = $this->refreshFavorite($cid);
		
		return json_encode(array(
				'count' => $count,
				'state' => $state
		));
	}
	
	/**
	 * 刷新收藏次数
	 * @param int $cid
	 * @return boolean
	 */
	public function refreshFavorite($cid) {
		$favorite = $this->db->query(
				'SELECT 
					SQL_CALC_FOUND_ROWS
					uid, cid
				FROM
					'.BYENDS_TABLE_FAVORITES.'
				WHERE
					cid = :1'
				, $cid
		);
		$count = $this->db->foundRows();
		$this->db->updateRow(
				BYENDS_TABLE_CONTENTS,
				array( 'type' => 'post', 'status' => 'publish', 'cid' => $cid ),
				array( 'favoritesNum' => $count)
		);
	
		return $count;
	}
	
	/**
	 * 判断当前用户是否已经收藏指定内容
	 *
	 * @param integer $mid
	 * @return boolean
	 */
	public function favoriteExists($cid)
	{
		$favorite = $this->db->getRow( 
				'SELECT 
					uid, cid 
				FROM 
					'.BYENDS_TABLE_FAVORITES.' 
				WHERE 
					uid = :1 AND cid = :2 LIMIT 1',
				$this->uid, $cid
		);
		return $favorite ? TRUE : FALSE;
	}
}