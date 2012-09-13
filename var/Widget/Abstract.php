<?php
/**
 * 数据抽象类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Widget_Abstract
 * @copyright  Copyright (c) 2012 Byends (http://www.byends.com)
 */
abstract class Widget_Abstract extends Byends_Widget
{
	public $options = null;
	public $user = null;
	public $uid = null;
	
	protected $select = null;
	protected $perPage = 0;
	protected $currentPage = 0;
	protected $totalPages = 0;
	protected $totals = 0;
	
	/**
	 * 构造函数,初始化组件
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->options = Widget_Options::get();
	}
	
	/**
	 * 查询
	 */
	abstract public function select();
	
	/**
	 * 插入
	 */
	abstract public function insert();
	
	/**
	 * 更新
	 */
	abstract public function update();
	
	/**
	 * 删除
	 */
	abstract public function delete();
	
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
	
}
?>