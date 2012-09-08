<?php
/**
 * 基本配置处理类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Widget_Options
 * @copyright  Copyright (c) 2011 Byends (http://www.byends.com)
 */
class Widget_Options
{
	protected $db = NULL;
	public static $options = NULL;
	
	/**
	 * 单例句柄
	 *
	 * @access private
	 * @var Widget_Options
	 */
	private static $_instance = NULL;
	
	public function __construct()
	{
		$this->db = Byends_Db::get();
		self::$options = (object)$this->getOptions();
	}
	
	/**
	 * 获取单例句柄
	 *
	 * @access public
	 * @return Widget_Options
	 */
	public static function getInstance()
	{
		if (NULL === self::$_instance) {
			self::$_instance = new Widget_Options();
		}
	
		return self::$_instance;
	}
	
	public static function get()
	{
		if (empty(self::$options)) {
			self::getInstance();
		}
		
		return self::$options;
	}
	
	public function getOptions()
	{
		$options = $this->db->query(
			'SELECT  
				p.*
			FROM 
				'.BYENDS_TABLE_OPTIONS.' p	'
		);
		$temp = array();
		foreach( $options as $v ) {
			$temp[$v['name']] = $v['value'];
		}
		$temp['imageConfig'] = unserialize($temp['imageConfig']);
		$temp['imageConfig']['coverSize'] = explode('|', $temp['imageConfig']['coverSize']);
		$temp['imageConfig']['thumbSize'] = explode('|', $temp['imageConfig']['thumbSize']);
		$temp['imageConfig']['stepSize']  = explode('|', $temp['imageConfig']['stepSize']);
		//$temp['absolutePath'] = substr($temp['absolutePath'], -1) == '/' ? substr($temp['absolutePath'], 0, -1) : $temp['absolutePath'];
		return $temp;
	}
	
	public function addOption( $name, $value) {
		$this->db->insertRow(
				ASAPH_TABLE_OPTIONS,
				array(
						'name'  => $name,
						'value' => $value
				)
		);
		return true;
	}
	
	public function updateOption( $name, $value) {
		$this->db->updateRow(
				BYENDS_TABLE_OPTIONS,
				array( 'name'  => $name ),
				array( 'value' => $value )
		);
		return true;
	}
	
}
?>