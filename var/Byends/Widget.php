<?php
/**
 * Widget 基类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Byends_Widget
 * @copyright  Copyright (c) 2012 Byends (http://www.byends.com)
 */
abstract class Byends_Widget
{
	protected $sCondition = array();
	public $gmtTimeStamp = 0;
	
	/**
	 * 可以直接用的时间戳
	 */
	public $timeStamp = 0;
	public $response = null;
	public $request = null;
	protected $db = null;
	
	public function __construct()
	{
		$this->gmtTimeStamp = Byends_Date::gmtTime();
		$this->timeStamp = Byends_Date::timeStamp($this->gmtTimeStamp);
		$this->response = Byends_Response::getInstance();
		$this->request = Byends_Request::getInstance();
		$this->db = Byends_Db::get();
	}
	
	/**
	 * 设置 select 查询条件
	 * @param array $sCondition
	 * @return Byends_Widget
	 */
	public function setCondition($sCondition = array()) 
	{
		if ($sCondition) {
			foreach ($sCondition as $k => $v) {
				$this->sCondition[$k] = $v;
			}
		}
	
		return $this;
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