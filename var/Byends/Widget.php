<?php
/**
 * Widget 基类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Byends_Widget
 * @copyright  Copyright (c) 2011 Byends (http://www.byends.com)
 */
class Byends_Widget
{
	protected $db = NULL;
	protected $select = NULL;
	protected $sCondition = array();
	protected $perPage = 0;
	protected $currentPage = 0;
	protected $totalPages = 0;
	protected $totals = 0;
	
	public $response = NULL;
	public $request = NULL;
	public $gmtTimeStamp = 0;
	
	/**
	 * 直接可以用的时间戳
	 */
	public $timeStamp = 0;
	public $options = NULL;
	public $user = NULL;
	public $uid = NULL;
	
	public function __construct()
	{
		$this->db = Byends_Db::get();
		$this->gmtTimeStamp = Byends_Date::gmtTime();
		$this->timeStamp = Byends_Date::timeStamp($this->gmtTimeStamp);
		$this->options = Widget_Options::get();
		$this->response = Byends_Response::getInstance();
		$this->request = Byends_Request::getInstance($this->options);
	}
	
	/**
	 * 设置 select 查询条件
	 * @param array $sCondition
	 * @return Byends_Widget
	 */
	public function setCondtion($sCondition = array()) {
		if ($sCondition) {
			foreach ($sCondition as $k => $v) {
				$this->sCondition[$k] = $v;
			}
		}
	
		return $this;
	}
	
	/**
	 * 获取
	 */
	public function select() {}
	
	/**
	 * 插入
	 */
	public function insert() {}
	
	/**
	 * 更新
	 */
	public function update() {}
	
	/**
	 * 删除
	 */
	public function delete() {}
	
	/**
	 * 设置每页数目
	 * @param integer
	 */
	public function setPerPage($perPage) {
		$this->perPage = $perPage;
	}
	
	/**
	 * 获取内容数目
	 * @return number
	 */
	public function getTotals()
	{
		return $this->totals;
	}
	
	/**
	 * 获取分布数目
	 * @return number
	 */
	public function getTotalPages() 
	{
		return $this->totalPages;
	}
	
	/**
	 * 获取分页链接
	 * @return string
	 */
	public function getPages() 
	{
		$pages = array( 
			'current' => 1,
			'total' => 1,
			'prev' => false,
			'next' => false,
		);
		if( $this->totals > 0 ) {
			$pages['current'] = $this->currentPage + 1;
			$pages['total'] = $this->totalPages;
			if( $this->currentPage > 0 ) {
				$pages['prev'] = $this->currentPage;
			}
			if( $this->totals > ($this->perPage * $this->currentPage + $this->perPage) ) {
				$pages['next'] = $this->currentPage + 2;
			}
		}
		
		return $pages;
	}
	
	/**
	 * 加载文件
	 * @param string $fileName
	 * @return string
	 */
	public function need($fileName)
	{
		$filePath = __BYENDS_ROOT_DIR__ . __BYENDS_THEME_DIR__ . $fileName;
		if( file_exists($filePath) ) {
			return $filePath;
		}
		else {
			die( 'The file '.$fileName.' is not exists!' );
		}
	}
}
?>